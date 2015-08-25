<?php

namespace PhpBench\Tabular\Tests\Unit;

use PhpBench\Tabular\DefinitionLoader;
use PhpBench\Tabular\Definition;

class DefinitionLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $loader;

    public function setUp()
    {
        $this->loader = new DefinitionLoader();
    }

    /**
     * It should accept Definition classes
     */
    public function testAcceptDefinitionClasses()
    {
        $result = $this->loader->load(new Definition(array(
            'rows' => array()
        )));

        $this->assertDefinition(array(
            'rows' => array()
        ), $result);
    }

    /**
     * It should accept file names of definition files
     */
    public function testAcceptFileNames()
    {
        $result = $this->loader->load(__DIR__ . '/fixtures/definition.json');

        $this->assertDefinition(array(
            'rows' => array(
                array('cells' => array(
                    array('name' => 'foo', 'literal' => 'bar')
                ))
            )
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
        $this->loader->load(__DIR__ . '/fixtures/definition_not_exist.json');
    }

    /**
     * It should throw an exception if an unsupported type is passed as a definition
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid definition
     */
    public function testDefinitionInvalidType()
    {
        $this->loader->load(new \stdClass);
    }

    /**
     * It should throw an exception if a file contains invalid JSON
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not decode
     */
    public function testAcceptFileNameNotValidJson()
    {
        $this->loader->load(__DIR__ . '/fixtures/definition_invalid.json');
    }

    /**
     * It should include other configurations
     */
    public function testInclude()
    {
        $result = $this->loader->load(__DIR__ . '/fixtures/include.json');
        $this->assertDefinition(array(
            'classes' => array(
                'stability' => array(
                    array('printf', array('format' => 'hi')),
                ),
                'foo' => array(
                    array('printf')
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
     * It should throw an exception if an invalid number of items are supplied in the include tuple
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid number
     */
    public function testInvalidNumberOfItems()
    {
        $this->loader->load(array('includes' => array(array('one', 'two', 'three'))));
    }

    private function assertDefinition($expected, $definition)
    {
        $this->assertInstanceOf('PhpBench\Tabular\Definition', $definition);
        $this->assertEquals($expected, $definition->getArrayCopy());
    }
}
