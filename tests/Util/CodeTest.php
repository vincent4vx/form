<?php

namespace Quatrevieux\Form\Util;

use PHPUnit\Framework\TestCase;

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
        $this->assertSame("array (\n)", Code::value([]));
        $this->assertSame("array (\n  0 => 'foo',\n  1 => 123,\n)", Code::value(['foo', 123]));
    }

    public function test_newExpression()
    {
        $this->assertSame("new \Quatrevieux\Form\Util\NewExprTestObject(pub: 'foo', prot: 123, priv: false)", Code::newExpression(new NewExprTestObject('foo', 123, false)));
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
