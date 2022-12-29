<?php

namespace Quatrevieux\Form;

use PHPUnit\Framework\TestCase;

class DummyTranslatorTest extends TestCase
{
    public function test_instance()
    {
        $this->assertSame(DummyTranslator::instance(), DummyTranslator::instance());
    }

    public function test_trans()
    {
        $this->assertSame('foo', DummyTranslator::instance()->trans('foo'));
        $this->assertSame('Hello John !', DummyTranslator::instance()->trans('Hello {{ name }} !', ['{{ name }}' => 'John']));
    }
}
