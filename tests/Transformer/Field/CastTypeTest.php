<?php

namespace Quatrevieux\Form\Transformer\Field;

use ArrayAccess;
use Closure;
use PHPUnit\Framework\TestCase;
use Traversable;

class CastTypeTest extends TestCase
{
    /**
     * @dataProvider provideCastValues
     */
    public function test_cast(CastType $type, mixed $value, mixed $castedValue)
    {
        if (is_object($castedValue)) {
            $this->assertEquals($castedValue, $type->cast($value));
            $this->assertEquals($castedValue, eval('return ' . $type->generateCastExpression(var_export($value, true)) . ';'));
        } else {
            $this->assertSame($castedValue, $type->cast($value));
            $this->assertSame($castedValue, eval('return ' . $type->generateCastExpression(var_export($value, true)) . ';'));
        }
    }

    public function provideCastValues()
    {
        return [
            [CastType::Int, '-1', -1],
            [CastType::Int, '', null],
            [CastType::Int, 1.5, 1],
            [CastType::Int, 'foo', 0],
            [CastType::Int, null, null],
            [CastType::Int, [], null],
            [CastType::Int, new \stdClass(), null],

            [CastType::Float, '-1', -1.0],
            [CastType::Float, '1.258', 1.258],
            [CastType::Float, '', null],
            [CastType::Float, 1.5, 1.5],
            [CastType::Float, 'foo', 0.0],
            [CastType::Float, null, null],
            [CastType::Float, [], null],
            [CastType::Float, new \stdClass(), null],

            [CastType::String, '-1', '-1'],
            [CastType::String, '', ''],
            [CastType::String, 'foo', 'foo'],
            [CastType::String, null, null],
            [CastType::String, [], null],
            [CastType::String, new \stdClass(), null],
            [CastType::String, new TestingStringableClass(), 'foo'],

            [CastType::Bool, '-1', true],
            [CastType::Bool, '1', true],
            [CastType::Bool, 'foo', true],
            [CastType::Bool, '', false],
            [CastType::Bool, '0', false],
            [CastType::Bool, null, null],
            [CastType::Bool, [], null],
            [CastType::Bool, new \stdClass(), null],

            [CastType::Array, '-1', ['-1']],
            [CastType::Array, 'foo', ['foo']],
            [CastType::Array, '', ['']],
            [CastType::Array, 0, [0]],
            [CastType::Array, null, null],
            [CastType::Array, [], []],
            [CastType::Array, [1, 2], [1, 2]],
            [CastType::Array, (object) ['foo' => 'bar'], ['foo' => 'bar']],

            [CastType::Object, -1, (object) ['scalar' => '-1']],
            [CastType::Object, 'foo', (object) ['scalar' => 'foo']],
            [CastType::Object, '', (object) ['scalar' => '']],
            [CastType::Object, 0, (object) ['scalar' => 0]],
            [CastType::Object, null, null],
            [CastType::Object, [], new \stdClass()],
            [CastType::Object, [1, 2], (object) [0 => 1, 1 => 2]],
            [CastType::Object, (object) ['foo' => 'bar'], (object) ['foo' => 'bar']],

            [CastType::Mixed, '-1', '-1'],
            [CastType::Mixed, '', ''],
            [CastType::Mixed, 1.5, 1.5],
            [CastType::Mixed, 42, 42],
            [CastType::Mixed, 'foo', 'foo'],
            [CastType::Mixed, null, null],
            [CastType::Mixed, [], []],
            [CastType::Mixed, $o = new \stdClass(), $o],
        ];
    }

    public function test_generateCastExpression()
    {
        $this->assertSame('(($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null) !== \'\' && is_scalar($__tmp_cf8d20da9cb97be602abb1ce003a22b3) ? (int) $__tmp_cf8d20da9cb97be602abb1ce003a22b3 : null)', CastType::Int->generateCastExpression('$data["foo"] ?? null'));
        $this->assertSame('(($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null) !== \'\' && is_scalar($__tmp_cf8d20da9cb97be602abb1ce003a22b3) ? (float) $__tmp_cf8d20da9cb97be602abb1ce003a22b3 : null)', CastType::Float->generateCastExpression('$data["foo"] ?? null'));
        $this->assertSame('(is_scalar($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null) || $__tmp_cf8d20da9cb97be602abb1ce003a22b3 instanceof \Stringable ? (string) $__tmp_cf8d20da9cb97be602abb1ce003a22b3 : null)', CastType::String->generateCastExpression('$data["foo"] ?? null'));
        $this->assertSame('(is_scalar($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null) ? (bool) $__tmp_cf8d20da9cb97be602abb1ce003a22b3 : null)', CastType::Bool->generateCastExpression('$data["foo"] ?? null'));
        $this->assertSame('(($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null) !== null ? (object) $__tmp_cf8d20da9cb97be602abb1ce003a22b3 : null)', CastType::Object->generateCastExpression('$data["foo"] ?? null'));
        $this->assertSame('(($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null) !== null ? (array) $__tmp_cf8d20da9cb97be602abb1ce003a22b3 : null)', CastType::Array->generateCastExpression('$data["foo"] ?? null'));
        $this->assertSame('$data["foo"] ?? null', CastType::Mixed->generateCastExpression('$data["foo"] ?? null'));
    }

    public function test_fromReflectionType()
    {
        $typeOf = fn (string $prop) => (new \ReflectionProperty(WithTypedProperties::class, $prop))->getType();

        $this->assertSame(CastType::Int, CastType::fromReflectionType($typeOf('int')));
        $this->assertSame(CastType::Int, CastType::fromReflectionType($typeOf('nullableInt')));
        $this->assertSame(CastType::Int, CastType::fromReflectionType($typeOf('nullableIntWithUnion')));
        $this->assertSame(CastType::String, CastType::fromReflectionType($typeOf('string')));
        $this->assertSame(CastType::Float, CastType::fromReflectionType($typeOf('float')));
        $this->assertSame(CastType::Bool, CastType::fromReflectionType($typeOf('bool')));
        $this->assertSame(CastType::Array, CastType::fromReflectionType($typeOf('array')));
        $this->assertSame(CastType::Object, CastType::fromReflectionType($typeOf('object')));
        $this->assertSame(CastType::Mixed, CastType::fromReflectionType($typeOf('iterable')));
        $this->assertSame(CastType::Mixed, CastType::fromReflectionType($typeOf('closure')));
        $this->assertSame(CastType::Mixed, CastType::fromReflectionType($typeOf('union')));
        $this->assertSame(CastType::Mixed, CastType::fromReflectionType($typeOf('intersection')));
        $this->assertSame(CastType::Mixed, CastType::fromReflectionType($typeOf('mixed')));
    }
}

class WithTypedProperties
{
    public int $int;
    public ?int $nullableInt;
    public int|null $nullableIntWithUnion;
    public string $string;
    public float $float;
    public bool $bool;
    public array $array;
    public object $object;
    public iterable $iterable;
    public Closure $closure;
    public object|string $union;
    public Traversable&ArrayAccess $intersection;
    public mixed $mixed;
}

class TestingStringableClass
{
    public function __toString(): string
    {
        return 'foo';
    }

    // handle var_export format
    public static function __set_state(array $an_array): object
    {
        return new TestingStringableClass();
    }
}
