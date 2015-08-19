<?php

namespace PhpBench\Tabular\Tests\Unit;

use PhpBench\Tabular\Sort;
use PhpBench\Tabular\Dom\Document;

class SortTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should sort and preserve order for unchanging comparisons.
     */
    public function testSort()
    {
        $array = array(
            array('col1' => 20, 'col2' => 20),
            array('col1' => 20, 'col2' => 10),
            array('col1' => 10, 'col2' => 50),
            array('col1' => 10, 'col2' => 10),
            array('col1' => 10, 'col2' => 20),
        );

        $expected = array(
            array('col1' => 10, 'col2' => 50),
            array('col1' => 10, 'col2' => 10),
            array('col1' => 10, 'col2' => 20),
            array('col1' => 20, 'col2' => 20),
            array('col1' => 20, 'col2' => 10),
        );

        Sort::mergesort($array, function ($row1, $row2) {
            return strcmp($row1['col1'], $row2['col1']);
        });

        $this->assertEquals($expected, $array);
    }

    /**
     * It should sort a table
     */
    public function testSortTable()
    {
        $table = $this->createTable(<<<EOT
<?xml version="1.0"?>
<table>
    <group name="_default">
        <row>
            <cell name="foobar">10000</cell>
        </row>
        <row>
            <cell name="foobar">100</cell>
        </row>
    </group>
</table>
EOT
        );

        Sort::sortTable($table, array('foobar' => 'asc'));

        $this->assertSortOrder($table, 0, 'foobar', 100);
        $this->assertSortOrder($table, 1, 'foobar', 10000);
    }

    /**
     * It should sort only a specific group
     * It should sort alphabetically
     */
    public function testSortTableGroup()
    {
        $table = $this->createTable(<<<EOT
<?xml version="1.0"?>
<table>
    <group name="_default">
        <row>
            <cell name="foobar">10000</cell>
        </row>
        <row>
            <cell name="foobar">100</cell>
        </row>
    </group>
    <group name="barbar">
        <row>
            <cell name="foobar">cccc</cell>
        </row>
        <row>
            <cell name="foobar">aaaa</cell>
        </row>
    </group>
</table>
EOT
        );

        Sort::sortTable($table, array('barbar#foobar' => 'asc'));

        $this->assertSortOrder($table, 0, 'foobar', 10000);
        $this->assertSortOrder($table, 1, 'foobar', 100);
        $this->assertSortOrder($table, 2, 'foobar', 'aaaa');
        $this->assertSortOrder($table, 3, 'foobar', 'cccc');
    }

    /**
     * It should sort descending
     */
    public function sortDescending()
    {
        $table = $this->createTable(<<<EOT
<?xml version="1.0"?>
<table>
    <group name="_default">
        <row>
            <cell name="foobar">100</cell>
        </row>
        <row>
            <cell name="foobar">10000</cell>
        </row>
    </group>
</table>
EOT
        );

        Sort::sortTable($table, array('foobar' => 'desc'));

        $this->assertSortOrder($table, 0, 'foobar', 10000);
        $this->assertSortOrder($table, 1, 'foobar', 100);
    }

    /**
     * It should sort with multiple columns
     */
    public function testSortMultipleColumns()
    {
        $table = $this->createTable(<<<EOT
<?xml version="1.0"?>
<table>
    <group name="_default">
        <row>
            <cell name="chocolate">10</cell>
            <cell name="coffee">20</cell>
        </row>
        <row>
            <cell name="chocolate">10</cell>
            <cell name="coffee">10</cell>
        </row>
        <row>
            <cell name="chocolate">5</cell>
            <cell name="coffee">20</cell>
        </row>
    </group>
</table>
EOT
        );

        Sort::sortTable($table, array('chocolate', 'coffee'));

        $this->assertSortOrder($table, 0, 'chocolate', 5);
        $this->assertSortOrder($table, 0, 'coffee', 20);
        $this->assertSortOrder($table, 1, 'chocolate', 10);
        $this->assertSortOrder($table, 1, 'coffee', 10);
        $this->assertSortOrder($table, 2, 'chocolate', 10);
        $this->assertSortOrder($table, 2, 'coffee', 20);
    }

    private function createTable($xml)
    {
        $table = new Document();
        $table->formatOutput = true;
        $table->loadXml($xml);

        return $table;
    }

    private function assertSortOrder($table, $position, $column, $expected)
    {
        $rowEls = $table->xpath()->query('//row');
        $value = $rowEls->item($position)->query('./cell[@name="' . $column . '"]')->item(0)->nodeValue;
        $this->assertEquals($expected, $value);
    }
}
