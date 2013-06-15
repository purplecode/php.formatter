<?php

require_once (dirname(__FILE__) . '/../../src/PhpFormatter.php');

class FunctionalTest extends PHPUnit_Framework_TestCase {

    private $cut;

    public function __construct() {
        $this->cut = new PhpFormatter();
    }


    public function testCase1() {
        // give
        $input = $this->getFile('1.in');

        // when
        $current = $this->cut->format($input);

        //then
        $expected = $this->getFile('1.out');

        $this->assertEquals($expected, $current);
    }

    private function getFile($name) {
    	return file_get_contents(dirname(__FILE__).'/'. $name);
    }

    private function print_diff($expected, $current) {
        $this->assertEquals(strlen($expected), strlen($current));
        
        $length = min(strlen($expected), strlen($current));
        for($i=0; $i<$length; $i++) {
            echo $current[$i] . " == ". $expected[$i]." -> ".($current[$i] == $expected[$i] ? "T" : 'F')."\n"; 
        }
    }

}