<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular\Tests\Unit\Dom;

use PhpBench\Tabular\Dom\functions;
use PhpBench\Tabular\Dom\XPathResolver;

class functionsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // force autoload of functions
        new XPathResolver();
    }

    /**
     * It should return the sum.
     */
    public function testSum()
    {
        $sum = functions\sum(array(30, 3));
        $this->assertEquals(33, $sum);
    }

    /**
     * Sum should accept an XML instance.
     */
    public function testSumDom()
    {
        $dom = new \DOMDocument(1.0);
        $dom->loadXml(<<<EOT
<?xml version="1.0"?>
<elements>
    <element value="10" />
    <element value="20" />
</elements>
EOT
    );

        $xpath = new \DOMXpath($dom);
        $elements = $xpath->query('//element/@value');

        $sum = functions\sum($elements);
        $this->assertEquals(30, $sum);
    }

    /**
     * It should return the min.
     */
    public function testMin()
    {
        $min = functions\min(array(4, 6, 1, 5));
        $this->assertEquals(1, $min);
    }

    /**
     * It should return the max.
     */
    public function testMax()
    {
        $max = functions\max(array(3, 1, 13, 5));
        $this->assertEquals(13, $max);
    }

    /**
     * It should return the average.
     */
    public function testMean()
    {
        $expected = 33 / 7;
        $this->assertEquals($expected, functions\mean(array(2, 2, 2, 2, 2, 20, 3)));
    }

    /**
     * Mean should handle no values.
     */
    public function testMeanNoValue()
    {
        $this->assertEquals(0, functions\mean(array()));
    }

    /**
     * Mean should return 0 if the sum of all values is zero.
     */
    public function testMeanAllZeros()
    {
        $this->assertEquals(0, functions\mean(array(0, 0, 0)));
    }

    /**
     * It should return the median of an even set of numbers.
     * The median should be the average between the middle two numbers.
     */
    public function testMedianEven()
    {
        $this->assertEquals(6, functions\median(array(9, 5, 7, 3)));
        $this->assertEquals(8, functions\median(array(9, 5, 7, 3, 10, 20)));
    }

    /**
     * It should return the median of an odd set of numbers.
     */
    public function testMedianOdd()
    {
        $this->assertEquals(3, functions\median(array(10, 3, 3), true));
        $this->assertEquals(3, functions\median(array(10, 8, 3, 1, 2), true));
    }

    /**
     * Median should handle no values.
     */
    public function testMedianNoValues()
    {
        $this->assertEquals(0, functions\median(array()));
    }

    /**
     * It should throw an exception if the value is not a valid object.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Values passed as an array must be scalar
     */
    public function testSumNonValidObject()
    {
        functions\sum(
            array(
                new \stdClass(),
            )
        );
    }

    /**
     * It should provide a deviation as a percentage.
     */
    public function testDeviation()
    {
        $this->assertEquals(0, functions\deviation(10, 10));
        $this->assertEquals(100, functions\deviation(10, 20));
        $this->assertEquals(-10, functions\deviation(10, 9));
        $this->assertEquals(10, functions\deviation(10, 11));
        $this->assertEquals(11, functions\deviation(0, 11));
        $this->assertEquals(-100, functions\deviation(10, 0));
        $this->assertEquals(0, functions\deviation(0, 0));
    }

    /**
     * It should return the standard deviation
     */
    public function testStdev()
    {
        $this->assertEquals(1.4142, round(functions\stdev(array(1, 2, 3, 4, 5)), 4));
        $this->assertEquals(17.2116, round(functions\stdev(array(13, 23, 12, 44, 55)), 4));
        $this->assertEquals(0, round(functions\stdev(array(1)), 4));
        $this->assertEquals(0, round(functions\stdev(array(1, 1, 1)), 4));
    }

    /**
     * It should return the absolute value
     */
    public function testAbs()
    {
        $this->assertEquals(6, functions\abs(6));
        $this->assertEquals(6, functions\abs(-6));
    }
}
