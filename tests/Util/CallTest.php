<?php

namespace Quatrevieux\Form\Util;

use PHPUnit\Framework\TestCase;

class CallTest extends TestCase
{
    public function test_static()
    {
        $this->assertSame("Foo::bar('arg1', 'arg2')", Call::static('Foo')->bar('arg1', 'arg2'));
    }

    public function test_object()
    {
        $this->assertSame("\$foo->bar('arg1', 'arg2')", Call::object('$foo')->bar('arg1', 'arg2'));
    }

    public function test_global_function()
    {
        $this->assertSame("foo('arg1', 'arg2')", Call::foo('arg1', 'arg2'));
    }
}
