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

use PhpBench\Tabular\Dom\TableDom;

class TableDomTest extends \PHPUnit_Framework_TestCase
{
    private $tableDom;

    public function setUp()
    {
        $this->tableDom = new TableDom();
        $this->tableDom->load(__DIR__ . '/../fixtures/table.xml');
    }

    /**
     * It should return a full array representation.
     */
    public function testToArray()
    {
        $result = $this->tableDom->toArray();
        $this->assertEquals(array(
            array(
                'time' => '444',
                'memory' => '444',
            ),
            array(
                'time' => '444',
                'memory' => '444',
            ),
            array(
                'time' => '888',
                'memory' => '888',
            ),
        ), $result);
    }

    /**
     * It should return a specific group as an array.
     */
    public function testToArrayGroup()
    {
        $result = $this->tableDom->toArray('footer');
        $this->assertEquals(array(
            array(
                'time' => '888',
                'memory' => '888',
            ),
        ), $result);

        $result = $this->tableDom->toArray('body');
        $this->assertEquals(array(
            array(
                'time' => '444',
                'memory' => '444',
            ),
            array(
                'time' => '444',
                'memory' => '444',
            ),
        ), $result);
    }
}
