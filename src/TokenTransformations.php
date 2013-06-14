<?php

require_once ('Settings.php');

class TokenTransformations {

	private $replacements;

	private function textLength($text) {
		return strlen($text) + substr_count($text, "\t") * (Settings::TABS_LENGTH - 1);
	}

	public function get($tokenKey) {
		return $this->replacements[$tokenKey];
	}

	public function has($tokenKey) {
		return isset($this->replacements[$tokenKey]);
	}	

	public function __construct() {
		
		$this->replacements = array(
			T_ABSTRACT => 'abstract ',
			T_AND_EQUAL => ' &= ',
			T_ARRAY => 'array',
			T_ARRAY_CAST => '(array) ',
			T_AS => ' as ',
			//T_BAD_CHARACTER => '$$',
			T_BOOLEAN_AND => ' && ',
			T_BOOLEAN_OR => ' || ',
			T_BOOL_CAST => '(bool) ',
			T_BREAK => 'break',
			//T_CHARACTER => '$$',
			T_CLASS_C => '__CLASS__',
			T_CLONE => 'clone ',
			T_CONCAT_EQUAL => ' .= ',
			T_CONST => 'const ',
			//T_CONSTANT_ENCAPSED_STRING => '$$',
			T_CONTINUE => 'continue',
			//T_CURLY_OPEN => '$$',
			T_DEC => '--',
			T_DECLARE => 'declare',
			T_DEFAULT => 'defalt',
			T_DIR => '__DIR__',
			T_DIV_EQUAL => ' /= ',
			//T_DNUMBER => ' $$ ',
			//T_DOLLAR_OPEN_CURLY_BRACES => '$$', 
			T_DOUBLE_ARROW => ' => ',
			T_DOUBLE_CAST => '(double) ',
			T_DOUBLE_COLON => '::', 
			T_ECHO => 'echo ', 
			T_EMPTY => 'empty',
			//T_ENCAPSED_AND_WHITESPACE => '$$',
			T_ENDDECLARE => 'enddeclare', 
			//T_END_HEREDOC => '$$', 
			//T_EVAL => '$$', 
			T_EXIT => 'exit',
			T_EXTENDS => ' extends ', 
			T_FILE => '__FILE__', 
			T_FINAL => 'final ', 
			T_FUNC_C =>	'__FUNCTION__',
			T_GLOBAL => 'global ', 
			T_GOTO => 'goto ', 
			//T_HALT_COMPILER => '$$', 
			T_IMPLEMENTS =>	' implements ',
			T_INC => '++', 
			T_INCLUDE => 'include ', 
			T_INCLUDE_ONCE => 'include_once ',
			T_INSTANCEOF => ' instanceof ',
			T_INT_CAST => ' (int) ', 
			T_INTERFACE => 'interface ', 
			T_ISSET => 'isset',
			T_IS_EQUAL => ' == ',
			T_IS_GREATER_OR_EQUAL => ' >= ', 
			T_IS_IDENTICAL => ' === ', 
			T_IS_NOT_EQUAL => ' != ',
			T_IS_NOT_IDENTICAL => ' !== ',
			T_IS_SMALLER_OR_EQUAL => ' <= ',
			T_LINE => '__LINE__',
			T_LIST => 'list',
			//T_LNUMBER => '$$',
			T_LOGICAL_AND => ' and ',
			T_LOGICAL_OR => ' or ',
			T_LOGICAL_XOR => ' xor ',
			T_METHOD_C => '__METHOD__',
			T_MINUS_EQUAL => ' -= ',
			T_MOD_EQUAL => ' %= ',
			T_MUL_EQUAL => ' *= ',
			T_NAMESPACE => 'namespace ',
			T_NS_C => '__NAMESPACE__',
			T_NS_SEPARATOR => '\\',
			T_NEW => 'new ',
			//T_NUM_STRING => '$$',
			T_OBJECT_CAST => '(object) ',
			T_OBJECT_OPERATOR => '->',
			T_OPEN_TAG_WITH_ECHO => '<?=',
			T_OR_EQUAL => ' |= ',
			T_PAAMAYIM_NEKUDOTAYIM => '::',
			T_PLUS_EQUAL => ' += ',
			T_PRINT => 'print',
			T_PRIVATE => 'private ',
			T_PUBLIC =>	'public ',
			T_PROTECTED => 'protected ',
			T_REQUIRE => 'require ',
			T_REQUIRE_ONCE => 'require_once ',
			T_SL => ' << ',
			T_SL_EQUAL => ' <<= ',
			T_SR => ' >> ',
			T_SR_EQUAL => ' >>= ',
			//T_START_HEREDOC => '$$',
			T_STATIC => 'static ',
			T_STRING_CAST => '(string) ',
			//T_STRING_VARNAME => '$$',
			T_THROW => 'throw ',
			T_UNSET => 'unset',
			T_UNSET_CAST => '(unset) ',
			T_USE => ' use ',
			T_VAR => 'var ',
			//T_VARIABLE => '$$',
			T_XOR_EQUAL => ' ^= ', 
			'+' => ' + ', 
			'-' => ' - ', 
			'*' => ' * ', 
			'/' => ' / ',
			'%' => ' % ',
			'.' => ' . ', 
			'>' => ' > ', 
			'<' => ' < ', 
			'=' => ' = ', 
			'!' => '!', 
			'^' => ' ^ ',
			// class names, function names, etc.
			T_STRING => function ($content, $state, $word) {
				$content->append($word);
				if(in_array($word, array('class', 'function'))) {
					$content->space();
				}
			},
			T_COMMENT => function ($content, $state, $word) {
				foreach(explode("\n", $word) as $commentLine) {
					$content->newline()->append(trim($commentLine));
				}
			},
			T_DOC_COMMENT => function ($content, $state, $word) {
				foreach(explode("\n", $word) as $commentLine) {
					$content->newline()->append(trim($commentLine));
				}
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
			T_WHILE => function ($content, $state, $word) {
				$content->newline()->append($word);
			},
			T_FOR => function ($content, $state, $word) {
				$content->newline()->append($word);
			},
			T_CLASS => function ($content, $state, $word) {
				$content->newline()->append($word)->space();
			},
			T_FUNCTION => function ($content, $state, $word) {
				$content->append($word)->space();
			},
			T_FOREACH => function ($content, $state, $word) {
				$content->newline()->append($word);
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
				$content->append($word);
			},
			')' => function ($content, $state, $word) {
				$content->append($word);
			},
			';' => function ($content, $state, $word) {
				$content->rtrim()->append($word)->newline();
			},
			',' => function ($content, $state, $word) {
				$content->rtrim()->append($word)->space();
			},
			'?' => function ($content, $state, $word) {
				$content->space()->append($word)->space();
			},
			':' => function ($content, $state, $word) {
				$content->space()->append($word)->space();
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
				if(preg_match("/\n\n+?/", $word)) {
					$content->newline();		
				}
			},
			T_INLINE_HTML => function ($content, $state, $word) {
				// TODO
			}
			);
	}
}