<?php

namespace PhpBench\Tabular\Tests\Unit;

use PhpBench\Tabular\TokenReplacer;

class TokenReplacerTest extends \PHPUnit_Framework_TestCase
{
    private $tokenReplacer;

    public function setUp()
    {
        $this->tokenReplacer = new TokenReplacer();
    }

    /**
     * It should replace tokens with scalars
     * It should replace tokens with arrays
     * *
     * @dataProvider provideScalarReplace
     */
    public function testScalarReplace($subject, $rowItem, $cellItem, array $parameters, $expected)
    {
        $result = $this->tokenReplacer->replaceTokens($subject, $rowItem, $cellItem, $parameters);
        $this->assertEquals($expected, $result);
    }

    public function provideScalarReplace()
    {
        return array(
            array(
                '//row[@name="{{ row.item }}"]//{{ cell.item }}',
                'row-item',
                'cell-item',
                array(),
                '//row[@name="row-item"]//cell-item',
            ),
            array(
                '{{ cell.class }}-{{ cell.foo }}',
                'cell',
                array('class' => 'orange', 'foo' => 'bar'),
                array(),
                'orange-bar',
            ),
            array(
                '{{ cell.class.color.bolar }}',
                null,
                array('class' => array('color' => array('bolar' => 'blue'))),
                array(),
                'blue',
            ),
            array(
                '{{ param.foo }}',
                null,
                null,
                array('foo' => 'bar'),
                'bar',
            ),
            array(
                '{{ param.foo.boo }}-{{ cell.item }}',
                null,
                'foo',
                array('foo' => array('boo' => 'baz')),
                'baz-foo',
            ),
        );
    }

    /**
     * It should throw an exception if the token has no body
     *
     * @dataProvider provideEmptyTokenBody
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Empty token in string
     */
    public function testEmptyTokenBody($string)
    {
        $this->tokenReplacer->replaceTokens(
            $string,
            null,
            null
        );
    }

    public function provideEmptyTokenBody()
    {
        return array(
            array('//bar{{}}'),
            array('//bar{{   }}'),
            array('{{   }}'),
        );
    }

    /**
     * It should throw an exception if the parameter context is invalid
     *
     * @dataProvider provideInvalidParameterContext
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown parameter context
     */
    public function testInvalidParameterContext($string)
    {
        $this->tokenReplacer->replaceTokens(
            $string,
            null,
            null
        );
    }

    public function provideInvalidParameterContext()
    {
        return array(
            array('//bar{{ foobar.barbar }}'),
            array('//bar{{narnar.aaaa}}'),
        );
    }

    /**
     * It should throw an exception if a simple scalar value is provided and
     * the param is not named *.item
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The token "{{ cell.barbar }}" is to be replaced by a simple scalar value, it should be named "cell.item"
     */
    public function testInvalidParameterNameForScalar()
    {
        $this->tokenReplacer->replaceTokens(
            '{{ cell.barbar }}',
            null,
            'bar'
        );
    }

    /**
     * It should throw an exception if a parameter is neither scalar nor an array
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid parameter type
     */
    public function testInvalidParameterType()
    {
        $this->tokenReplacer->replaceTokens(
            '{{ cell.barbar }}',
            null,
            new \stdClass
        );
    }

    /**
     * It should throw an exception if an array key does not exist
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Key "barbar" not present in value
     */
    public function testArrayKeyNotExist()
    {
        $this->tokenReplacer->replaceTokens(
            '{{ cell.barbar }}',
            null,
            array('barboo' => 'me')
        );
    }
}
