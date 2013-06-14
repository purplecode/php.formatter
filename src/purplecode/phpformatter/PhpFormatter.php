<?php

require_once ('TokenTransformations.php');


class State {

	public $indent = 0;

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

	public function space() {
		$this->content .= ' ';	
		return $this;
	}

	public function newline() {
		$this->content .= "\n".str_repeat(' ', $this->state->indent * Settings::TABS_LENGTH);	
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

	public function endsWith($suffix) {
		return substr_compare($this->content, $suffix, -strlen($suffix), strlen($suffix)) === 0;
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

	public function format($fileContent) {
		
		$state = new State();
		$content = new Content($state);

		$tokens = token_get_all($fileContent);
		foreach($tokens as $index => $token) {
			
			list($tokenKey, $word) = $this->normalizeToken($token);

			if (!$this->transformations->has($tokenKey)) {
				$content->append($word);
				continue;
			}

			$transformation = $this->transformations->get($tokenKey);
			if(is_callable($transformation)) {
				$transformation($content, $state, $word);
			} else {
				$content->append($transformation);
			}
		}

		$result = $content->getContent();

		$result = preg_replace('/\n\n\n+?/', "\n\n", $result);

		return $result;
	}
}