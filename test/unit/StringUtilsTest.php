<?php

require_once (dirname(__FILE__) . '/../../src/StringUtils.php');

class StringUtilsTest extends PHPUnit_Framework_TestCase {

    public function testStartsWith() {
    	// given 
    	$text = "ala ma kota";

    	// then
        $this->assertTrue(StringUtils::startsWith($text, "ala ma"));
        $this->assertFalse(StringUtils::startsWith($text, "la ma"));
    }

	public function testEndsWith() {
		// given 
    	$text = "ala ma kota";

    	// then
        $this->assertTrue(StringUtils::endsWith($text, "ota"));
        $this->assertFalse(StringUtils::endsWith($text, "kot"));
    }

    public function testContains() {
    	// given 
    	$text = "ala ma kota";

    	// then
        $this->assertTrue(StringUtils::contains($text, " ma"));
        $this->assertFalse(StringUtils::contains($text, "m a"));
    }

    public function testSimpleReplace() {
    	// given 
    	$text = "ala ma kota";

    	// when
		$result = StringUtils::replace('/ala/', 'bob', $text);

    	// then
        $this->assertEquals("bob ma kota", $result);
    }

    public function testLineCommentReplace() {
    	// given 
    	$text = "//some comment";

    	// when
		$result = StringUtils::replace('/\/\/(\w)/', '// \1', $text);

    	// then
        $this->assertEquals("// some comment", $result);
    }
}
