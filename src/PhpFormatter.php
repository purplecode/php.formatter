<?php

require_once ('TokenTransformations.php');
require_once ('Content.php');
require_once ('State.php');

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