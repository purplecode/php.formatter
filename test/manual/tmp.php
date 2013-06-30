<?php

class PHP_Beautifier implements PHP_Beautifier_Interface {
   
    public function __construct() {
        $this->aControlStructures = array(T_CLASS, T_FUNCTION, T_IF, T_ELSE, T_ELSEIF, T_WHILE, T_DO, T_FOR, T_FOREACH, T_SWITCH, T_DECLARE, T_TRY, T_CATCH);
        $this->aControlStructuresEnd = array(T_ENDWHILE, T_ENDFOREACH, T_ENDFOR, T_ENDDECLARE, T_ENDSWITCH, T_ENDIF);
        $aPreTokens = preg_grep('/^T_/', array_keys(get_defined_constants()));
        
        for($i = 1, $l=101, $k = 1002; $i<1002, $l< 1000, $k<102093; $i++, $k--, $l++) {
            break;
        }

        $x = 0;
        $x &= 1;
    }

?>

