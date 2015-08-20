<?php

namespace PhpBench\Tabular\Tests\Unit;

use PhpBench\Tabular\Tabulizer;
use PhpBench\Tabular\Dom\Document;
use PhpBench\Tabular\TableBuilder;
use Prophecy\Argument;

class TableBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $rowBuilder;
    private $document;
    private $xpathResolver;

    public function __construct()
    {
        $this->xpathResolver = $this->prophesize('PhpBench\Tabular\Dom\XPathResolver');
        $this->rowBuilder = new TableBuilder($this->xpathResolver->reveal());
        $this->document = new \DOMDocument();
        $this->document->load(__DIR__ . '/fixtures/report.xml');

        $this->xpathResolver->replaceFunctions(Argument::any())->will(function ($string) { return $string[0]; });
        $this->xpathResolver->registerXPathFunctions(Argument::any())->shouldBeCalled();
    }

    /**
     * It should convert transform an XML document into a table using a given configuration
     */
    public function testTabularize()
    {
        $result = $this->rowBuilder->buildTable($this->document, array(
            array(
                'cells' => array(
                    'time' => array(
                        'expr' => 'sum(//iteration/@time)',
                    ),
                    'memory' => array(
                        'expr' => 'sum(//iteration/@memory)',
                    ),
                ),
            ),
        ));

        $this->assertTable(array(
            array(
                'time' => 80,
                'memory' => 800,
            ),
        ), $result);
    }

    /**
     * It should iterate over a query to generate more rows
     */
    public function testWithQuery()
    {
        $result = $this->rowBuilder->buildTable($this->document, array(
            'one' => array(
                'cells' => array(
                    'time' => array(
                        'expr' => 'sum(.//iteration/@time)',
                    ),
                    'memory' => array(
                        'expr' => 'sum(.//iteration/@memory)',
                    ),
                ),
                'with_query' => '//subject',
            ),
        ));

        $this->assertTable(array(
            array(
                'time' => 40,
                'memory' => 400,
            ),
            array(
                'time' => 40,
                'memory' => 400,
            ),
        ), $result);
    }

    /**
     * It should apply groups to the table DOM
     */
    public function testGroups()
    {
        $result = $this->rowBuilder->buildTable($this->document, array(
            'one' => array(
                'group' => 'one',
                'cells' => array(
                    'time' => array(
                        'expr' => 'sum(.//iteration/@time)',
                    ),
                    'memory' => array(
                        'expr' => 'sum(.//iteration/@memory)',
                    ),
                ),
            ),
            'two' => array(
                'group' => 'two',
                'cells' => array(
                    'time' => array(
                        'expr' => 'sum(.//iteration/@time)',
                    ),
                    'memory' => array(
                        'expr' => 'sum(.//iteration/@memory)',
                    ),
                ),
            ),
        ));

        $this->assertEquals('one', $result->xpath()->evaluate('string(/table/group[1]/@name)'));
        $this->assertEquals('two', $result->xpath()->evaluate('string(/table/group[2]/@name)'));
    }

    /**
     * It should allow literal cell values
     */
    public function testLiteralCellValue()
    {
        $result = $this->rowBuilder->buildTable($this->document, array(
            'one' => array(
                'cells' => array(
                    'one' => array(
                        'literal' => 'Helli',
                    ),
                ),
            ),
        ));

        $this->assertTable(array(
            array('one' => 'Helli'),
        ), $result);
    }

    /**
     * It should iterate over parameters on ROWS
     */
    public function testIterateParameterRows()
    {
        $result = $this->rowBuilder->buildTable($this->document, array(
            array(
                'group' => 'one',
                'cells' => array(
                    'param' => array(
                        'literal' => '{{ row.item }}',
                    ),
                ),
                'with_items' => array('one', 'two'),
            ),
        ));

        $this->assertTable(array(
            array('param' => 'one'),
            array('param' => 'two'),
        ), $result);
    }

    /**
     * It should iterate over parameters on CELLS
     * It should dynamically create columns using CELLS
     */
    public function testIterateParameterCells()
    {
        $result = $this->rowBuilder->buildTable($this->document, array(
            array(
                'group' => 'one',
                'cells' => array(
                    'cell_{{ cell.item }}' => array(
                        'literal' => '{{ cell.item }}',
                        'with_items' => array('one', 'two'),
                    ),
                ),
            ),
        ));

        $this->assertTable(array(
            array('cell_one' => 'one', 'cell_two' => 'two'),
        ), $result);
    }

    private function assertTable($expected, Document $result)
    {
        $results = array();
        foreach ($result->xpath()->query('//row') as $rowEl) {
            $row = array();
            foreach ($result->xpath()->query('./cell', $rowEl) as $cellEl) {
                $row[$cellEl->getAttribute('name')] = $cellEl->nodeValue;
            }
            $results[] = $row;
        }

        $this->assertEquals($expected, $results);
    }
}
