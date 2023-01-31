<?php

namespace Quatrevieux\Form\Util;

use PHPUnit\Framework\TestCase;

class ExprTest extends TestCase
{
    public function test_default()
    {
        $this->assertSame('$foo', (string) new Expr('$foo'));
    }

    public function test_property_access()
    {
        $this->assertEquals('$foo->bar', (new Expr('$foo'))->bar);
    }

    public function test_method_call()
    {
        $this->assertEquals('$foo->bar()', (new Expr('$foo'))->bar());
        $this->assertEquals('$foo->bar(true, 123, [\'foo\' => \'bar\'])', (new Expr('$foo'))->bar(true, 123, ['foo' => 'bar']));
    }

    public function test_invoke()
    {
        $this->assertEquals('(function () {})(123, true)', (new Expr('function () {}'))(123, true));
    }

    public function test_isArrayOr()
    {
        $this->assertEquals('(is_array($__tmp_0e648d68d745be2623453551d81b2eeb = $foo) ? $__tmp_0e648d68d745be2623453551d81b2eeb : [123])', (new Expr('$foo'))->isArrayOr([123]));
    }

    public function test_isInstanceOfOr()
    {
        $this->assertEquals('($__tmp_0e648d68d745be2623453551d81b2eeb = $foo) instanceof \Foo ? $__tmp_0e648d68d745be2623453551d81b2eeb : NULL', (new Expr('$foo'))->isInstanceOfOr('Foo', null));
    }

    public function test_chain()
    {
        $this->assertEquals('$foo->bar->baz()->oof', (new Expr('$foo'))->bar->baz()->oof);
    }

    public function test_this()
    {
        $this->assertEquals('$this->foo', Expr::this()->foo);
    }
}
