<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular\Tests\Unit\Definition;

use PhpBench\Tabular\Definition;
use PhpBench\Tabular\Definition\Expander;

class ExpanderTest extends \PHPUnit_Framework_TestCase
{
    private $expander;

    public function setUp()
    {
        $this->expander = new Expander();
    }

    /**
     * It should expand the definition to include a cell definition for each determined
     * column name.
     */
    public function testExpandAllColumnNames()
    {
        $definition = $this->loadDefinition(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array('name' => 'one', 'literal' => '1'),
                    ),
                ),
                array(
                    'cells' => array(
                        array('name' => 'two', 'literal' => '2'),
                    ),
                ),
                array(
                    'cells' => array(
                        array('name' => 'three', 'literal' => '3'),
                        array('name' => 'four', 'literal' => '4'),
                    ),
                ),
            ),
        ));
        $definition->setMetadata(array(
            'one', 'two', 'three', 'four',
        ), array());

        $this->expander->expand($definition);
        $result = $definition->getArrayCopy();

        $this->assertEquals(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array('name' => 'one', 'literal' => '1'),
                        array('name' => 'two', 'literal' => null),
                        array('name' => 'three', 'literal' => null),
                        array('name' => 'four', 'literal' => null),
                    ),
                ),
                array(
                    'cells' => array(
                        array('name' => 'one', 'literal' => null),
                        array('name' => 'two', 'literal' => '2'),
                        array('name' => 'three', 'literal' => null),
                        array('name' => 'four', 'literal' => null),
                    ),
                ),
                array(
                    'cells' => array(
                        array('name' => 'one', 'literal' => null),
                        array('name' => 'two', 'literal' => null),
                        array('name' => 'three', 'literal' => '3'),
                        array('name' => 'four', 'literal' => '4'),
                    ),
                ),
            ),
        ), $result);
    }

    /**
     * It should iterate cells.
     */
    public function testIterateRows()
    {
        $definition = $this->loadDefinition(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => '{{ cell.item }}',
                            'literal' => '1',
                            'with_items' => array('one', 'two'),
                        ),
                    ),
                ),
                array(
                    'cells' => array(
                        array('name' => 'one', 'literal' => '2'),
                        array('name' => 'two', 'literal' => '2'),
                        array('name' => 'three', 'literal' => '2'),
                    ),
                ),
            ),
        ));
        $definition->setMetadata(array(
            'one', 'two', 'three',
        ), array());

        $this->expander->expand($definition);
        $result = $definition->getArrayCopy();

        $this->assertEquals(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array('name' => 'one', 'literal' => '1'),
                        array('name' => 'two', 'literal' => '1'),
                        array('name' => 'three', 'literal' => null),
                    ),
                ),
                array(
                    'cells' => array(
                        array('name' => 'one', 'literal' => '2'),
                        array('name' => 'two', 'literal' => '2'),
                        array('name' => 'three', 'literal' => '2'),
                    ),
                ),
            ),
        ), $result);
    }

    /**
     * It should iterate rows.
     */
    public function testIterateCells()
    {
        $definition = $this->loadDefinition(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => 'one',
                            'literal' => '{{ row.item }}',
                        ),
                    ),
                    'with_items' => array('one', 'two'),
                ),
            ),
        ));
        $definition->setMetadata(array(
            'one',
        ), array());

        $this->expander->expand($definition);
        $result = $definition->getArrayCopy();

        $this->assertEquals(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array('name' => 'one', 'literal' => 'one'),
                    ),
                ),
                array(
                    'cells' => array(
                        array('name' => 'one', 'literal' => 'two'),
                    ),
                ),
            ),
        ), $result);
    }

    /**
     * It should replace tokens in expressions
     * It should replace tokens in literals
     * It should replace tokens in queries.
     */
    public function testReplaceTokens()
    {
        $definition = $this->loadDefinition(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array(
                            'name' => '{{ cell.item }}',
                            'class' => '{{ row.item }}-{{ cell.item }}',
                            'literal' => '{{ row.item }}-{{ cell.item }}',
                            'with_items' => array('alpha', 'beta'),
                        ),
                        array(
                            'name' => 'expr-{{ cell.item }}',
                            'expr' => 'sum(//{{ param.foo }}/{{ row.item }}/{{ cell.item }}',
                            'with_items' => array('alpha', 'beta'),
                        ),
                    ),
                    'with_items' => array('one', 'two'),
                    'with_query' => '//{{ row.item }}/{{ param.foo }}',
                ),
            ),
        ));
        $definition->setMetadata(array(
            'alpha', 'beta', 'expr-alpha', 'expr-beta',
        ), array());
        $this->expander->expand($definition, array('foo' => 'foo'));

        $result = $definition->getArrayCopy();
        $this->assertEquals(array(
            'rows' => array(
                array(
                    'cells' => array(
                        array('name' => 'alpha', 'class' => 'one-alpha', 'literal' => 'one-alpha'),
                        array('name' => 'beta', 'class' => 'one-beta', 'literal' => 'one-beta'),
                        array('name' => 'expr-alpha', 'expr' => 'sum(//foo/one/alpha'),
                        array('name' => 'expr-beta', 'expr' => 'sum(//foo/one/beta'),
                    ),
                    'with_query' => '//one/foo',
                ),
                array(
                    'cells' => array(
                        array('name' => 'alpha', 'class' => 'two-alpha', 'literal' => 'two-alpha'),
                        array('name' => 'beta', 'class' => 'two-beta', 'literal' => 'two-beta'),
                        array('name' => 'expr-alpha', 'expr' => 'sum(//foo/two/alpha'),
                        array('name' => 'expr-beta', 'expr' => 'sum(//foo/two/beta'),
                    ),
                    'with_query' => '//two/foo',
                ),
            ),
        ), $result);
    }

    private function loadDefinition(array $definition)
    {
        return new Definition($definition);
    }
}
