<?php

namespace Quatrevieux\Form\View;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChoiceViewTest extends TestCase
{
    public function test_localizedLabel()
    {
        $view = new ChoiceView('value', 'label');

        $this->assertSame('label', $view->localizedLabel());

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->with('label')->willReturn('translated');
        $view->setTranslator($translator);

        $this->assertSame('translated', $view->localizedLabel());
    }

    public function test_use_LabelInterface()
    {
        $label = new class implements LabelInterface {
            public function label(): string
            {
                return 'label';
            }

            public function translatedLabel(TranslatorInterface $translator, ?string $locale = null): string
            {
                return $translator->trans('label', [], null, $locale);
            }
        };

        $view = new ChoiceView('value', $label);

        $this->assertSame('label', $view->localizedLabel());

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->with('label')->willReturn('translated');
        $view->setTranslator($translator);

        $this->assertSame('translated', $view->localizedLabel());
    }

    public function test_localizedLabel_missing_label()
    {
        $view = new ChoiceView('value', null);

        $this->assertNull($view->localizedLabel());
    }
}
