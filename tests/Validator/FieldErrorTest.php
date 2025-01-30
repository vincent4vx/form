<?php

namespace Quatrevieux\Form\Validator;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\DummyTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    public function test_withTranslator()
    {
        $error = new FieldError('bar {{ foo }}', ['foo' => 'baz']);
        $newError = $error->withTranslator(new class implements TranslatorInterface {
            public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
            {
                return 'translated';
            }

            public function getLocale(): string
            {
                return 'en';
            }
        });

        $this->assertNotSame($error, $newError);
        $this->assertEquals('translated', (string) $newError);
        $this->assertEquals('bar baz', (string) $error);
    }

    public function test_localizedMessage_without_translator()
    {
        $error = new FieldError('bar {{ foo }}', ['foo' => 'baz']);

        $this->assertEquals('bar baz', $error->localizedMessage());
    }

    public function test_localizedMessage_with_translator()
    {
        $error = new FieldError('bar {{ foo }}', ['foo' => 'baz']);
        $translator = $this->createMock(TranslatorInterface::class);
        $error = $error->withTranslator($translator);

        $translator->expects($this->once())->method('trans')->with('bar {{ foo }}', ['{{ foo }}' => 'baz'], null, 'fr')->willReturn('translated');

        $this->assertEquals('translated', $error->localizedMessage('fr'));
    }

    public function test_trans()
    {
        $error = new FieldError('bar {{ foo }}', ['foo' => 'baz']);
        $translator = $this->createMock(TranslatorInterface::class);

        $translator->expects($this->once())->method('trans')->with('bar {{ foo }}', ['{{ foo }}' => 'baz'], null, 'fr')->willReturn('translated');

        $this->assertEquals('translated', $error->trans($translator, 'fr'));
    }
}
