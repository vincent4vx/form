<?php

namespace Quatrevieux\Form\Util;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Transformer\Field\ArrayCast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\Csv;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\FieldError;

class CodeTest extends TestCase
{
    public function test_varName()
    {
        $this->assertSame('$__tmp_0e648d68d745be2623453551d81b2eeb', Code::varName('$foo'));
        $this->assertSame('$__tmp_ea2e30a06233e9cbfe2a6e6ed52fbd65', Code::varName('$foo["bar"]'));
        $this->assertSame('$__test_ea2e30a06233e9cbfe2a6e6ed52fbd65', Code::varName('$foo["bar"]', 'test'));
    }

    public function test_value()
    {
        $this->assertSame('123', Code::value(123));
        $this->assertSame('12.3', Code::value(12.3));
        $this->assertSame('true', Code::value(true));
        $this->assertSame('false', Code::value(false));
        $this->assertSame('null', Code::value(null));
        $this->assertSame("'foo'", Code::value('foo'));
        $this->assertSame("'foo' . PHP_EOL . 'bar'", Code::value('foo' . PHP_EOL . 'bar'));
        $this->assertSame("[]", Code::value([]));
        $this->assertSame("['foo', 123]", Code::value(['foo', 123]));
        $this->assertSame("['foo' => 123, 'bar' => 456]", Code::value(['foo' => 123, 'bar' => 456]));
        $this->assertSame("(object) ['foo' => 'bar']", Code::value((object) ['foo' => 'bar']));
        $this->assertSame("new \Quatrevieux\Form\Transformer\Field\Csv(separator: ';', enclosure: '')", Code::value(new Csv(separator: ';')));
        $this->assertSame("\Quatrevieux\Form\Transformer\Field\CastType::Int", Code::value(CastType::Int));
        $this->assertSame("[new \Quatrevieux\Form\Transformer\Field\Csv(separator: ';', enclosure: ''), new \Quatrevieux\Form\Transformer\Field\ArrayCast(elementType: \Quatrevieux\Form\Transformer\Field\CastType::Int, preserveKeys: true)]", Code::value([new Csv(separator: ';'), new ArrayCast(CastType::Int)]));
        $this->assertSame('$foo', Code::value(Code::raw('$foo')));
    }

    public function test_instantiate()
    {
        $this->assertSame("new \Quatrevieux\Form\Util\NewExprTestObject(pub: 'foo', prot: 123, priv: false)", Code::instantiate(new NewExprTestObject('foo', 123, false)));
        $this->assertSame("new \Quatrevieux\Form\Validator\Constraint\Length(max: 5)", Code::instantiate(new Length(max: 5)));
    }

    public function test_inlineStrtr()
    {
        $this->assertSame("'Hello ' . \$name . ' !'", Code::inlineStrtr('Hello {{ name }} !', ['{{ name }}' => '$name']));
        $this->assertSame("'Hello ' . \$name", Code::inlineStrtr('Hello {{ name }}', ['{{ name }}' => '$name']));
        $this->assertSame("'Hello ' . \$firstName . ' ' . \$lastName . ' !'", Code::inlineStrtr('Hello {{ firstName }} {{ lastName }} !', ['{{ firstName }}' => '$firstName', '{{ lastName }}' => '$lastName']));
        $this->assertSame("'Hello ' . \$firstName . \$lastName . ' !'", Code::inlineStrtr('Hello {{ firstName }}{{ lastName }} !', ['{{ firstName }}' => '$firstName', '{{ lastName }}' => '$lastName']));
        $this->assertSame("'Hello ' . \$firstName . \$lastName", Code::inlineStrtr('Hello {{ firstName }}{{ lastName }}', ['{{ firstName }}' => '$firstName', '{{ lastName }}' => '$lastName']));
    }

    public function test_call()
    {
        $this->assertSame("substr('azerty', 1, 3)", Code::call('substr', ['azerty', 1, 3]));
        $this->assertSame("substr('azerty', length: 3)", Code::call('substr', ['azerty', 'length' => 3]));
        $this->assertSame("Foo::bar('arg1', 'arg2')", Code::call('Foo::bar', ['arg1', 'arg2']));
        $this->assertSame("Foo::bar(\$foo, '\$bar')", Code::call('Foo::bar', [Code::raw('$foo'), '$bar']));
    }

    public function test_callStatic()
    {
        $this->assertSame("Foo::bar('arg1', 'arg2')", Code::callStatic('Foo', 'bar', ['arg1', 'arg2']));
        $this->assertSame("Foo::bar(\$foo, '\$bar')", Code::callStatic('Foo', 'bar', [Code::raw('$foo'), '$bar']));
        $this->assertSame("\Quatrevieux\Form\Util\CodeTest::bar(123)", Code::callStatic(self::class, 'bar', [123]));
    }

    public function test_callMethod()
    {
        $this->assertSame("\$foo->bar('arg1', 'arg2')", Code::callMethod('$foo', 'bar', ['arg1', 'arg2']));
        $this->assertSame("\$foo->bar(\$foo, '\$bar')", Code::callMethod('$foo', 'bar', [Code::raw('$foo'), '$bar']));
    }

    public function test_new()
    {
        $this->assertSame("new \Quatrevieux\Form\Transformer\Field\Csv(';')", Code::new(Csv::class, [';']));
        $this->assertSame("new Csv(';')", Code::new('Csv', [';']));
    }

    public function test_instanceOfOrNull()
    {
        $this->assertSame('($__tmp_ea2e30a06233e9cbfe2a6e6ed52fbd65 = $foo["bar"]) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_ea2e30a06233e9cbfe2a6e6ed52fbd65 : null', Code::instanceOfOrNull('$foo["bar"]', FieldError::class));
        $this->assertSame('null', Code::instanceOfOrNull('null', FieldError::class));
    }

    public function test_instanceOfOr()
    {
        $this->assertSame('($__tmp_ea2e30a06233e9cbfe2a6e6ed52fbd65 = $foo["bar"]) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_ea2e30a06233e9cbfe2a6e6ed52fbd65 : new \Quatrevieux\Form\Validator\FieldError(message: \'default\', parameters: [], code: \'bb8ebf72-1310-4d65-bdb5-9192708543ee\')', Code::instanceOfOr('$foo["bar"]', FieldError::class, new FieldError('default')));
    }

    public function test_isArrayOr()
    {
        $this->assertSame('(is_array($__tmp_ea2e30a06233e9cbfe2a6e6ed52fbd65 = $foo["bar"]) ? $__tmp_ea2e30a06233e9cbfe2a6e6ed52fbd65 : [])', Code::isArrayOr('$foo["bar"]', []));
        $this->assertSame('(is_array($__tmp_ea2e30a06233e9cbfe2a6e6ed52fbd65 = $foo["bar"]) ? $__tmp_ea2e30a06233e9cbfe2a6e6ed52fbd65 : null)', Code::isArrayOr('$foo["bar"]', null));
    }

    public function test_expr()
    {
        $this->assertInstanceOf(Expr::class, Code::expr('$foo'));
        $this->assertSame('$foo["bar"] ?? null', (string) Code::expr('$foo["bar"] ?? null'));
    }
}

class NewExprTestObject
{
    public function __construct(
        public string $pub,
        protected int $prot,
        private bool $priv,
    ) {
    }
}
