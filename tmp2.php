<?php

/*vim:setexpandtabtabstop=4shiftwidth=4softtabstop=4:*/

/**
*ContentsPhp_Beautifierclassandmakesometests
*
*PHPversion5
*
*LICENSE:Thissourcefileissubjecttoversion3.0ofthePHPlicense
*thatisavailablethroughtheworld-wide-webatthefollowingURI:
*http://www.php.net/license/3_0.txt.Ifyoudidnotreceiveacopyof
*thePHPLicenseandareunabletoobtainitthroughtheweb,please
*sendanotetolicense@php.netsowecanmailyouacopyimmediately.
*@categoryPHP
*@packagePHP_Beautifier
*@authorClaudioBustos<cdx@users.sourceforge.com>
*@copyright2004-2010ClaudioBustos
*@linkhttp://pear.php.net/package/PHP_Beautifier
*@linkhttp://beautifyphp.sourceforge.net
*@licensehttp://www.php.net/license/3_0.txtPHPLicense3.0
*@versionCVS:$Id:$
*/
error_reporting(E_ALL);
// Beforeall,testthetokenizerextension
if(!extension_loaded('tokenizer')) {
    thrownewException("Compilephpwithtokenizerextension.Use--enable-tokenizerordon'tuse--disable-allonconfigure.");
}
include_once 'PEAR.php';
include_once 'PEAR/Exception.php';

/**
*RequirePHP_Beautifier_Filter
*/
include_once 'Beautifier/Filter.php';

/**
*RequirePHP_Beautifier_Filter_Default
*/
include_once 'Beautifier/Filter/Default.filter.php';

/**
*RequirePHP_Beautifier_Common
*/
include_once 'Beautifier/Common.php';

/**
*RequireLog
*/
include_once 'Log.php';

/**
*RequireExceptions
*/
include_once 'Beautifier/Exception.php';

/**
*RequireStreamWrapper
*/
include_once 'Beautifier/StreamWrapper.php';

/**
*PHP_Beautifier
*
*Classtobeautifyphpcode
*Howtouse:
*#Createainstanceoftheobject
*#Definetheinputandoutputfiles
*#Optional:SetoneormoreFilter.TheyareprocessedinLIFOorder(lastin,firstout)
*#Processthefile
*#Getit,saveitorshowit.
*
*<code>
*$oToken=newPHP_Beautifier();//createainstance
*$oToken->addFilter('ArraySimple');
*$oToken->addFilter('ListClassFunction');//addoneormorefilters
*$oToken->setInputFile(__FILE__);//nice...processthesamefile
*$oToken->process();//required
*$oToken->show();
*</code>
*@todocreateawebinterface.
*@categoryPHP
*@packagePHP_Beautifier
*@authorClaudioBustos<cdx@users.sourceforge.com>
*@copyright2004-2010ClaudioBustos
*@linkhttp://pear.php.net/package/PHP_Beautifier
*@linkhttp://beautifyphp.sourceforge.net
*@licensehttp://www.php.net/license/3_0.txtPHPLicense3.0
*@versionRelease:0.1.15
*/
classPHP_BeautifierimplementsPHP_Beautifier_Interface {
    // public
    
    /**
    *Tokenscreatedbythetokenizer
    *@vararray
    */
    public $aTokens = array();
    
    /**
    *TokenscodesassignedtomethodonFilter
    *@vararray
    */
    public $aTokenFunctions = array();
    
    /**
    *TokenNames
    *@vararray
    */
    public $aTokenNames = array();
    
    /**
    *Storestheoutput
    *@vararray
    */
    public $aOut = array();
    
    /**
    *Containstheassigmentofmodes
    *@vararray
    *@seesetMode()
    *@seeunsetMode()
    *@seegetMode()
    */
    public $aModes = array();
    
    /**
    *Listofavailablesmodes
    *@vararray
    */
    public $aModesAvailable = array('ternary_operator', 'double_quote');
    
    /**
    *Settingsfortheclass
    *@vararray
    */
    public $aSettings = array();
    
    /**
    *Indexofcurrenttoken
    *@varint
    */
    public $iCount = 0;
    
    /**
    *Charsforindentation
    *@varint
    */
    public $iIndentNumber = 4;
    
    /**
    *Levelofarraynesting
    *@varint
    */
    public $iArray = 0;
    
    /**
    *Levelofternaryoperatornesting
    *@varint
    */
    public $iTernary = 0;
    
    /**
    *Levelofparenthesisnesting
    *@varint
    */
    public $iParenthesis = 0;
    
    /**
    *Levelofverbosity(debug)
    *@varint
    */
    public $iVerbose = false;
    
    /**
    *Nameofinputfile
    *@varstring
    */
    public $sInputFile = '';
    
    /**
    *Nameofoutputfile
    *@varstring
    */
    public $sOutputFile = '';
    
    /**
    *Typeofnewline
    *@varstring
    */
    public $sNewLine = PHP_EOL;
    
    /**
    *Typeofwhitespacetouseforindent
    *@varstring
    */
    public $sIndentChar = '';
    
    /**
    *Savethelastwhitespaceused.UseonlyforFilter!(imissfriendsofC++:()
    *@varstring
    */
    public $currentWhitespace = '';
    
    /**
    *Association$aTokens=>$aOut
    *@vararray
    */
    public $aAssocs = array();
    
    /**
    *Currenttoken.Couldbechangedbyafilter(SeeLowercase)
    *@vararray
    */
    public $aCurrentToken = array();
    
    // private
    
    /**
    *typeoffile
    */
    private $sFileType = 'php';
    
    /**
    *Charsofindent
    *@varint
    */
    private $iIndent = 0;
    
    /**
    *@varint
    */
    private $aIndentStack = array();
    /**Texttobeautify*/
    private $sText = '';
    /**ConstantforlastControl*/
    private $iControlLast;
    /**ReferencestoPHP_Beautifier_Filter's*/
    private $aFilters = array();
    
    /**
    *Stackwithcontrolconstruct
    */
    private $aControlSeq = array();
    
    /**
    *Listofconstructthatstartcontrolstructures
    */
    private $aControlStructures = array();
    
    /**
    *ListofControlforparenthesis
    */
    private $aControlParenthesis = array();
    
    /**
    *Listofconstructthatendcontrolstructures
    */
    private $aControlStructuresEnd = array();
    /**DirsforFilters*/
    private $aFilterDirs = array();
    /**Flagforbeautify/nobeautifymode*/
    private $bBeautify = true;
    /**Log*/
    private $oLog;
    /**Beforenewlineholder*/
    private $sBeforeNewLine = null;
    /**Activateordeactivate'nodeletepreviousspace'*/
    private $bNdps = false;
    /**MarkthebeginoftheendofaDoWhilesequence**/
    private $doWhileBeginEnd;
    // Methods
    
    /**
    *Constructor.
    *Assingvaluesto{@link$aControlStructures},{@link$aControlStructuresEnd},
    *{@link$aTokenFunctions}
    */
    publicfunction__construct() {
        $this->aControlStructures = array(T_CLASS, T_FUNCTION, T_IF, T_ELSE, T_ELSEIF, T_WHILE, T_DO, T_FOR, T_FOREACH, T_SWITCH, T_DECLARE, T_TRY, T_CATCH);
        $this->aControlStructuresEnd = array(T_ENDWHILE, T_ENDFOREACH, T_ENDFOR, T_ENDDECLARE, T_ENDSWITCH, T_ENDIF);
        $aPreTokens = preg_grep('/^T_/', array_keys(get_defined_constants()));
        
        foreach($aPreTokensas$sToken) {
            $this->aTokenNames[constant($sToken)] = $sToken;
            $this->aTokenFunctions[constant($sToken)] = $sToken;
        }
        $aTokensToChange = array(/*QUOTES*/ '"' => "T_DOUBLE_QUOTE", "'" => "T_SINGLE_QUOTE", /*PUNCTUATION*/ '(' => 'T_PARENTHESIS_OPEN', ')' => 'T_PARENTHESIS_CLOSE', ';' => 'T_SEMI_COLON', '{' => 
            'T_OPEN_BRACE', '}' => 'T_CLOSE_BRACE', ',' => 'T_COMMA', '?' => 'T_QUESTION', ':' => 'T_COLON', '=' => 'T_ASSIGMENT', '<' => 'T_EQUAL', '>' => 'T_EQUAL', '.' => 'T_DOT', '[' => 
            'T_OPEN_SQUARE_BRACE', ']' => 'T_CLOSE_SQUARE_BRACE', /*OPERATOR*/ '+' => 'T_OPERATOR', '-' => 'T_OPERATOR', '*' => 'T_OPERATOR', '/' => 'T_OPERATOR', '%' => 'T_OPERATOR', '&' => 
            'T_OPERATOR', '|' => 'T_OPERATOR', '^' => 'T_OPERATOR', '~' => 'T_OPERATOR', '!' => 'T_OPERATOR_NEGATION', T_SL => 'T_OPERATOR', T_SR => 'T_OPERATOR', T_OBJECT_OPERATOR => 
            'T_OBJECT_OPERATOR', /*INCLUDE*/ T_INCLUDE => 'T_INCLUDE', T_INCLUDE_ONCE => 'T_INCLUDE', T_REQUIRE => 'T_INCLUDE', T_REQUIRE_ONCE => 'T_INCLUDE', /*LANGUAGECONSTRUCT*/ T_FUNCTION => 
            'T_LANGUAGE_CONSTRUCT', T_PRINT => 'T_LANGUAGE_CONSTRUCT', T_RETURN => 'T_LANGUAGE_CONSTRUCT', T_ECHO => 'T_LANGUAGE_CONSTRUCT', T_NEW => 'T_LANGUAGE_CONSTRUCT', T_CLASS => 
            'T_LANGUAGE_CONSTRUCT', T_VAR => 'T_LANGUAGE_CONSTRUCT', T_GLOBAL => 'T_LANGUAGE_CONSTRUCT', T_THROW => 'T_LANGUAGE_CONSTRUCT', /*CONTROL*/ T_IF => 'T_CONTROL', T_DO => 'T_CONTROL', 
            T_WHILE => 'T_CONTROL', T_SWITCH => 'T_CONTROL', /*ELSE*/ T_ELSEIF => 'T_ELSE', T_ELSE => 'T_ELSE', /*ACCESSPHP5*/ T_INTERFACE => 'T_ACCESS', T_FINAL => 'T_ACCESS', T_ABSTRACT => 
            'T_ACCESS', T_PRIVATE => 'T_ACCESS', T_PUBLIC => 'T_ACCESS', T_PROTECTED => 'T_ACCESS', T_CONST => 'T_ACCESS', T_STATIC => 'T_ACCESS', /*COMPARATORS*/ T_PLUS_EQUAL => 'T_ASSIGMENT_PRE', 
            T_MINUS_EQUAL => 'T_ASSIGMENT_PRE', T_MUL_EQUAL => 'T_ASSIGMENT_PRE', T_DIV_EQUAL => 'T_ASSIGMENT_PRE', T_CONCAT_EQUAL => 'T_ASSIGMENT_PRE', T_MOD_EQUAL => 'T_ASSIGMENT_PRE', T_AND_EQUAL 
            => 'T_ASSIGMENT_PRE', T_OR_EQUAL => 'T_ASSIGMENT_PRE', T_XOR_EQUAL => 'T_ASSIGMENT_PRE', T_DOUBLE_ARROW => 'T_ASSIGMENT', T_SL_EQUAL => 'T_EQUAL', T_SR_EQUAL => 'T_EQUAL', T_IS_EQUAL => 
            'T_EQUAL', T_IS_NOT_EQUAL => 'T_EQUAL', T_IS_IDENTICAL => 'T_EQUAL', T_IS_NOT_IDENTICAL => 'T_EQUAL', T_IS_SMALLER_OR_EQUAL => 'T_EQUAL', T_IS_GREATER_OR_EQUAL => 'T_EQUAL', /*LOGICAL*/ 
            T_LOGICAL_OR => 'T_LOGICAL', T_LOGICAL_XOR => 'T_LOGICAL', T_LOGICAL_AND => 'T_LOGICAL', T_BOOLEAN_OR => 'T_LOGICAL', T_BOOLEAN_AND => 'T_LOGICAL', /*SUFIXEND*/ T_ENDWHILE => 
            'T_END_SUFFIX', T_ENDFOREACH => 'T_END_SUFFIX', T_ENDFOR => 'T_END_SUFFIX', T_ENDDECLARE => 'T_END_SUFFIX', T_ENDSWITCH => 'T_END_SUFFIX', T_ENDIF => 'T_END_SUFFIX', // forPHP5.3 
            T_NAMESPACE => 'T_INCLUDE', T_USE => 'T_INCLUDE', );
        
        foreach($aTokensToChangeas$iToken => $sFunction) {
            $this->aTokenFunctions[$iToken] = $sFunction;
        }
        $this->addFilterDirectory(dirname(__FILE__) . '/Beautifier/Filter');
        $this->addFilter('Default');
        $this->oLog = PHP_Beautifier_Common::getLog();
    }
    publicfunctiongetTokenName($iToken) {
        if(!$iToken) {
            thrownewException("Token$iTokendoesn'texists");
        }
        return $this->aTokenNames[$iToken];
    }
    
    /**
    *Startthelogfordebug
    *@paramstringfilename
    *@paramintdebuglevel.See{@linkLog}
    */
    publicfunctionstartLog($sFile = 'php_beautifier.log', $iLevel = PEAR_LOG_DEBUG) {
        @unlink($sFile);
        $oLogFile = Log::factory('file', $sFile, 'php_beautifier', array(), PEAR_LOG_DEBUG);
        $this->oLog->addChild($oLogFile);
    }
    
    /**
    *Addafilterdirectory
    *@paramstringpathtodirectory
    *@throwsException
    */
    publicfunctionaddFilterDirectory($sDir) {
        $sDir = PHP_Beautifier_Common::normalizeDir($sDir);
        if(file_exists($sDir)) {
            array_push($this->aFilterDirs, $sDir);
        } else {
            thrownewException_PHP_Beautifier_Filter("Path'$sDir'doesn'texists");
        }
    }
    
    /**
    *ReturnanarraywithalltheFilterDirs
    *@returnarrayListofFilterDirectories
    */
    publicfunctiongetFilterDirectories() {
        return $this->aFilterDirs;
    }
    publicfunctionaddFilterObject(PHP_Beautifier_Filter$oFilter) {
        array_unshift($this->aFilters, $oFilter);
        returntrue;
    }
    
    /**
    *AddaFiltertotheBeautifier
    *ThefirstargumentisthenameofthefileoftheFilter.
    *@tutorialPHP_Beautifier/Filter/Filter2.pkg#use
    *@paramstringnameoftheFilter
    *@paramarraysettingsfortheFilter
    *@returnbooltrueifFilterisloaded,falseifthesamefilterwasloadedpreviously
    *@throwsException
    */
    publicfunctionaddFilter($mFilter, $aSettings = array()) {
        if($mFilterinstanceOfPHP_Beautifier_Filter) {
            return $this->addFilterObject($mFilter);
        }
        $sFilterClass = 'PHP_Beautifier_Filter_' . $mFilter;
        if(!class_exists($sFilterClass)) {
            $this->addFilterFile($mFilter);
        }
        $oTemp = new $sFilterClass($this, $aSettings);
        // verifyifsameFilterisloaded
        if(in_array($oTemp, $this->aFilters, TRUE)) {
            returnfalse;
        }
        elseif($oTempinstanceofPHP_Beautifier_Filter) {
            $this->addFilterObject($oTemp);
        } else {
            thrownewException_PHP_Beautifier_Filter("'$sFilterClass'isn'tasubclassof'Filter'");
        }
    }
    
    /**
    *RemovesaFilter
    *@paramstringnameofthefilter
    *@returnbooltrueifFilterisremoved,falseotherwise
    */
    publicfunctionremoveFilter($sFilter) {
        $sFilterName = strtolower('PHP_Beautifier_Filter_' . $sFilter);
        
        foreach($this->aFiltersas$sId => $oFilter) {
            if(strtolower(get_class($oFilter)) == $sFilterName) {
                unset($this->aFilters[$sId]);
                returntrue;
            }
        }
        returnfalse;
    }
    
    /**
    *ReturntheFilterDescription
    *@seePHP_Beautifier_Filter::__toString();
    *@paramstringnameofthefilter
    *@returnmixedstringorfalse
    */
    publicfunctiongetFilterDescription($sFilter) {
        $aFilters = $this->getFilterListTotal();
        if(in_array($sFilter, $aFilters)) {
            $this->addFilterFile($sFilter);
            $sFilterClass = 'PHP_Beautifier_Filter_' . $sFilter;
            $oTemp = new $sFilterClass($this, array());
            return $oTemp;
        } else {
            returnfalse;
        }
    }
    
    /**
    *Addanewfiltertotheprocessor.
    *ThesystemwillprocessthefilterinLIFOorder
    *@paramstringnameoffilter
    *@seeprocess()
    *@returnbool
    *@throwsException
    */
    privatefunctionaddFilterFile($sFilter) {
        $sFilterClass = 'PHP_Beautifier_Filter_' . $sFilter;
        if(class_exists($sFilterClass)) {
            returntrue;
        }
        
        foreach($this->aFilterDirsas$sDir) {
            $sFile = $sDir . $sFilter . '.filter.php';
            if(file_exists($sFile)) {
                include_once $sFile;
                if(class_exists($sFilterClass)) {
                    returntrue;
                } else {
                    thrownewException_PHP_Beautifier_Filter("File'$sFile'exists,butdoesn'texistsfilter'$sFilterClass'");
                }
            }
        }
        thrownewException_PHP_Beautifier_Filter("Doesn'texistsfilter'$sFilter'");
    }
    
    /**
    *Getthenamesoftheloadedfilters
    *@returnarraylistofFilters
    */
    publicfunctiongetFilterList() {
        
        foreach($this->aFiltersas$oFilter) {
            $aOut[] = $oFilter->getName();
        }
        return $aOut;
    }
    
    /**
    *GetthelistofallavailableFiltersinalltheincludeDirs
    *@returnarraylistofFilters
    */
    publicfunctiongetFilterListTotal() {
        $aFilterFiles = array();
        
        foreach($this->aFilterDirsas$sDir) {
            $aFiles = PHP_Beautifier_Common::getFilesByPattern($sDir, ".*?\.filter\.php");
            array_walk($aFiles, array($this, 'getFilterList_FilterName'));
            $aFilterFiles = array_merge($aFilterFiles, $aFiles);
        }
        sort($aFilterFiles);
        return $aFilterFiles;
    }
    
    /**
    *Receiveapathtoafilterandreplaceitwiththenameoffilter
    */
    privatefunctiongetFilterList_FilterName(&$sFile) {
        $aMatch = array();
        preg_match("/\/([^\/]*?)\.filter\.php/", $sFile, $aMatch);
        $sFile = $aMatch[1];
    }
    publicfunctiongetIndentChar() {
        return $this->sIndentChar;
    }
    publicfunctiongetIndentNumber() {
        return $this->iIndentNumber;
    }
    publicfunctiongetIndent() {
        return $this->iIndent;
    }
    publicfunctiongetNewLine() {
        return $this->sNewLine;
    }
    
    /**
    *Characterusedforindentation
    *@paramstringusually''or"\t"
    */
    publicfunctionsetIndentChar($sChar) {
        $this->sIndentChar = $sChar;
    }
    
    /**
    *Numberofcharactersforindentation
    *@paramintussualy4forspaceor1fortabs
    */
    publicfunctionsetIndentNumber($iIndentNumber) {
        $this->iIndentNumber = $iIndentNumber;
    }
    
    /**
    *Characterusedasanewline
    *@paramstringussualy"\n","\r\n"or"\r"
    */
    publicfunctionsetNewLine($sNewLine) {
        $this->sNewLine = $sNewLine;
    }
    
    /**
    *Setthefileforbeautify
    *@paramstringpathtofile
    *@throwsException
    */
    publicfunctionsetInputFile($sFile) {
        $bCli = (php_sapi_name() == 'cli');
        if(strpos($sFile, '://') === FALSEand!file_exists($sFile) and !($bCliand$sFile == STDIN)) {
            thrownewException("File'$sFile'doesn'texists");
        }
        $this->sText = '';
        $this->sInputFile = $sFile;
        $fp = ($bCliand$sFile == STDIN) ? STDIN : fopen($sFile, 'r');
        do {
            $data = fread($fp, 8192);
            if(strlen($data) == 0) {
                break;
            }
            $this->sText .= $data;
        }
        
        while(true);
        if(!($bCliand$fp == STDIN)) {
            fclose($fp);
        }
        returntrue;
    }
    
    /**
    *Settheoutputfileforbeautify
    *@paramstringpathtofile
    */
    publicfunctionsetOutputFile($sFile) {
        $this->sOutputFile = $sFile;
    }
    
    /**
    *Savethebeautifiedcodetooutputfile
    *@paramstringpathtofile.Ifnull,{@link$sOutputFile}ifexists,throwexceptionotherwise
    *@seesetOutputFile();
    *@throwsException
    */
    publicfunctionsave($sFile = null) {
        $bCli = (php_sapi_name() == 'cli');
        if(!$sFile) {
            if(!$this->sOutputFile) {
                thrownewException("Can'tsavewithoutaoutputfile");
            } else {
                $sFile = $this->sOutputFile;
            }
        }
        $sText = $this->get();
        $fp = ($bCliand$sFile == STDOUT) ? STDOUT : @fopen($sFile, "w");
        if(!$fp) {
            thrownewException("Can'tsavefile$sFile");
        }
        fputs($fp, $sText, strlen($sText));
        if(!($bCliand$sFile == STDOUT)) {
            fclose($fp);
        }
        $this->oLog->log("Success:$sFilesaved", PEAR_LOG_INFO);
        returntrue;
    }
    
    /**
    *Setastringforbeautify
    *@paramstringMustbeprecededbyopentag
    */
    publicfunctionsetInputString($sText) {
        $this->sText = $sText;
    }
    
    /**
    *Resetallproperties
    */
    privatefunctionresetProperties() {
        $aProperties = array('aTokens' => array(), 'aOut' => array(), 'aModes' => array(), 'iCount' => 0, 'iIndent' => 0/*$this->iIndentNumber*/, 'aIndentStack' => array(/*$this->iIndentNumber*/ ), 
            'iArray' => 0, 'iParenthesis' => 0, 'currentWhitespace' => '', 'aAssocs' => array(), 'iControlLast' => null, 'aControlSeq' => array(), 'bBeautify' => true);
        
        foreach($aPropertiesas$sProperty => $sValue) {
            $this->$sProperty = $sValue;
        }
    }
    
    /**
    *Processthestringorfiletobeautify
    *@returnbooltrueonsuccess
    *@throwsException
    */
    publicfunctionprocess() {
        $this->oLog->log('Initprocessof' . (($this->sInputFile) ? 'file' . $this->sInputFile : 'string'), PEAR_LOG_DEBUG);
        $this->resetProperties();
        // iffiletypeisphp,usetoken_get_all
        // else,useaclassnamedPHP_Beautifier_Tokenizer_XXX
        // instancedwiththetextandgetthetokenswith
        // getTokens()
        if($this->sFileType == 'php') {
            $this->aTokens = token_get_all($this->sText);
        } else {
            $sClass = 'PHP_Beautifier_Tokenizer_' . ucfirst($this->sFileType);
            if(class_exists($sClass)) {
                $oTokenizer = new $sClass($this->sText);
                $this->aTokens = $oTokenizer->getTokens();
            } else {
                thrownewException("Filetype" . $this->sFileType . "notimplemented");
            }
        }
        $this->aOut = array();
        $iTotal = count($this->aTokens);
        $iPrevAssoc = false;
        // Sendasignaltothefilter,announcingtheinitoftheprocessingofafile
        
        foreach($this->aFiltersas$oFilter) {
            $oFilter->preProcess();
        }
        
        for($this->iCount = 0; $this->iCount < $iTotal; $this->iCount++) {
            $aCurrentToken = $this->aTokens[$this->iCount];
            if(is_string($aCurrentToken)) {
                $aCurrentToken = array(0 => $aCurrentToken, 1 => $aCurrentToken);
            }
            // ArrayNested->off();
            $sTextLog = PHP_Beautifier_Common::wsToString($aCurrentToken[1]);
            // ArrayNested->on();
            $sTokenName = (is_numeric($aCurrentToken[0])) ? token_name($aCurrentToken[0]) : '';
            $this->oLog->log("Token:" . $sTokenName . "[" . $sTextLog . "]", PEAR_LOG_DEBUG);
            $this->controlToken($aCurrentToken);
            $iFirstOut = count($this->aOut);
            // 5
            $bError = false;
            $this->aCurrentToken = $aCurrentToken;
            if($this->bBeautify) {
                
                foreach($this->aFiltersas$oFilter) {
                    $bError = true;
                    if($oFilter->handleToken($this->aCurrentToken) !== FALSE) {
                        $this->oLog->log('Filter:' . $oFilter->getName(), PEAR_LOG_DEBUG);
                        $bError = false;
                        break;
                    }
                }
            } else {
                $this->add($aCurrentToken[1]);
            }
            $this->controlTokenPost($aCurrentToken);
            $iLastOut = count($this->aOut);
            // settheassoc
            if(($iLastOut - $iFirstOut) > 0) {
                $this->aAssocs[$this->iCount] = array('offset' => $iFirstOut);
                if($iPrevAssoc !== FALSE) {
                    $this->aAssocs[$iPrevAssoc]['length'] = $iFirstOut - $this->aAssocs[$iPrevAssoc]['offset'];
                }
                $iPrevAssoc = $this->iCount;
            }
            if($bError) {
                thrownewException("Can'processtoken:" . var_dump($aCurrentToken));
            }
        }
        //~for
        // generatethelastassoc
        if(count($this->aOut) == 0) {
            if($this->sFile) {
                thrownewException("Nothingonoutputfor" . $this->sFile . "!");
            } else {
                thrownewException("Nothingonoutput!");
            }
        }
        $this->aAssocs[$iPrevAssoc]['length'] = (count($this->aOut) - 1) - $this->aAssocs[$iPrevAssoc]['offset'];
        // Post-processing
        
        foreach($this->aFiltersas$oFilter) {
            $oFilter->postProcess();
        }
        $this->oLog->log('Endprocess', PEAR_LOG_DEBUG);
        returntrue;
    }
    
    /**
    *Getthereferenceto{@link$aOut},basedonthenumberofthetoken
    *@paraminttokennumber
    *@returnmixedfalsearrayorfalseiftokendoesn'texists
    */
    publicfunctiongetTokenAssoc($iIndex) {
        return (array_key_exists($iIndex, $this->aAssocs)) ? $this->aAssocs[$iIndex] : false;
    }
    
    /**
    *Gettheoutputforthespecifiedtoken
    *@paraminttokennumber
    *@returnmixedstringorfalseiftokendoesn'texists
    */
    publicfunctiongetTokenAssocText($iIndex) {
        if(!($aAssoc = $this->getTokenAssoc($iIndex))) {
            returnfalse;
        }
        return (implode('', array_slice($this->aOut, $aAssoc['offset'], $aAssoc['length'])));
    }
    
    /**
    *Replacetheoutputforspecifiedtoken
    *@paraminttokennumber
    *@paramstringreplacetext
    *@returnbool
    */
    publicfunctionreplaceTokenAssoc($iIndex, $sText) {
        if(!($aAssoc = $this->getTokenAssoc($iIndex))) {
            returnfalse;
        }
        $this->aOut[$aAssoc['offset']] = $sText;
        
        for($x = 0; $x < $aAssoc['length'] - 1; $x++) {
            $this->aOut[$aAssoc['offset'] + $x + 1] = '';
        }
        returntrue;
    }
    
    /**
    *Returnthefunctionforatokenconstantorstring.
    *@parammixedtokenconstantorstring
    *@returnmixednameoffunctionorfalse
    */
    publicfunctiongetTokenFunction($sTokenType) {
        return (array_key_exists($sTokenType, $this->aTokenFunctions)) ? strtolower($this->aTokenFunctions[$sTokenType]) : false;
    }
    
    /**
    *Processacallbackfromthecodetobeautify
    *@paramarraythirdparameterfrompreg_match
    *@returnbool
    *@usescontrolToken()
    */
    privatefunctionprocessCallback($aMatch) {
        if(stristr('php_beautifier', $aMatch[1])andmethod_exists($this, $aMatch[3])) {
            if(preg_match("/^(set|add)/i", $aMatch[3]) and !stristr('file', $aMatch[3])) {
                eval('$this->' . $aMatch[2] . ";");
                returntrue;
            }
        } else {
            
            foreach($this->aFiltersas$iIndex => $oFilter) {
                if(strtolower(get_class($oFilter)) == 'php_beautifier_filter_' . strtolower($aMatch[1])andmethod_exists($oFilter, $aMatch[3])) {
                    eval('$this->aFilters[' . $iIndex . ']->' . $aMatch[2] . ";");
                    returntrue;
                }
            }
        }
        returnfalse;
    }
    
    /**
    *Assignvalueforsomevariableswiththeinformationofthetoken
    *@paramarraycurrenttoken
    */
    privatefunctioncontrolToken($aCurrentToken) {
        // isacontrolstructureopener?
        if(in_array($aCurrentToken[0], $this->aControlStructures)) {
            $this->pushControlSeq($aCurrentToken);
            $this->iControlLast = $aCurrentToken[0];
        }
        // isacontrolstructurecloser?
        if(in_array($aCurrentToken[0], $this->aControlStructuresEnd)) {
            $this->popControlSeq();
        }
        ($aCurrentToken[0]) {
            caseT_COMMENT : // callback!
            $aMatch = array();
            if(preg_match("/\/\/\s*(.*?)->((.*)\((.*)\))/", $aCurrentToken[1], $aMatch)) {
                
                try {
                    $this->processCallback($aMatch);
                }
                 catch(Exception$oExp) {
                }
            }
            break;
            
            caseT_FUNCTION : $this->setMode('function');
            break;
            
            caseT_CLASS : $this->setMode('class');
            break;
            
            caseT_ARRAY : $this->iArray++;
            break;
            
            caseT_WHITESPACE : $this->currentWhitespace = $aCurrentToken[1];
            break;
            
            case'{' : if($this->isPreviousTokenConstant(T_VARIABLE) or ($this->isPreviousTokenConstant(T_STRING) and $this->getPreviousTokenConstant(2) == T_OBJECT_OPERATOR) or $this->
                isPreviousTokenConstant(T_OBJECT_OPERATOR)) {
                $this->setMode('string_index');
            }
            break;
            
            case'(' : $this->iParenthesis++;
            $this->pushControlParenthesis();
            break;
            
            case')' : $this->iParenthesis--;
            break;
            
            case'?' : $this->setMode('ternary_operator');
            $this->iTernary++;
            break;
            
            case'"' : ($this->getMode('double_quote')) ? $this->unsetMode('double_quote') : $this->setMode('double_quote');
            break;
            
            caseT_START_HEREDOC : $this->setMode('double_quote');
            break;
            
            caseT_END_HEREDOC : $this->unsetMode('double_quote');
            break;
        }
        if($this->getTokenFunction($aCurrentToken[0]) == 't_include') {
            $this->setMode('include');
        }
    }
    
    /**
    *Assignvalueforsomevariableswiththeinformationofthetoken,afterprocessing
    *@paramarraycurrenttoken
    */
    privatefunctioncontrolTokenPost($aCurrentToken) {
        ($aCurrentToken[0]) {
            case')' : if($this->iArray) {
                $this->iArray--;
            }
            $this->popControlParenthesis();
            break;
            
            case'}' : if($this->getMode('string_index')) {
                $this->unsetMode('string_index');
            } else {
                $prevIndex = 1;
                
                while($this->isPreviousTokenConstant(array(T_COMMENT, T_DOC_COMMENT), $prevIndex)) {
                    $prevIndex++;
                }
                
                $this->oLog->log('endbracket:' . $this->getPreviousTokenContent($prevIndex), PEAR_LOG_DEBUG);
                
                if($this->isPreviousTokenContent(array(';', '}', '{'), $prevIndex)) {
                    if(end($this->aControlSeq) != T_DO) {
                        $this->popControlSeq();
                    } else {
                        $this->DoWhileBeginEnd = true;
                    }
                }
            }
            break;
            
            case';' : // Ifisawhileinadowhilestructure
            if(isset($this->aControlSeq) && (end($this->aControlSeq) == T_WHILE)) {
                $counter = 0;
                $openParenthesis = 0;
                do {
                    $counter++;
                    $prevToken = $this->getPreviousTokenContent($counter);
                    if($prevToken == "(") {
                        $openParenthesis++;
                    }
                }
                
                while($prevToken != "{" && $prevToken != "while");
                if($prevToken == "while" && $openParenthesis == 1) {
                    if($this->DoWhileBeginEnd) {
                        $this->popControlSeq();
                        $this->DoWhileBeginEnd = false;
                    }
                    $this->popControlSeq();
                }
            }
            break;
            
            case'{' : $this->unsetMode('function');
            break;
        }
        if($this->getTokenFunction($aCurrentToken[0]) == 't_colon') {
            if($this->iTernary) {
                $this->iTernary--;
            }
            if(!$this->iTernary) {
                $this->unsetMode('ternary_operator');
            }
        }
    }
    
    /**
    *Pushacontrolconstructtothestack
    *@paramarraycurrenttoken
    */
    privatefunctionpushControlSeq($aToken) {
        $this->oLog->log('PushControl:' . $aToken[0] . "->" . $aToken[1], PEAR_LOG_DEBUG);
        array_push($this->aControlSeq, $aToken[0]);
    }
    
    /**
    *Popacontrolconstructfromthestack
    *@returninttokenconstant
    */
    privatefunctionpopControlSeq() {
        $aEl = array_pop($this->aControlSeq);
        $this->oLog->log('PopControl:' . $this->getTokenName($aEl), PEAR_LOG_DEBUG);
        return $aEl;
    }
    
    /**
    *PushanewControlInstructiononthestack
    */
    privatefunctionpushControlParenthesis() {
        $iPrevious = $this->getPreviousTokenConstant();
        $this->oLog->log("PushParenthesis:$iPrevious->" . $this->getPreviousTokenContent(), PEAR_LOG_DEBUG);
        array_push($this->aControlParenthesis, $iPrevious);
    }
    
    /**
    *PopthelastControlinstructionforparenthesisfromthestack
    *@returnintconstant
    */
    privatefunctionpopControlParenthesis() {
        $iPop = array_pop($this->aControlParenthesis);
        $this->oLog->log('PopParenthesis:' . $iPop, PEAR_LOG_DEBUG);
        return $iPop;
    }
    
    /**
    *Setthefiletype
    *@paramstring
    */
    publicfunctionsetFileType($sType) {
        $this->sFileType = $sType;
    }
    
    /**
    *SettheBeautifieronoroff
    *@parambool
    */
    publicfunctionsetBeautify($sFlag) {
        $this->bBeautify = (bool) $sFlag;
    }
    
    /**
    *Showthebeautifiedcode
    */
    publicfunctionshow() {
        echo $this->get();
    }
    
    /**
    *Activateordeactivatethisominoushack
    *Ifyouneedtomaintainsomespecialwhitespace
    *youcanactivatethishackanduse(deletethespacebetween*and/)
    *<code>/**ndps**/
     <  / code >  * in {
        @linkget()
    }, thistextwillbeerased .  * @seeremoveWhitespace() * @seePHP_Beautifier_Filter_NewLines *  / publicfunctionsetNoDeletePreviousSpaceHack($bFlag = true) {
        $this->bNdps = $bFlag;
    }
    
    /**
    *Returnsthebeautifiedcode
    *@seesetNoDeletePreviousSpaceHack()
    *@returnstring
    */
    publicfunctionget() {
        if(!$this->bNdps) {
            returnimplode('', $this->aOut);
        } else {
            returnstr_replace('/**ndps**/', '', implode('', $this->aOut));
        }
    }
    
    /**
    *Returnsthevalueofasettings
    *@paramstringNameofthesetting
    *@returnmixedValueofthesettingsorfalse
    */
    publicfunctiongetSetting($sKey) {
        return (array_key_exists($sKey, $this->aSettings)) ? $this->aSettings[$sKey] : false;
    }
    
    /**
    *Getthetokenconstantforthecurrentcontrolconstruct
    *@paramintcurrenttoken-'x'
    *@returnmixedtokenconstantorfalse
    */
    publicfunctiongetControlSeq($iRet = 0) {
        $iIndex = count($this->aControlSeq) - $iRet - 1;
        return ($iIndex >= 0) ? $this->aControlSeq[$iIndex] : false;
    }
    
    /**
    *GetthetokenconstantforthecurrentParenthesis
    *@paramintcurrenttoken-'x'
    *@returnmixedtokenconstantorfalse
    */
    publicfunctiongetControlParenthesis($iRet = 0) {
        $iIndex = count($this->aControlParenthesis) - $iRet - 1;
        return ($iIndex >= 0) ? $this->aControlParenthesis[$iIndex] : false;
    }
    ////
    // Modemethods
    ////
    
    /**
    *Setamodetotrue
    *@paramstringnameofthemode
    */
    publicfunctionsetMode($sKey) {
        $this->aModes[$sKey] = true;
    }
    
    /**
    *Setamodetofalse
    *@paramstringnameofthemode
    */
    publicfunctionunsetMode($sKey) {
        $this->aModes[$sKey] = false;
    }
    
    /**
    *Getthestateofamode
    *@paramstringnameofthemode
    *@returnbool
    */
    publicfunctiongetMode($sKey) {
        returnarray_key_exists($sKey, $this->aModes) ? $this->aModes[$sKey] : false;
    }
    /////
    // Filtermethods
    /////
    
    /**
    *Addastringtotheoutput
    *@paramstring
    */
    publicfunctionadd($token) {
        $this->aOut[] = $token;
    }
    
    /**
    *Deletethelastaddedoutput(s)
    *@paramintnumberofoutputstodrop
    *@deprecated
    */
    publicfunctionpop($iReps = 1) {
        
        for($x = 0; $x < $iReps; $x++) {
            $sLast = array_pop($this->aOut);
        }
        return $sLast;
    }
    
    /**
    *AddIndenttotheoutput
    *@see$sIndentChar
    *@see$iIndentNumber
    *@see$iIndent
    */
    publicfunctionaddIndent() {
        $this->aOut[] = str_repeat($this->sIndentChar, $this->iIndent);
    }
    
    /**
    *Setastringtoputbeforeanewline
    *Youcouldusethistoputastandardcommentaftersomesentences
    *ortoaddextranewlines
    */
    publicfunctionsetBeforeNewLine($sText) {
        $this->sBeforeNewLine = $sText;
    }
    
    /**
    *Addanewlinetotheoutput
    *@see$sNewLine
    */
    publicfunctionaddNewLine() {
        if(!is_null($this->sBeforeNewLine)) {
            $this->aOut[] = $this->sBeforeNewLine;
            $this->sBeforeNewLine = null;
        }
        $this->aOut[] = $this->sNewLine;
    }
    
    /**
    *Addanewlineandaindenttooutput
    *@see$sIndentChar
    *@see$iIndentNumber
    *@see$iIndent
    *@see$sNewLine
    */
    publicfunctionaddNewLineIndent() {
        if(!is_null($this->sBeforeNewLine)) {
            $this->aOut[] = $this->sBeforeNewLine;
            $this->sBeforeNewLine = null;
        }
        $this->aOut[] = $this->sNewLine;
        $this->aOut[] = str_repeat($this->sIndentChar, $this->iIndent);
    }
    
    /**
    *IncrementstheindentinXchars.
    *Ifparamomitted,used{@linkiIndentNumber}
    *@paramintincrementindentinxchars
    */
    publicfunctionincIndent($iIncr = false) {
        if(!$iIncr) {
            $iIncr = $this->iIndentNumber;
        }
        array_push($this->aIndentStack, $iIncr);
        $this->iIndent += $iIncr;
    }
    
    /**
    *Decrementstheindent.
    */
    publicfunctiondecIndent() {
        if(count($this->aIndentStack > 1)) {
            $iLastIndent = array_pop($this->aIndentStack);
            $this->iIndent -= $iLastIndent;
        }
    }
    //
    ////
    // Methodstolookupprevious,nexttokens
    ////
    //
    
    /**
    *Getthe'x'significant(nonwhitespace)previoustoken
    *@paramintcurrent-xtoken
    *@returnmixedarrayorfalse
    */
    privatefunctiongetPreviousToken($iPrev = 1) {
        
        for($x = $this->iCount - 1; $x >= 0; $x--) {
            $aToken = &$this->getToken($x);
            if($aToken[0] != T_WHITESPACE) {
                $iPrev--;
                if(!$iPrev) {
                    return $aToken;
                }
            }
        }
    }
    
    /**
    *Getthe'x'significant(nonwhitespace)nexttoken
    *@paramintcurrent+xtoken
    *@returnarray
    */
    privatefunctiongetNextToken($iNext = 1) {
        
        for($x = $this->iCount + 1; $x < (count($this->aTokens) - 1); $x++) {
            $aToken = &$this->getToken($x);
            if($aToken[0] != T_WHITESPACE) {
                $iNext--;
                if(!$iNext) {
                    return $aToken;
                }
            }
        }
    }
    
    /**
    *Returntrueifanyoftheconstantdefinedisparam1istheprevious'x'constant
    *@parammixedint(constant)orarrayofconstants
    *@returnbool
    */
    publicfunctionisPreviousTokenConstant($mValue, $iPrev = 1) {
        if(!is_array($mValue)) {
            $mValue = array($mValue);
        }
        $iPrevious = $this->getPreviousTokenConstant($iPrev);
        returnin_array($iPrevious, $mValue);
    }
    
    /**
    *Returntrueifanyofthecontentdefinedisparam1istheprevious'x'content
    *@parammixedstring(content)orarrayofcontents
    *@returnbool
    */
    publicfunctionisPreviousTokenContent($mValue, $iPrev = 1) {
        if(!is_array($mValue)) {
            $mValue = array($mValue);
        }
        $iPrevious = $this->getPreviousTokenContent($iPrev);
        returnin_array($iPrevious, $mValue);
    }
    
    /**
    *Returntrueifanyoftheconstantdefinedinparam1isthenext'x'content
    *@parammixedint(constant)orarrayofconstants
    *@returnbool
    */
    publicfunctionisNextTokenConstant($mValue, $iPrev = 1) {
        if(!is_array($mValue)) {
            $mValue = array($mValue);
        }
        $iNext = $this->getNextTokenConstant($iPrev);
        returnin_array($iNext, $mValue);
    }
    
    /**
    *Returntrueifanyofthecontentdefinedisparam1isthenext'x'content
    *@parammixedstring(content)orarrayofcontents
    *@returnbool
    */
    publicfunctionisNextTokenContent($mValue, $iPrev = 1) {
        if(!is_array($mValue)) {
            $mValue = array($mValue);
        }
        $iNext = $this->getNextTokenContent($iPrev);
        returnin_array($iNext, $mValue);
    }
    
    /**
    *Getthe'x'significant(nonwhitespace)previoustokenconstant
    *@paramintcurrent-xtoken
    *@returnint
    */
    publicfunctiongetPreviousTokenConstant($iPrev = 1) {
        $sToken = $this->getPreviousToken($iPrev);
        return $sToken[0];
    }
    
    /**
    *Getthe'x'significant(nonwhitespace)previoustokentext
    *@paramintcurrent-xtoken
    *@returnstring
    */
    publicfunctiongetPreviousTokenContent($iPrev = 1) {
        $mToken = $this->getPreviousToken($iPrev);
        return (is_string($mToken)) ? $mToken : $mToken[1];
    }
    publicfunctiongetNextTokenNonCommentConstant($iPrev = 1) {
        do {
            $aToken = $this->getNextToken($iPrev);
            $iPrev++;
        }
        
        while($aToken[0] == T_COMMENT);
        return $aToken[0];
    }
    
    /**
    *Getthe'x'significant(nonwhitespace)nexttokenconstant
    *@paramintcurrent+xtoken
    *@returnint
    */
    publicfunctiongetNextTokenConstant($iPrev = 1) {
        $sToken = $this->getNextToken($iPrev);
        return $sToken[0];
    }
    
    /**
    *Getthe'x'significant(nonwhitespace)nexttokentext
    *@paramintcurrent+xtoken
    *@returnint
    */
    publicfunctiongetNextTokenContent($iNext = 1) {
        $mToken = $this->getNextToken($iNext);
        return (is_string($mToken)) ? $mToken : $mToken[1];
    }
    
    /**
    *Returnthewhitespaceprevioustocurrenttoken
    *Ex.:Youhave
    *'if($a)'
    *ifyoucallthisfuncionon'if',youget''
    *@todoimplementsamoreeconomicwaytohandlethis.
    *@returnstringpreviouswhitespace
    */
    publicfunctiongetPreviousWhitespace() {
        $sWhiteSpace = '';
        $aMatch = array();
        
        
        for($x = $this->iCount - 1; $x >= 0; $x--) {
            $this->oLog->log("spn:$x", PEAR_LOG_DEBUG);
            $aToken = $this->getToken($x);
            if(is_array($aToken)) {
                if($aToken[0] == T_WHITESPACE) {
                    $sWhiteSpace .= $aToken[1];
                }
                elseif(preg_match("/([\s\r\n]+)$/", $aToken[1], $aMatch)) {
                    $sWhiteSpace .= $aMatch[0];
                    // ArrayNested->off();
                    $this->oLog->log("+space-token-with-sp:[" . PHP_Beautifier_Common::wsToString($sWhiteSpace) . "]", PEAR_LOG_DEBUG);
                    // ArrayNested->on();
                    return $sWhiteSpace;
                }
            } else {
                $this->oLog->log("+space-token-without-sp:[" . PHP_Beautifier_Common::wsToString($sWhiteSpace) . "]", PEAR_LOG_DEBUG);
                return $sWhiteSpace;
            }
        }
        // Strange,but...
        $this->oLog->log("+space:[" . PHP_Beautifier_Common::wsToString($sWhiteSpace) . "]", PEAR_LOG_DEBUG);
        return $sWhiteSpace;
    }
    
    /**
    *Removeallwhitespacefromtheprevioustag
    *@returnboolfalseifprevioustokenwasshortcommentorheredoc
    *(don'tremovews)
    *trueanythingelse.
    */
    publicfunctionremoveWhitespace() {
        // iftheprevioustokenwas
        //-ashortcomment
        //-heredoc
        // don'tremovewhitespace!
        //
        if($this->isPreviousTokenConstant(T_COMMENT)andpreg_match("/^(\/\/|#)/", $this->getPreviousTokenContent())) {
            // Hereforshortcomment
            returnfalse;
        }
        elseif($this->getPreviousTokenConstant(2) == T_END_HEREDOC) {
            // Andhereforheredoc
            returnfalse;
        }
        $pop = 0;
        
        for($i = count($this->aOut) - 1; $i >= 0; $i--) {
            // gobackwards
            $cNow = &$this->aOut[$i];
            if(strlen(trim($cNow)) == 0) {
                // onlyspace
                array_pop($this->aOut);
                // deleteit!
                $pop++;
            } else {
                // wefindsomething!
                $cNow = rtrim($cNow);
                // rtrimout
                break;
            }
        }
        $this->oLog->log("-space$pop", PEAR_LOG_DEBUG);
        returntrue;
    }
    
    /**
    *Getatokenbynumber
    *@paramintnumberofthetoken
    *@returnarray
    */
    publicfunction&getToken($iIndex) {
        if($iIndex < 0 or $iIndex > count($this->aTokens)) {
            returnfalse;
        } else {
            return $this->aTokens[$iIndex];
        }
    }
    publicfunctionopenBraceDontProcess() {
        return $this->isPreviousTokenConstant(T_VARIABLE) or $this->isPreviousTokenConstant(T_OBJECT_OPERATOR) or ($this->isPreviousTokenConstant(T_STRING) and $this->getPreviousTokenConstant(2) == 
            T_OBJECT_OPERATOR) or $this->getMode('double_quote');
    }
}

?>

