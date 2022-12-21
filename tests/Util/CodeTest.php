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
    }

    public function test_newExpression()
    {
        $this->assertSame("new \Quatrevieux\Form\Util\NewExprTestObject(pub: 'foo', prot: 123, priv: false)", Code::newExpression(new NewExprTestObject('foo', 123, false)));
    }

    public function test_inlineStrtr()
    {
        $this->assertSame("'Hello ' . \$name . ' !'", Code::inlineStrtr('Hello {{ name }} !', ['{{ name }}' => '$name']));
        $this->assertSame("'Hello ' . \$name", Code::inlineStrtr('Hello {{ name }}', ['{{ name }}' => '$name']));
        $this->assertSame("'Hello ' . \$firstName . ' ' . \$lastName . ' !'", Code::inlineStrtr('Hello {{ firstName }} {{ lastName }} !', ['{{ firstName }}' => '$firstName', '{{ lastName }}' => '$lastName']));
        $this->assertSame("'Hello ' . \$firstName . \$lastName . ' !'", Code::inlineStrtr('Hello {{ firstName }}{{ lastName }} !', ['{{ firstName }}' => '$firstName', '{{ lastName }}' => '$lastName']));
        $this->assertSame("'Hello ' . \$firstName . \$lastName", Code::inlineStrtr('Hello {{ firstName }}{{ lastName }}', ['{{ firstName }}' => '$firstName', '{{ lastName }}' => '$lastName']));
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
