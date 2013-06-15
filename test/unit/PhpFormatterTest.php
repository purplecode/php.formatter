<?php

require_once (dirname(__FILE__) . '/../../src/PhpFormatter.php');

class PhpFormatterTest extends PHPUnit_Framework_TestCase {

    private $cut;

    public function __construct() {
        $this->cut = new PhpFormatter();
    }


    public function testFormatingIfBlock() {
        // give
        $input = "<?php if (\$x   == 2) { return ;} ?>";

        // when
        $current = $this->cut->format($input);

        //then
        $expected = "<?php \nif(\$x == 2) {\n    return;\n}\n\n?>\n";

        //$this->print_diff($expected, $current);

        $this->assertEquals($expected, $current);
    }

    private function print_diff($expected, $current) {
        $this->assertEquals(strlen($expected), strlen($current));
        
        $length = min(strlen($expected), strlen($current));
        for($i=0; $i<$length; $i++) {
            echo $current[$i] . " == ". $expected[$i]." -> ".($current[$i] == $expected[$i] ? "T" : 'F')."\n"; 
        }
    }

}