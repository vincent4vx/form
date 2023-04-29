<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

use PHPUnit\Framework\TestCase;

class ArrayTypeTest extends TestCase
{
    public function test_fixed()
    {
        $type = new ArrayType([
            'foo' => 'string',
            'bar' => 'int',
        ], allowExtraKeys: false);

        $this->assertTrue($type->check(['foo' => '1', 'bar' => 1]));
        $this->assertFalse($type->check(['foo' => '1', 'bar' => 1, 'baz' => 1]));
        $this->assertFalse($type->check(['foo' => '1']));
        $this->assertFalse($type->check(['foo' => '1', 'bar' => '1']));
        $this->assertFalse($type->check('foo'));

        $this->assertSame('array', $type->name());
        $this->assertSame('is_array($value) && ((array_key_exists(\'foo\', $value) && is_string($value[\'foo\']))) && ((array_key_exists(\'bar\', $value) && is_int($value[\'bar\']))) && array_diff_key($value, [\'foo\' => 1, \'bar\' => 1]) === []', $type->generateCheck('$value'));
    }

    public function test_allow_extra_keys()
    {
        $type = new ArrayType([
            'foo' => 'string',
            'bar' => 'int',
        ]);

        $this->assertTrue($type->check(['foo' => '1', 'bar' => 1]));
        $this->assertTrue($type->check(['foo' => '1', 'bar' => 1, 'baz' => 1]));
        $this->assertFalse($type->check(['foo' => '1']));
        $this->assertFalse($type->check(['foo' => '1', 'bar' => '1']));
        $this->assertFalse($type->check('foo'));

        $this->assertSame('array', $type->name());
        $this->assertSame('is_array($value) && ((array_key_exists(\'foo\', $value) && is_string($value[\'foo\']))) && ((array_key_exists(\'bar\', $value) && is_int($value[\'bar\'])))', $type->generateCheck('$value'));
    }

    public function test_array_of_type()
    {
        $type = new ArrayType(valueType: 'int|float');

        $this->assertTrue($type->check([1, 2.5, 3]));
        $this->assertFalse($type->check([1, 2, 3, '4']));

        $this->assertSame('array', $type->name());
        $this->assertSame('is_array($value) && (function ($values) {foreach ($values as $key => $value) {if (!((is_int($value)) || (is_float($value)))) {return false;}}return true;})($value)', $type->generateCheck('$value'));
    }

    public function test_array_of_key_type()
    {
        $type = new ArrayType(keyType: 'int');

        $this->assertTrue($type->check([1 => 1, 2 => 2]));
        $this->assertFalse($type->check(['foo' => 1, 2 => 2]));

        $this->assertSame('array', $type->name());
        $this->assertSame('is_array($value) && (function ($values) {foreach ($values as $key => $value) {if (!(is_int($key))) {return false;}}return true;})($value)', $type->generateCheck('$value'));
    }

    public function test_list_of_array()
    {
        $type = new ArrayType(keyType: 'int', valueType: [
            'foo' => 'string',
            'bar' => 'int',
        ]);

        $this->assertTrue($type->check([
            ['foo' => '1', 'bar' => 1],
            ['foo' => '2', 'bar' => 2],
        ]));
        $this->assertFalse($type->check([
            ['foo' => '1', 'bar' => 1],
            ['foo' => '2'],
        ]));
        $this->assertFalse($type->check([
            ['foo' => '2', 'bar' => false],
        ]));

        $this->assertSame('array', $type->name());
        $this->assertSame('is_array($value) && (function ($values) {foreach ($values as $key => $value) {if (!(is_int($key))) {return false;}if (!(is_array($value) && ((array_key_exists(\'foo\', $value) && is_string($value[\'foo\']))) && ((array_key_exists(\'bar\', $value) && is_int($value[\'bar\']))))) {return false;}}return true;})($value)', $type->generateCheck('$value'));
    }

    public function test_optional_key()
    {
        $type = new ArrayType([
            'foo?' => 'string',
            'bar?' => 'int',
        ]);

        $this->assertTrue($type->check(['foo' => '1', 'bar' => 1]));
        $this->assertTrue($type->check(['foo' => '1']));
        $this->assertFalse($type->check(['foo' => '1', 'bar' => '1']));

        $this->assertSame('array', $type->name());
        $this->assertSame('is_array($value) && ((!array_key_exists(\'foo\', $value) || is_string($value[\'foo\']))) && ((!array_key_exists(\'bar\', $value) || is_int($value[\'bar\'])))', $type->generateCheck('$value'));
    }

    public function test_optimise_key_check()
    {
        $this->assertSame('is_array($value) && (function ($values) {foreach ($values as $key => $value) {if (!(is_int($value))) {return false;}}return true;})($value)', (new ArrayType(keyType: 'int|string', valueType: 'int'))->generateCheck('$value'));
        $this->assertSame('is_array($value) && (function ($values) {foreach ($values as $key => $value) {if (!(is_int($value))) {return false;}}return true;})($value)', (new ArrayType(keyType: 'string|int|float', valueType: 'int'))->generateCheck('$value'));
        $this->assertSame('is_array($value) && (function ($values) {foreach ($values as $key => $value) {if (!(is_int($value))) {return false;}}return true;})($value)', (new ArrayType(keyType: 'mixed', valueType: 'int'))->generateCheck('$value'));
        $this->assertSame('is_array($value) && (function ($values) {foreach ($values as $key => $value) {if (!(is_int($value))) {return false;}}return true;})($value)', (new ArrayType(keyType: 'int|mixed', valueType: 'int'))->generateCheck('$value'));
    }

    public function test_optimise_value_check()
    {
        $this->assertSame('is_array($value) && (function ($values) {foreach ($values as $key => $value) {if (!(is_int($key))) {return false;}}return true;})($value)', (new ArrayType(keyType: 'int', valueType: 'mixed'))->generateCheck('$value'));
        $this->assertSame('is_array($value) && (function ($values) {foreach ($values as $key => $value) {if (!(is_int($key))) {return false;}}return true;})($value)', (new ArrayType(keyType: 'int', valueType: 'mixed|bool'))->generateCheck('$value'));
    }
}
