<?php

namespace PhpBench\Tabular\Tests\Unit\Formatter\Format;

use PhpBench\Tabular\Formatter\Format\BalanceFormat;

class BalanceFormatTest extends \PHPUnit_Framework_TestCase
{
    private $format;

    public function setUp()
    {
        $this->format = new BalanceFormat();
    }

    /**
     * It should format positive numbers
     * It should format negative numbers
     * It should format neutral numbers
     */
    public function testFormat()
    {
        $result = $this->format->format(0, $this->format->getDefaultOptions());
        $this->assertEquals('0', $result);

        $result = $this->format->format(-1, $this->format->getDefaultOptions());
        $this->assertEquals('-1', $result);

        $result = $this->format->format(1, $this->format->getDefaultOptions());
        $this->assertEquals('+1', $result);
    }
}
