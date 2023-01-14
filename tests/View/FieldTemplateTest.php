<?php

namespace Quatrevieux\Form\View;

use PHPUnit\Framework\TestCase;

class FieldTemplateTest extends TestCase
{
    public function test_invoke()
    {
        $view = new FieldView(
            'name',
            'my value',
            null,
            [
                'attr' => 'value',
                'flag' => true,
                'disabledFag' => false,
            ]
        );

        $this->assertSame('<input name="name" value="my value" attr="value" flag />', (FieldTemplate::Input)($view));
        $this->assertSame('<textarea name="name" attr="value" flag >my value</textarea>', (FieldTemplate::Textarea)($view));
    }

    public function test_renderTemplate()
    {
        $view = new FieldView(
            'name',
            'my value',
            null,
            [
                'attr' => 'value',
                'flag' => true,
                'disabledFag' => false,
            ]
        );

        $this->assertSame('<MyCustomElement name="name" attr="value" flag >my value</MyCustomElement>', FieldTemplate::renderTemplate('<MyCustomElement name="{{ name }}" {{ attributes }}>{{ value }}</MyCustomElement>', $view));
    }

    public function test_escaping()
    {
        $this->assertSame('<input name="&gt;&lt;unsaf€&apos;/na&quot;me" value="" />', (FieldTemplate::Input)(new FieldView('><unsaf€\'/na"me', null, null)));
        $this->assertSame('<input name="foo[bar]" value="&quot;/&gt;&lt;script&gt;alert(&apos;xss&apos;);&lt;/script&gt;" />', (FieldTemplate::Input)(new FieldView('foo[bar]', '"/><script>alert(\'xss\');</script>', null)));
        $this->assertSame('<input name="foo" value="" attr="&quot;/&gt;&lt;script&gt;alert(&apos;xss&apos;);&lt;/script&gt;" />', (FieldTemplate::Input)(new FieldView('foo', null, null, ['attr' => '"/><script>alert(\'xss\');</script>'])));
        $this->assertSame('<input name="foo" value="" &quot;/&gt;&lt;script&gt;alert(&apos;xss&apos;);&lt;/script&gt; />', (FieldTemplate::Input)(new FieldView('foo', null, null, ['"/><script>alert(\'xss\');</script>' => true])));
        $this->assertSame('<input name="foo" value="" &quot;/&gt;&lt;script&gt;alert(&apos;xss&apos;);&lt;/script&gt;="bar" />', (FieldTemplate::Input)(new FieldView('foo', null, null, ['"/><script>alert(\'xss\');</script>' => 'bar'])));
    }
}
