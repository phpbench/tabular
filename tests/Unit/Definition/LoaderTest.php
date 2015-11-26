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

use PhpBench\Dom\Document;
use PhpBench\Tabular\Definition;
use PhpBench\Tabular\Definition\Loader;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    private $loader;

    public function setUp()
    {
        $this->loader = new Loader();
    }

    /**
     * It should accept Definition classes.
     */
    public function testAcceptDefinitionClasses()
    {
        $result = $this->loader->load(new Definition(array(
            'rows' => array(),
        )));

        $this->assertDefinition(array(
            'rows' => array(),
        ), $result);
    }

    /**
     * It should accept file names of definition files.
     */
    public function testAcceptFileNames()
    {
        $result = $this->loader->load(__DIR__ . '/../fixtures/definition.json');

        $this->assertDefinition(array(
            'rows' => array(
                array('cells' => array(
                    array('name' => 'foo', 'literal' => 'bar'),
                )),
            ),
        ), $result);
    }

    /**
     * It should accept arrays as definitions.
     */
    public function testAcceptArray()
    {
        $result = $this->loader->load(array(
            'rows' => array(
                array('cells' => array(
                    array('name' => 'foo', 'literal' => 'bar'),
                )),
            ),
        ));

        $this->assertDefinition(array(
            'rows' => array(
                array('cells' => array(
                    array('name' => 'foo', 'literal' => 'bar'),
                )),
            ),
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
        $this->loader->load(__DIR__ . '/../fixtures/definition_not_exist.json');
    }

    /**
     * It should throw an exception if an unsupported type is passed as a definition.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid definition
     */
    public function testDefinitionInvalidType()
    {
        $this->loader->load(new \stdClass());
    }

    /**
     * It should throw an exception if a file contains invalid JSON.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not decode
     */
    public function testAcceptFileNameNotValidJson()
    {
        $this->loader->load(__DIR__ . '/../fixtures/definition_invalid.json');
    }

    /**
     * It should include other configurations.
     */
    public function testInclude()
    {
        $result = $this->loader->load(__DIR__ . '/../fixtures/include.json');
        $this->assertDefinition(array(
            'classes' => array(
                'stability' => array(
                    array('printf', array('format' => 'hi')),
                ),
                'foo' => array(
                    array('printf'),
                ),
            ),
            'rows' => array(
                array(
                    'cells' => array(
                        array('name' => 'foo', 'literal' => 'bar'),
                    ),
                ),
            ),
            'includes' => array(
                array('_include1.json', array('classes')),
                array('_include2.json', array()),
            ),
        ), $result);
    }

    /**
     * It should throw an exception if an invalid number of items are supplied in the include tuple.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid number
     */
    public function testInvalidNumberOfItems()
    {
        $this->loader->load(array('includes' => array(array('one', 'two', 'three'))));
    }

    /**
     * It should evaluate and set the column names for the loaded definition.
     */
    public function testColumnNames()
    {
        $definition = $this->loader->load(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array('name' => 'foo', 'literal' => 'bar'),
                    ),
                ),
                array(
                    'cells' => array(
                        array(
                            'name' => 'col_{{ cell.item }}',
                            'literal' => 'bar',
                            'with_items' => array('foo', 'bar', 'boo'),
                        ),
                        array('name' => 'hello', 'literal' => 'baz'),
                    ),
                ),
                array(
                    'cells' => array(
                        array('name' => 'hai', 'literal' => 'bye'),
                    ),
                ),
            ),
        ));

        $this->assertEquals(array(
            'foo', 'col_foo', 'col_bar', 'col_boo', 'hello', 'hai',
        ), $definition->getColumnNames());
    }

    /**
     * It should expand items from an expression when a source document is given.
     */
    public function testColumnNamesItems()
    {
        $dom = new Document();
        $dom->load(__DIR__ . '/files/articles.xml');
        $definition = $this->loader->load(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => '{{ cell.item }}',
                            'literal' => 'bar',
                            'with_items' => array(
                                'selector' => '//article',
                                'value' => 'string(./@name)',
                            ),
                        ),
                    ),
                ),
            ),
        ), $dom);

        $this->assertEquals(array(
            'one', 'two', 'three',
        ), $definition->getColumnNames());
    }

    /**
     * It should throw an exception if no source document is given when evaluating an expression for items.
     *
     * @expectedException RuntimeException 
     * @expectedExceptionMessage You must pass a source document
     */
    public function testItemExprNoSource()
    {
        $this->loader->load(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => '{{ cell.item }}',
                            'literal' => 'bar',
                            'with_items' => array(
                                'selector' => '//article',
                                'value' => 'string(./@name)',
                            ),
                        ),
                    ),
                ),
            ),
        ));
    }

    /**
     * It should record and sort the compiler pass numbers.
     */
    public function testCompilerPassDefinition()
    {
        $definition = $this->loader->load(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array('name' => 'foo', 'literal' => 'bar', 'pass' => 4),
                    ),
                ),
                array(
                    'cells' => array(
                        array('name' => 'hello', 'literal' => 'baz', 'pass' => 2),
                    ),
                ),
                array(
                    'cells' => array(
                        array('name' => 'hai', 'literal' => 'bye', 'pass' => 10),
                    ),
                ),
            ),
        ));

        $this->assertEquals(array(2, 4, 10), $definition->getPasses());
    }

    private function assertDefinition($expected, $definition)
    {
        $this->assertInstanceOf('PhpBench\Tabular\Definition', $definition);
        $this->assertEquals($expected, $definition->getArrayCopy());
    }
}
