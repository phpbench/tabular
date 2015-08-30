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

class DefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testDefinition()
    {
        $definition = new Definition(array(
            'foo' => 'bar',
        ), '/path/to');

        $this->assertEquals('bar', $definition['foo']);
        $this->assertEquals('/path', $definition->getBasePath());
    }
}
