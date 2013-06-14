<?php

require_once (dirname(__FILE__) . '/PhpFormatter.php');

// run it as a command line tool
$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];

/**
Some comment
	*/
if ($argc < 2)
{
	printf("Usage: %s input file [output file]\n" . "Input file can be '-' for stdin, " .
			"and output can be '_' for same as input.\n\n",
		$argv[0]);
	exit(0);
}

$inputFile = $argv[1] == '-' ? 'php://stdin' : $argv[1]; 
$fileContent = file_get_contents($inputFile);





$formatter = new PHPFormatter();
$output = $formatter->format($fileContent);

echo $output;

