# php.formatter

Yet another php formatter. Maybe this time you will be satisfied.

This project was started as a proof of concept, that it is possible to write a programming language formatter with clear set of rules and with small amount of if's. Formatter that can be easily extendible and customizable.

Currently only pure php code is supported (no html-php mess). I used it to format project based on Symfony framework.

## Usage

```
php format.php <input.php> [<output.php>]
```

## Limitations

I prefer adding curly braces after `if', `for` and 'case', that is why currently only such cases are nicely formatted.

Enjoy!
