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

use JsonSchema\Validator;
use PhpBench\Tabular\Dom\Document;
use PhpBench\Tabular\Dom\XPathResolver;
use PhpBench\Tabular\Formatter;
use PhpBench\Tabular\Formatter\Format\PrintfFormat;
use PhpBench\Tabular\Formatter\Registry\ArrayRegistry;
use PhpBench\Tabular\TableBuilder;
use PhpBench\Tabular\Tabular;
use PhpBench\Tabular\Definition;

class TabularTest extends \PHPUnit_Framework_TestCase
{
    private $tabular;
    private $document;

    public function __construct()
    {
        $validator = new Validator();
        $formatRegistry = new ArrayRegistry();
        $formatRegistry->register('printf', new PrintfFormat());
        $xpathResolver = new XPathResolver();
        $xpathResolver->registerFunction('hello', 'PhpBench\Tabular\Tests\Unit\TabularTest::xpathFunction');
        $tableBuilder = new TableBuilder($xpathResolver);
        $formatter = new Formatter($formatRegistry);
        $this->tabular = new Tabular($tableBuilder, $validator, $formatter);
        $this->document = new \DOMDocument();
        $this->document->load(__DIR__ . '/fixtures/report.xml');
    }

    public static function xpathFunction()
    {
        return 'hello';
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
     * It should iterate over parameters on ROWS.
     */
    public function testIterateParameterRows()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'group' => 'one',
                    'cells' => array(
                        array(
                            'name' => 'param',
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
     * It should dynamically create columns using CELLS.
     */
    public function testIterateParameterCells()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'group' => 'one',
                    'cells' => array(
                        array(
                            'name' => 'cell_{{ cell.item }}',
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
                            'expr' => 'string(hello())',
                        ),
                    ),
                ),
            ),
        ));

        $this->assertTable(array(
            array('one' => 'hello'),
        ), $result);
    }

    /**
     * It should allow compiler passes.
     */
    public function testCompilerPass()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => 'one',
                            'literal' => 5,
                        ),
                        array(
                            'name' => 'three',
                            'pass' => 10,
                            'expr' => 'sum(//cell[@name="two"]) + 1',
                        ),
                        array(
                            'name' => 'two',
                            'pass' => 5,
                            'expr' => 'sum(//cell[@name="one"])',
                        ),
                    ),
                ),
            ),
        ));

        $this->assertTable(array(
            array('one' => '5', 'two' => '5', 'three' => '6'),
        ), $result);
    }

    /**
     * It should allow parameterized definitions
     */
    public function testParameterized()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => 'one',
                            'literal' => '{{ param.foo }}',
                        ),
                    ),
                ),
            ),
            'params' => array('foo' => 'bar'),
        ));

        $this->assertTable(array(
            array('one' => 'bar'),
        ), $result);
    }

    /**
     * It should override default parameters
     */
    public function testParametersOverride()
    {
        $result = $this->tabular->tabulate($this->document, array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => 'one',
                            'literal' => '{{ param.foo }}',
                        ),
                    ),
                ),
            ),
            'params' => array('foo' => 'bar'),
        ), array('foo' => 'baz'));

        $this->assertTable(array(
            array('one' => 'baz'),
        ), $result);
    }

    /**
     * It should accept Definition classes
     */
    public function testAcceptDefinitionClasses()
    {
        $result = $this->tabular->tabulate($this->document, new Definition(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => 'one',
                            'literal' => 'two',
                        ),
                    ),
                ),
            ),
        )));

        $this->assertTable(array(
            array('one' => 'two'),
        ), $result);
    }

    /**
     * It should accept file names of definition files
     */
    public function testAcceptFileNames()
    {
        $result = $this->tabular->tabulate($this->document, __DIR__ . '/fixtures/definition.json');

        $this->assertTable(array(
            array('foo' => 'bar'),
        ), $result);
    }

    /**
     * It should throw an exception if the definition file does not exist.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage does not exist
     */
    public function testAcceptFileNameNotExist()
    {
        $this->tabular->tabulate($this->document, __DIR__ . '/fixtures/definition_not_exist.json');
    }

    /**
     * It should throw an exception if an unsupported type is passed as a definition
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid definition
     */
    public function testDefinitionInvalidType()
    {
        $this->tabular->tabulate($this->document, new \stdClass);
    }

    /**
     * It should throw an exception if a file contains invalid JSON
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not decode
     */
    public function testAcceptFileNameNotValidJson()
    {
        $this->tabular->tabulate($this->document, __DIR__ . '/fixtures/definition_invalid.json');
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