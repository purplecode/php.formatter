<?php

require_once ('Settings.php');
require_once ('StringUtils.php');

class TokenTransformations {

	private $replacements;

	private function textLength($text) {
		return strlen($text) + substr_count($text, "\t") * (Settings::TABS_LENGTH - 1);
	}

	// @VisibleForTesting
	public function beautifyComment($comment) {
		if(Settings::SPACE_AFTER_COMMENT) {
			$comment = StringUtils::replace(array('/\/\/(\w)/', '/#(\w)/'), array('// \1', '# \1'), $comment);
		}
		return $comment;
	}

	public function get($tokenKey) {
		return $this->replacements[$tokenKey];
	}

	public function has($tokenKey) {
		return isset($this->replacements[$tokenKey]);
	}

	public function __construct() {

		$replacements = array(
			T_ABSTRACT => 'abstract ',
			T_ARRAY => 'array',
			T_ARRAY_CAST => '(array) ',
			T_BOOL_CAST => '(bool) ',
			T_BREAK => 'break',
			T_CLASS_C => '__CLASS__',
			T_CLONE => 'clone ',
			T_CONST => 'const ',
			T_CONTINUE => 'continue',
			T_DEC => '--',
			T_DECLARE => 'declare',
			T_DEFAULT => 'defalt',
			T_DIR => '__DIR__',
			T_DOUBLE_CAST => '(double) ',
			T_DOUBLE_COLON => '::', 
			T_ECHO => 'echo ', 
			T_EMPTY => 'empty',
			T_ENDDECLARE => 'enddeclare', 
			T_EXIT => 'exit',
			T_FILE => '__FILE__', 
			T_FINAL => 'final ', 
			T_FUNC_C =>	'__FUNCTION__',
			T_GLOBAL => 'global ', 
			T_GOTO => 'goto ', 
			T_USE => ' use ',
			T_INC => '++', 
			T_INCLUDE => 'include ', 
			T_INCLUDE_ONCE => 'include_once ',
			T_INSTANCEOF => ' instanceof ',
			T_INT_CAST => ' (int) ', 
			T_INTERFACE => 'interface ', 
			T_ISSET => 'isset',
			T_LINE => '__LINE__',
			T_LIST => 'list',
			T_METHOD_C => '__METHOD__',
			T_MINUS_EQUAL => ' -= ',
			T_MOD_EQUAL => ' %= ',
			T_MUL_EQUAL => ' *= ',
			T_NAMESPACE => 'namespace ',
			T_NS_C => '__NAMESPACE__',
			T_NS_SEPARATOR => '\\',
			T_NEW => 'new ',
			T_OBJECT_CAST => '(object) ',
			T_OBJECT_OPERATOR => '->',
			T_OPEN_TAG_WITH_ECHO => '<?=',
			T_PAAMAYIM_NEKUDOTAYIM => '::',
			T_PRINT => 'print',
			T_REQUIRE => 'require ',
			T_REQUIRE_ONCE => 'require_once ',
			T_STATIC => 'static ',
			T_STRING_CAST => '(string) ',
			T_THROW => 'throw ',
			T_UNSET => 'unset',
			T_UNSET_CAST => '(unset) ',
			T_VAR => 'var ',
			'!' => '!', 
			//T_END_HEREDOC => '$$', 
			//T_EVAL => '$$', 
			//T_HALT_COMPILER => '$$', 
			//T_ENCAPSED_AND_WHITESPACE => '$$',
			//T_LNUMBER => '$$',
			//T_NUM_STRING => '$$',
			//T_START_HEREDOC => '$$',
			//T_STRING_VARNAME => '$$',
			//T_VARIABLE => '$$',
			//T_CHARACTER => '$$',
			//T_CONSTANT_ENCAPSED_STRING => '$$',
			//T_CURLY_OPEN => '$$',
			//T_DNUMBER => ' $$ ',
			//T_BAD_CHARACTER => '$$',
			//T_DOLLAR_OPEN_CURLY_BRACES => '$$', 
			
			// class names, function names, etc.
			T_STRING => function ($content, $state, $word) {
				$content->append($word);
				if(in_array($word, array('class', 'function'))) {
					$content->space();
				}
			},
			T_COMMENT => function ($content, $state, $word) {
				$word = $this->beautifyComment($word);
				foreach(explode("\n", $word) as $commentLine) {
					if(!empty($commentLine)) {
						$content->append(trim($commentLine))->newline();
					}
				}
			},
			T_DOC_COMMENT => function ($content, $state, $word) {
				foreach(explode("\n", $word) as $commentLine) {
					$content->newline()->append(trim($commentLine));
				}
				$content->newline();
			}, 
			T_RETURN => function ($content, $state, $word) {
				$content->append($word)->space();
			}, 
			T_IF => function ($content, $state, $word) {
				$content->append($word);
			},
			T_ELSE => function ($content, $state, $word) {
				if(Settings::BRACES_IN_NEW_LINE) {
					$content->newline();
				} else {
					$content->rtrim()->space();
				}
				$content->append($word);
			},	
			T_TRY => function ($content, $state, $word) {
				$content->newline();
				$content->append($word);
			},
			T_CATCH => function ($content, $state, $word) {
				if(Settings::BRACES_IN_NEW_LINE) {
					$content->newline();
				} else {
					$content->space();
				}
				$content->append($word);
			},
			T_SWITCH => function ($content, $state, $word) {
				// TODO
			},
			T_DEFAULT => function ($content, $state, $word) {
				// TODO
			},
			T_CLASS => function ($content, $state, $word) {
				$content->newline()->append($word)->space();
			},
			T_FUNCTION => function ($content, $state, $word) {
				$content->append($word)->space();
			},
			'{' => function ($content, $state, $word) {
				if(Settings::BRACES_IN_NEW_LINE) {
					$content->newline();
				} else {
					$content->space();
				}
				$state->indent++;
				$content->append($word)->newline();
			},
			'}' => function ($content, $state, $word) {
				if(Settings::BRACES_IN_NEW_LINE) {
					$content->newline();
				} else {
					$content->space();
				}
				$state->indent--;
				$content->rtrim()->newline()->append($word)->newline();
			},
			'(' => function ($content, $state, $word) {
				$content->br()->openBraces();
			},
			')' => function ($content, $state, $word) {
				$content->closeBraces()->br();
			},
			';' => function ($content, $state, $word) {
				$content->rtrim()->append($word)->br()->newline();
			},
			',' => function ($content, $state, $word) {
				$content->rtrim()->append($word)->br()->space();
			},
			'?' => function ($content, $state, $word) {
				$content->space()->append($word)->br()->space();
			},
			':' => function ($content, $state, $word) {
				$content->space()->append($word)->br()->space();
			},
			T_OPEN_TAG => 
			function ($content, $state, $word) {
				$state->index = 0;
				if(!$content->isEmpty()) {
					$content->newline();
				}
				$content->append($word)->newline();
			},
			T_CLOSE_TAG => function ($content, $state, $word) {
				$state->index = 0;
				$content->newline()->append($word)->newline();
			},
			T_WHITESPACE => function ($content, $state, $word) {
				if($word == "\n\n") {
					$content->newline();
				}
			},
			T_INLINE_HTML => function ($content, $state, $word) {
				// TODO
			});

		$_ = function($tokenKeys, $function) use (&$replacements) {
			if(is_array($tokenKeys)) {
				foreach ($tokenKeys as $tokenKey) {
					$replacements[$tokenKey] = $function;
				}
			} else {
				$replacements[$tokenKeys] = $function;
			}
		};

		$_(array(
				T_PRIVATE, 
				T_PUBLIC, 
				T_PROTECTED
			),
			function ($content, $state, $word) {
				if(StringUtils::matches('/}\n\s*?$/', $content->getContent())) {
					$content->newline();
				}			
				$content->append($word)->space();
		});

		$_(array(
				T_WHILE,
				T_FOR,
				T_FOREACH
			),
			function ($content, $state, $word) {
				$content->newline()->append($word);
		});

		$_(array(
				'+', //  ' + '
				'-', //  ' - '
				'*', //  ' * '
				'/', //  ' / '
				'%', //  ' % '
				'.', //  ' . '
				'>', //  ' > '
				'<', //  ' < '
				'=', //  ' = '
				'^', //  ' ^ '
				T_AND_EQUAL, 			//  ' &= '
				T_AS, 					//  ' as '
				T_BOOLEAN_OR, 			//  ' || '
				T_CONCAT_EQUAL, 		//  ' .= '
				T_BOOLEAN_AND, 			//  ' && '
				T_DIV_EQUAL, 			//  ' /= '
				T_DOUBLE_ARROW, 		//  ' => '
				T_IS_EQUAL, 			//  ' == '
				T_IS_GREATER_OR_EQUAL,	//  ' >= '
				T_IS_IDENTICAL, 		//  ' === '
				T_IS_NOT_EQUAL, 		//  ' != '
				T_IS_NOT_IDENTICAL, 	//  ' !== '
				T_IS_SMALLER_OR_EQUAL,  //  ' <= '
				T_LOGICAL_AND, 			//  ' and '
				T_LOGICAL_OR, 			//  ' or '
				T_LOGICAL_XOR,			//  ' xor '
				T_OR_EQUAL, 			//  ' |= '
				T_PLUS_EQUAL, 			//  ' += '
				T_SL, 					//  ' << '
				T_SL_EQUAL, 			//  ' <<= '
				T_SR, 					//  ' >> '
				T_SR_EQUAL, 			//  ' >>= '
				T_XOR_EQUAL, 			//  ' ^= '
				T_IMPLEMENTS, 			// 	' implements '
				T_EXTENDS 				//  ' extends '
			), 
			function ($content, $state, $word) {
				$content->space()->append($word)->br()->space();
			});
			
			$this->replacements = $replacements;
	}
}