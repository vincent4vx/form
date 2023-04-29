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
        $this->assertEquals('$foo instanceof \Foo ? $foo : null', (new Expr('$foo'))->isInstanceOfOr('Foo', null));
        $this->assertEquals('($__tmp_48dbc397b237e7c6eabd2e7f6b76eb9e = $foo->bar) instanceof \Foo ? $__tmp_48dbc397b237e7c6eabd2e7f6b76eb9e : null', (new Expr('$foo->bar'))->isInstanceOfOr('Foo', null));
    }

    public function test_chain()
    {
        $this->assertEquals('$foo->bar->baz()->oof', (new Expr('$foo'))->bar->baz()->oof);
    }

    public function test_this()
    {
        $this->assertEquals('$this->foo', Expr::this()->foo);
    }

    public function test_new()
    {
        $this->assertEquals(new Expr('new Foo()'), Expr::new('Foo'));
        $this->assertEquals(new Expr('new Foo(123, true)'), Expr::new('Foo', [123, true]));
    }

    public function test_varName()
    {
        $this->assertEquals(new Expr('$__tmp_acbd18db4cc2f85cedef654fccc4a4d8'), Expr::varName('foo'));
    }

    public function test_value()
    {
        $this->assertEquals(new Expr('123'), Expr::value(123));
        $this->assertEquals(new Expr('true'), Expr::value(true));
        $this->assertEquals('$foo', Expr::value(Code::raw('$foo')));
    }

    public function test_format()
    {
        $this->assertEquals(new Expr('$this->var instanceof Foo ? $this->var : new Foo($this->var)'), Expr::this()->var->format('{} instanceof Foo ? {} : new Foo({})'));
        $this->assertEquals(new Expr('apply($this->var, [1, 2], $bar)'), Expr::this()->var->format('apply({}, {foo}, {bar})', foo: [1, 2], bar: Code::raw('$bar')));
        $this->assertEquals(new Expr('apply($this->var, [1, 2], $bar)'), Expr::this()->var->format('apply({}, {0}, {1})', [1, 2], Code::raw('$bar')));
    }

    public function test_storeAndFormat()
    {
        $this->assertEquals(new Expr('($__tmp_0a1d84bfe00558f8bc5e242a49c17c04 = $this->var) instanceof Foo ? $__tmp_0a1d84bfe00558f8bc5e242a49c17c04 : new Foo($__tmp_0a1d84bfe00558f8bc5e242a49c17c04)'), Expr::this()->var->storeAndFormat('{} instanceof Foo ? {} : new Foo({})'));
        $this->assertEquals(new Expr('apply(($__tmp_0a1d84bfe00558f8bc5e242a49c17c04 = $this->var), [1, 2], $bar)'), Expr::this()->var->storeAndFormat('apply({}, {foo}, {bar})', foo: [1, 2], bar: Code::raw('$bar')));
        $this->assertEquals(new Expr('apply(($__tmp_0a1d84bfe00558f8bc5e242a49c17c04 = $this->var), [1, 2], $bar)'), Expr::this()->var->storeAndFormat('apply({}, {0}, {1})', [1, 2], Code::raw('$bar')));
        $this->assertEquals(new Expr('($__tmp_0a1d84bfe00558f8bc5e242a49c17c04 = $this->var) === null ? null : $__tmp_0a1d84bfe00558f8bc5e242a49c17c04->foo'), Expr::this()->var->storeAndFormat('{} === null ? null : {}->foo'));
    }

    public function test_format_missing_placeholder()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Format must contain at least one placeholder "{}"');

        Expr::this()->var->format('invalid');
    }

    public function test_storeAndFormat_missing_placeholder()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Format must contain at least one placeholder "{}"');

        Expr::this()->var->storeAndFormat('invalid');
    }
}
