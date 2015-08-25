<?php

namespace PhpBench\Tabular\Tests\Unit;

use PhpBench\Tabular\Definition;

class DefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testDefinition()
    {
        $definition = new Definition(array(
            'foo' => 'bar'
        ), '/path/to');

        $this->assertEquals('bar', $definition['foo']);
        $this->assertEquals('/path', $definition->getBasePath());
    }
}
