<?php

namespace PhpBench\Tabular\Tests\Unit\Formatter\Formatter;

use PhpBench\Tabular\Formatter\Format\NumberFormat;

class NumberFormatTest extends \PHPUnit_Framework_TestCase
{
    private $format;

    public function setUp()
    {
        $this->format = new NumberFormat();
    }

    /**
     * It should format a number
     */
    public function testNumberFormat()
    {
        $result = $this->format->format(1000000, $this->format->getDefaultOptions());
        $this->assertEquals('1,000,000', $result);
    }

}
