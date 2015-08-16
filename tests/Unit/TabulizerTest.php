<?php

namespace PhpBench\Tabular\Tests\Unit;

use PhpBench\Tabular\Tabulizer;
use PhpBench\Tabular\Dom\Document;

class TabulizerTest extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $this->tabulizer = new Tabulizer();
        $this->document = new \DOMDocument();
        $this->document->load(__DIR__ . '/fixtures/report.xml');
    }

    /**
     * It should convert transform an XML document into a table using a given configuration
     */
    public function testTabularize()
    {
        $result = $this->tabulizer->tabularize($this->document, array(
            'rows' => array(
                'one' => array(
                    'cells' => array(
                        'time' => array(
                            'expr' => 'sum(//iteration/@time)',
                        ),
                        'memory' => array(
                            'expr' => 'sum(//iteration/@memory)',
                        ),
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
        $result = $this->tabulizer->tabularize($this->document, array(
            'rows' => array(
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
        $result = $this->tabulizer->tabularize($this->document, array(
            'rows' => array(
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
            ),
        ));

        $this->assertEquals('one', $result->xpath()->evaluate('string(/table/row[1]/@group)'));
        $this->assertEquals('two', $result->xpath()->evaluate('string(/table/row[2]/@group)'));
    }

    /**
     * It should allow literal cell values
     */
    public function testLiteralCellValue()
    {
        $result = $this->tabulizer->tabularize($this->document, array(
            'rows' => array(
                'one' => array(
                    'cells' => array(
                        'one' => array(
                            'literal' => 'Helli',
                        ),
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
        $result = $this->tabulizer->tabularize($this->document, array(
            'rows' => array(
                array(
                    'group' => 'one',
                    'cells' => array(
                        'param' => array(
                            'literal' => '{{ row.item }}',
                        ),
                    ),
                    'with_items' => array('one', 'two'),
                ),
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
        $result = $this->tabulizer->tabularize($this->document, array(
            'rows' => array(
                array(
                    'group' => 'one',
                    'cells' => array(
                        'cell_{{ cell.item }}' => array(
                            'literal' => '{{ cell.item }}',
                            'with_items' => array('one', 'two'),
                        ),
                    ),
                ),
            ),
        ));

        $this->assertTable(array(
            array('cell_one' => 'one', 'cell_two' => 'two'),
        ), $result);
    }

    /**
     * It should sort rows by a single column ascending
     */
    public function testSortSingleColumn()
    {
        $result = $this->tabulizer->tabularize($this->document, array(
            'rows' => array(
                array(
                    'cells' => array(
                        'one' => array(
                            'literal' => 'ccc',
                        ),
                    ),
                ),
                array(
                    'cells' => array(
                        'one' => array(
                            'literal' => 'aaa',
                        ),
                    ),
                ),
            ),
            'sort' => array(
                'literal' => 'asc',
            ),
        ));

        $this->assertTable(array(
            array('one' => 'aaa'),
            array('one' => 'ccc'),
        ), $result);
    }

    /**
     * It should sort rows by a multiple columns
     */

    /**
     * It should sort based on groups using the group#colname syntax
     */

    /**
     * It should format the values
     *
     * MORE HERE
     */

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
