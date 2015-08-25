<?php

namespace PhpBench\Tabular\Tests\Unit;

use PhpBench\Tabular\PathUtil;

class PathUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should return an absolute path for a relative path
     */
    public function testGetPathRelative()
    {
        $result = PathUtil::getPath('foobar/barfoo', '/path/to');
        $this->assertEquals('/path/to/foobar/barfoo', $result);
    }

    /**
     * It should return an unmodified absolute path
     */
    public function testGetPathAbsolute()
    {
        $result = PathUtil::getPath('/path/to/foobar/barfoo', '/path/to');
        $this->assertEquals('/path/to/foobar/barfoo', $result);
    }
}
