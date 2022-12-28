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

    public function test_json()
    {
        $this->assertEquals('{"code":"d2e95635-fdb6-4752-acb4-aa8f76f64de6","message":"bar baz","parameters":{"foo":"baz"}}', json_encode(new FieldError('bar {{ foo }}', ['foo' => 'baz'], 'd2e95635-fdb6-4752-acb4-aa8f76f64de6')));
        $this->assertEquals('{"code":"d2e95635-fdb6-4752-acb4-aa8f76f64de6","message":"bar"}', json_encode(new FieldError('bar', [], 'd2e95635-fdb6-4752-acb4-aa8f76f64de6')));
    }
}
