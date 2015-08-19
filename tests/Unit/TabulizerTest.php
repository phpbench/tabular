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
     * It should throw an exception if the definition is invalid
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage [rows[0]] The property - cinvalidls - is not defined and the definition does not allow additional properties
     */
    public function testInvalidDefinition()
    {
        $this->tabulizer->tabularize($this->document, array(
            'rows' => array(
                array(
                    'cinvalidls' => array(
                ),
            ),
        )));
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

        $this->assertEquals('one', $result->xpath()->evaluate('string(/table/group[1]/@name)'));
        $this->assertEquals('two', $result->xpath()->evaluate('string(/table/group[2]/@name)'));
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
                'one' => 'asc',
            ),
        ));

        $this->assertTable(array(
            array('one' => 'aaa'),
            array('one' => 'ccc'),
        ), $result);
    }

    /**
     * It should format the values with printf
     */
    public function testFormat()
    {
        $result = $this->tabulizer->tabularize($this->document, array(
            'rows' => array(
                array(
                    'cells' => array(
                        'one' => array(
                            'literal' => '100',
                        ),
                    ),
                ),
            ),
            'format' => array(
                'one' => 'euro',
            ),
            'formatters' => array(
                'euro' => array('type' => 'printf', 'medium' => 'console', 'format' => '€%s'),
            )
        ));

        $this->assertEquals(array(
            array('one' => '€100')
        ), $result);
    }

    /**
     * It should allow parameterized tables
     */

    /**
     * It should allow the registration of XPath functions
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
