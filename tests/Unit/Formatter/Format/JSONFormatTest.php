<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular\Tests\Unit\Formatter\Format;

use PhpBench\Tabular\Formatter\Format\JSONFormat;

class JSONFormatTest extends \PHPUnit_Framework_TestCase
{
    private $format;

    public function setUp()
    {
        $this->format = new JSONFormat();
    }

    public function testFormat()
    {
        $result = $this->format->format('{"hello": {"goodbye": "hello"}, "goodbye": "hello"}', array());
        $this->assertEquals(<<<EOT
{
    "hello": {
        "goodbye": "hello"
    },
    "goodbye": "hello"
}
EOT
        , $result);
    }
}
