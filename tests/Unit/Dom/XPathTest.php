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

use PhpBench\Tabular\Dom\XPath;

class XPathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should throw an exception if an XPath expression is invalid.
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
     * It should throw an exception if a query is invalid.
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

    /**
     * It should throw an exception if an expression returns an object.
     *
     * @expectedException InvalidArgumentException
     */
    public function testExpressionReturnsAnObject()
    {
        $dom = new \DOMDocument('1.0');
        $xpath = new XPath($dom);
        $xpath->evaluate('//book');
    }
}
