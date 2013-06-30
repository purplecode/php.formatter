<?php

class Content {

	private $content = '';

	private $lineWidth = 0;

	private $lastBr = 0;

	private $state;

	public function __construct($state) {
		$this->state = $state;
	}

	public function openBraces() {
		$this->state->openBraces++;
		$this->append('(');
		return $this;
	}

	public function closeBraces() {
		$this->state->openBraces--;
		$this->append(')');
		return $this;
	}

	public function space() {
		$this->append(' ');	
		return $this;
	}

	public function append($text) {
		$textLength = strlen($text);
		if($this->lineWidth + $textLength >= Settings::LINE_LENGTH) {
			$this->newline(true);
		}
		$this->lineWidth += $textLength;
		$this->content .= $text;
		return $this;
	}

	public function br() {
		$this->lastBr = strlen($this->content);
		return $this;
	}

	public function newline($lineWrapping = false) {
		// TODO breaking for loops
		if($this->state->openBraces > 0 && !$lineWrapping) {
			$this->space();
			return $this;
		}

		$this->lineWidth = 0;
		$this->state->lineWrapping  = $lineWrapping;

		$this->content .= "\n";
		$this->indent($this->state->indent + ($this->state->lineWrapping ? 1 : 0));
		return $this;
	}

	private function indent($indentSize) {
		$this->append(str_repeat(' ', $indentSize));
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