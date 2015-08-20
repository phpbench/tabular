<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular\Tests\Unit\Formatter;

use PhpBench\Tabular\Formatter\Registry\ArrayRegistry;

class ArrayRegistryTest extends \PHPUnit_Framework_TestCase
{
    private $registry;
    private $format;

    public function setUp()
    {
        $this->registry = new ArrayRegistry();
        $this->format = $this->prophesize('PhpBench\Tabular\Formatter\FormatInterface');
    }

    /**
     * It should register and retrieve formats.
     */
    public function testRegisterRetrieve()
    {
        $this->registry->register('hello', $this->format->reveal());
        $format = $this->registry->get('hello');
        $this->assertSame($this->format->reveal(), $format);
    }

    /**
     * It should throw an exception if an attempt is made to add a duplicate format.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Formatter with name
     */
    public function testRegisterExisting()
    {
        $this->registry->register('hello', $this->format->reveal());
        $this->registry->register('hello', $this->format->reveal());
    }

    /**
     * It should throw an exception if an unknown formatter is requiested.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown formatter
     */
    public function testUnknownFormatter()
    {
        $this->registry->get('hello');
    }
}
