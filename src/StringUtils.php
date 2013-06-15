<?php

class StringUtils {

	public static function endsWith($text, $suffix) {
		return substr_compare($text, $suffix, -strlen($suffix), strlen($suffix)) === 0;
	}

	public static function startsWith($text, $prefix) {
		return substr_compare($text, $prefix, 0, strlen($prefix)) === 0;
	}

	public static function contains($text, $infix) {
		return strpos($text, $infix) !== FALSE;
	}

	public static function replace($regexp, $replacement, $text) {
		return preg_replace($regexp, $replacement, $text);
	}

}
