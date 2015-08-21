<?php

namespace PhpBench\Tabular\Tests\Unit\Dom;

use PhpBench\Tabular\Dom\XPath;

class XPathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should throw an exception if an XPath expression is invalid
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Errors encountered when evaluating XPath expression
     */
    public function testInvalidXpathExpression()
    {
        $dom = new \DOMDocument('1.0');
        $xpath = new XPath($dom);
        $xpath->evaluate('asdf))()');
    }

    /**
     * It should throw an exception if a query is invalid
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Errors encountered when evaluating XPath query
     */
    public function testInvalidXPathQuery()
    {
        $dom = new \DOMDocument('1.0');
        $xpath = new XPath($dom);
        $xpath->query('asdf))()');
    }
}
