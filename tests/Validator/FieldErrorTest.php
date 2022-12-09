<?php

namespace Quatrevieux\Form\Validator;

use PHPUnit\Framework\TestCase;

class FieldErrorTest extends TestCase
{
    public function test_getters()
    {
        $error = new FieldError('bar');

        $this->assertEquals('bar', $error->message);
        $this->assertEquals('bar', (string) $error);
    }
}
