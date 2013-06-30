<?php

class Content {

	private $content = '';

	private $lastLine = '';

	private $state;

	public function __construct($state) {
		$this->state = $state;
	}

	public function append($text) {
		$this->lastLine .= $text;
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
		$this->append(' ');	
		return $this;
	}

	public function newline() {
		if($this->state->openBraces == 0) {
			$indent = str_repeat(' ', $this->state->indent * Settings::TABS_LENGTH);
			$this->content .= $this->lastLine."\n".$indent;
			$this->lastLine = '';
		} else {
			$this->space();
		}
		return $this;
	}	

	public function isEmpty() {
		$fullContent = $this->getContent(); 
		return empty($fullContent);
	}

	public function rtrim() {
		$this->lastLine = rtrim($this->lastLine);
		if(empty($this->lastLine)) {
			$this->content = rtrim($this->content);
		}
		return $this;
	}

	public function getContent() {
		return $this->content.$this->lastLine;
	}
}

?>