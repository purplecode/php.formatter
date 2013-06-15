<?php

require_once (dirname(__FILE__) . '/../../src/TokenTransformations.php');

class TokenTransformationsTest extends PHPUnit_Framework_TestCase {

    private $cut;

    public function __construct() {
        $this->cut = new TokenTransformations();
    }

    public function testBeautifyComment() {
        // given 
        $text = "//some comment";

        // when
        $result = $this->cut->beautifyComment($text);

        // then
        $this->assertEquals("// some comment", $result);
    }

}
