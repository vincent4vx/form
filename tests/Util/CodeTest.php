<?php

namespace Quatrevieux\Form\Util;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Transformer\Field\ArrayCast;
use Quatrevieux\Form\Transformer\Field\Cast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\Csv;

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
        $this->assertSame('NULL', Code::value(null));
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
