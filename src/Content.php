<?php

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

?>