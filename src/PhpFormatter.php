<?php

require_once ('TokenTransformations.php');


class State {

	public $indent = 0;

	public $openBraces = 0;

}

class Content {

	private $content = '';

	private $state;

	public function __construct($state) {
		$this->state = $state;
	}

	public function append($text) {
		$this->content .= $text;
		return $this;
	}

	public function openBraces() {
		$this->state->openBraces++;
		$this->append('(');
	}

	public function closeBraces() {
		$this->state->openBraces--;
		$this->append(')');
	}

	public function space() {
		$this->content .= ' ';	
		return $this;
	}

	public function newline() {
		if($this->state->openBraces == 0) {
			$this->content .= "\n".str_repeat(' ', $this->state->indent * Settings::TABS_LENGTH);
		} else {
			$this->space();
		}
		return $this;
	}	

	public function isEmpty() {		
		return empty($this->content);
	}

	public function rtrim() {
		$this->content = rtrim($this->content);
		return $this;
	}

	public function getContent() {
		return $this->content;
	}
}

class PhpFormatter {

	private $transformations;

	public function __construct() {
		$this->transformations = new TokenTransformations();
	}

	private function normalizeToken($token) {
		$word = count($token) == 3 ? $token[1] : $token[0];
		$word = str_replace("\r", '', $word);
		$tokenKey = $token[0];
		return array($tokenKey, $word);
	}

	private function encodeEmptyLines($text) {
		return preg_replace('/\n\s*?\n/', " $$ ", $text);		
	}

	private function restoreEmptyLines($text) {
		return preg_replace('/\$\$/', "\n", $text);
	}

	public function format($fileContent) {
		
		$state = new State();
		$content = new Content($state);

		$fileContent = preg_replace('/\n\s*\n/', "\n\n", $fileContent);

		$tokens = token_get_all($fileContent);
		foreach($tokens as $index => $token) {
			
			list($tokenKey, $word) = $this->normalizeToken($token);

			if (!$this->transformations->has($tokenKey)) {
				$content->append($word);
				continue;
			} else {
				$transformation = $this->transformations->get($tokenKey);
				if(is_callable($transformation)) {
					$transformation($content, $state, $word);
				} else {
					$content->append($transformation);
				}
			}
		}

		$result = $content->getContent();

		$result = preg_replace('/\n\n+/', "\n\n", $result);

		return $result;
	}
}