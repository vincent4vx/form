<?php

namespace Quatrevieux\Form\Validator;

use PHPUnit\Framework\TestCase;

class FieldErrorTest extends TestCase
{
    public function test_getters()
    {
        $error = new FieldError('bar', ['foo' => 'baz']);

        $this->assertEquals('bar', $error->message);
        $this->assertEquals('bar', (string) $error);
        $this->assertEquals(['foo' => 'baz'], $error->parameters);
    }

    public function test_replace_placeholders()
    {
        $error = new FieldError('bar {{ foo }}', ['foo' => 'baz']);

        $this->assertEquals('bar baz', (string) $error);
    }
}
