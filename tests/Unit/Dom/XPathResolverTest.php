<?php

namespace PhpBench\Tabular\Tests\Unit\Dom;

use PhpBench\Tabular\Dom\XPathResolver;

class XPathResolverTest extends \PHPUnit_Framework_TestCase
{
    private $xpathResolver;

    public function setUp()
    {
        $this->xpathResolver = new XPathResolver();
    }

    /**
     * It should expand function names to their php:function forms
     *
     * @dataProvider provideFunctionNameReplace
     */
    public function testFunctionNameReplace($query, $expected)
    {
        $this->xpathResolver->registerFunction('average', 'Bar\\Foo\\Bar::average');
        $this->xpathResolver->registerFunction('min', 'Bar\\Foo\\Bar::min');
        $query = $this->xpathResolver->replaceFunctions($query);
        $this->assertEquals($expected, $query);
    }

    public function provideFunctionNameReplace()
    {
        return array(
            array(
                'min()',
                'php:function(\'Bar\\Foo\\Bar::min\')',
            ),
            array(
                'string(min())',
                'string(php:function(\'Bar\\Foo\\Bar::min\'))',
            ),
            array(
                'sum(floor(//time)) + 5',
                'sum(floor(//time)) + 5',
            ),
            array(
                'average(min(//foobar/@time))',
                'php:function(\'Bar\\Foo\\Bar::average\', php:function(\'Bar\\Foo\\Bar::min\', //foobar/@time))'
            ),
            array(
                'average(//foobar/@time)',
                'php:function(\'Bar\\Foo\\Bar::average\', //foobar/@time)'
            ),
        );
    }

    /**
     * It should throw an exception if an unknown function is encountered
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown Tabular XPath function "barbar" in query "barbar(//bar)". Known Tabular functions: "average"
     */
    public function testUnknownFunction()
    {
        $this->xpathResolver->registerFunction('average', 'Bar\\Foo\\Bar::average');
        $this->xpathResolver->replaceFunctions('barbar(//bar)');
    }
}
