<?php

namespace PhpBench\Tabular\Tests\Unit\Formatter\Formatter;

use PhpBench\Tabular\Formatter\Format\PrintfFormat;

class PrintfFormatTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->format = new PrintfFormat();
    }

    /**
     * It should format using sprintf
     */
    public function testNumberFormat()
    {
        $result = $this->format->format('hai', array('format' => '%s bye'));
        $this->assertEquals('hai bye', $result);
    }

}
