<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Ramsey\Uuid\Uuid;

class RegexTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(Regex::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'Regex')->toString());
    }

    public function test_html_attribute()
    {
        $this->assertSame(['pattern' => '.*foo.*'], (new Regex('foo'))->getAttributes());
        $this->assertSame(['pattern' => 'foo.*'], (new Regex('^foo'))->getAttributes());
        $this->assertSame(['pattern' => '.*foo'], (new Regex('foo$'))->getAttributes());
        $this->assertSame(['pattern' => 'foo'], (new Regex('^foo$'))->getAttributes());
        $this->assertSame([], (new Regex('^foo$', flags: 'u'))->getAttributes());
        $this->assertSame(['pattern' => '.*[fF][oO][oO].*'], (new Regex('foo', flags: 'i'))->getAttributes());
        $this->assertSame(['pattern' => '.*[fF][oO][oO].*'], (new Regex('FOO', flags: 'i'))->getAttributes());
        $this->assertSame(['pattern' => '.*[a-zA-Z][a-z0-9A-Z0-9]+.*'], (new Regex('[a-z][a-z0-9]+', flags: 'i'))->getAttributes());
        $this->assertSame(['pattern' => '[a-zA-Z]+\[[0-90-9]+\]'], (new Regex('^[a-z]+\[[0-9]+\]$', flags: 'i'))->getAttributes());
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function test_functional(bool $generated)
    {
        $form = $generated ? $this->generatedForm(FormWithRegex::class) : $this->runtimeForm(FormWithRegex::class);

        $this->assertErrors(['foo' => 'My error message'], $form->submit(['foo' => '123'])->errors());
        $this->assertErrors(['hex' => 'invalid hex string'], $form->submit(['hex' => 'azerty'])->errors());
        $this->assertErrors(['hexNotCi' => 'invalid hex string'], $form->submit(['hexNotCi' => 'azerty'])->errors());

        $this->assertTrue($form->submit(['foo' => 'abc123'])->valid());
        $this->assertTrue($form->submit(['hex' => '#fce3'])->valid());
        $this->assertTrue($form->submit(['hexNotCi' => 'fce3'])->valid());
        $this->assertTrue($form->submit(['hex' => '0xFCE3'])->valid());
        $this->assertTrue($form->submit(['foo' => 'ABC123'])->valid());

        $this->assertFalse($form->submit(['hexNotCi' => '0xFCE3'])->valid());
    }

    public function test_generate()
    {
        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && !preg_match(\'#^(\\\#|0x)?[0-9a-f]+$#i\', (string) ($data->foo ?? null)) ? new \Quatrevieux\Form\Validator\FieldError(\'invalid hex string\', [], \'4ba73c60-bba8-58cc-a92b-7f572ecaaf1f\') : null', new Regex(pattern: '^(#|0x)?[0-9a-f]+$', flags: 'i', message: 'invalid hex string'));
    }

    public function test_invalid_pattern()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The regular expression "[A---Z]" is not valid : Internal error');

        new Regex('[A---Z]');
    }
}

class FormWithRegex
{
    #[Regex(pattern: '^[a-z][a-z0-9]+$', flags: 'i', message: 'My error message')]
    public ?string $foo;

    #[Regex(pattern: '^(#|0x)?[0-9a-f]+$', flags: 'i', message: 'invalid hex string')]
    public ?string $hex;

    #[Regex(pattern: '^(#|0x)?[0-9a-f]+$', message: 'invalid hex string')]
    public ?string $hexNotCi;
}
