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

use PhpBench\Tabular\Dom\TableDom;
use PhpBench\Tabular\Formatter;

class FormatterTest extends \PHPUnit_Framework_TestCase
{
    private $registry;
    private $formatter;
    private $format;

    public function setUp()
    {
        $this->registry = $this->prophesize('PhpBench\Tabular\Formatter\RegistryInterface');
        $this->format = $this->prophesize('PhpBench\Tabular\Formatter\FormatInterface');
        $this->formatter = new Formatter($this->registry->reveal());
    }

    /**
     * It should format a table.
     */
    public function testFormatTable()
    {
        $tableDom = $this->createTable(<<<EOT
<?xml version="1.0"?>
<table>
    <row>
        <cell class="foo" name="boo">bar</cell>
        <cell class="bar" name="baz">foo</cell>
    </row>
</table>
EOT
        );

        $this->formatter->setClassDefinition(
            'foo',
            array(
                array('printf', array('format' => '%s')),
            )
        );
        $this->formatter->setClassDefinition(
            'bar',
            array(
                array('printf', array('format' => '%s')),
            )
        );
        $this->format->getDefaultOptions()->willReturn(array('format' => 'xx'));
        $this->format->format('bar', array('format' => '%s'))->willReturn('hello');
        $this->format->format('foo', array('format' => '%s'))->willReturn('hello');
        $this->registry->get('printf')->willReturn($this->format->reveal());
        $this->formatter->formatTable(
            $tableDom
        );

        $this->assertContains('hello', $tableDom->saveXml());
    }

    /**
     * It should wrap any exception thrown by the formatter.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Error encountered formatting cell "boo"
     */
    public function testWrapException()
    {
        $tableDom = $this->createTable(<<<EOT
<?xml version="1.0"?>
<table>
    <row>
        <cell class="foo" name="boo">bar</cell>
    </row>
</table>
EOT
        );

        $this->formatter->setClassDefinition(
            'foo',
            array(
                array('printf', array()),
            )
        );
        $this->format->getDefaultOptions()->willReturn(array());
        $this->format->format('bar', array())->willThrow(new \InvalidArgumentException('This is the original exception'));
        $this->registry->get('printf')->willReturn($this->format->reveal());
        $this->formatter->formatTable(
            $tableDom
        );
    }

    /**
     * It should throw an exception if there is an undefined class in the XML.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage No class defined
     */
    public function testUndefinedClass()
    {
        $tableDom = $this->createTable(<<<EOT
<?xml version="1.0"?>
<table>
    <row>
        <cell class="foo" name="boo">bar</cell>
    </row>
</table>
EOT
        );

        $this->formatter->formatTable($tableDom);
    }

    /**
     * It should throw an exception if unknown options are given to a format.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown options
     */
    public function testUnknownOptions()
    {
        $tableDom = $this->createTable(<<<EOT
<?xml version="1.0"?>
<table>
    <row>
        <cell class="foo" name="boo">bar</cell>
    </row>
</table>
EOT
        );

        $this->formatter->setClassDefinition(
            'foo',
            array(
                array('printf', array('foo' => 'x', 'bar' => 'y')),
            )
        );
        $this->format->getDefaultOptions()->willReturn(array('boo' => 'baz', 'baz' => 'boo'));
        $this->registry->get('printf')->willReturn($this->format->reveal());
        $this->formatter->formatTable(
            $tableDom
        );
    }

    private function createTable($xml)
    {
        $table = new TableDom();
        $table->formatOutput = true;
        $table->loadXml($xml);

        return $table;
    }
}
