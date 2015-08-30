<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular\Tests\Unit;

use PhpBench\Tabular\Definition;
use PhpBench\Tabular\Definition\Loader;
use PhpBench\Tabular\Dom\TableDom;
use PhpBench\Tabular\Dom\XPathResolver;
use PhpBench\Tabular\Formatter;
use PhpBench\Tabular\Formatter\Format\PrintfFormat;
use PhpBench\Tabular\Formatter\Registry\ArrayRegistry;
use PhpBench\Tabular\TableBuilder;
use PhpBench\Tabular\Tabular;

class TabularTest extends \PHPUnit_Framework_TestCase
{
    private $tabular;
    private $document;

    public function __construct()
    {
        $this->tabular = Tabular::getInstance();
        $this->document = new \DOMDocument();
        $this->document->load(__DIR__ . '/fixtures/report.xml');
    }

    /**
     * It should convert transform an XML document into a table using a given configuration.
     */
    public function testTabularize()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => 'time',
                            'expr' => 'sum(//iteration/@time)',
                        ),
                        array(
                            'name' => 'memory',
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
     * It should throw an exception if the definition is invalid.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage cinvalidls
     */
    public function testInvalidDefinition()
    {
        $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'cinvalidls' => array(
                ),
            ),
        ), ));
    }

    /**
     * It should iterate over a query to generate more rows.
     */
    public function testWithQuery()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => 'time',
                            'expr' => 'sum(.//iteration/@time)',
                        ),
                        array(
                            'name' => 'memory',
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
     * It should apply groups to the table DOM.
     */
    public function testGroups()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'group' => 'one',
                    'cells' => array(
                        array(
                            'name' => 'time',
                            'expr' => 'sum(.//iteration/@time)',
                        ),
                        array(
                            'name' => 'memory',
                            'expr' => 'sum(.//iteration/@memory)',
                        ),
                    ),
                ),
                array(
                    'group' => 'two',
                    'cells' => array(
                        array(
                            'name' => 'time',
                            'expr' => 'sum(.//iteration/@time)',
                        ),
                        array(
                            'name' => 'memory',
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
     * It should allow literal cell values.
     */
    public function testLiteralCellValue()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => 'one',
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
     * It should sort rows by a single column ascending.
     */
    public function testSortSingleColumn()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => 'one',
                            'literal' => 'ccc',
                        ),
                    ),
                ),
                array(
                    'cells' => array(
                        array(
                            'name' => 'one',
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
     * It should format the values with printf.
     */
    public function testFormat()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => 'one',
                            'class' => 'percent',
                            'literal' => '100',
                        ),
                    ),
                ),
            ),
            'classes' => array(
                'percent' => array(
                    array('printf', array('format' => '%s percent')),
                    array('printf', array('format' => 'this is %s')),
                ),
            ),
        ));

        $this->assertTable(array(
            array('one' => 'this is 100 percent'),
        ), $result);
    }

    /**
     * Its should allow the use of custom xpath functions.
     */
    public function testCustomXPathFunction()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => 'one',
                            'expr' => 'string(sum(//@time))',
                        ),
                    ),
                ),
            ),
        ));

        $this->assertTable(array(
            array('one' => '80'),
        ), $result);
    }

    private function assertTable($expected, TableDom $result)
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
