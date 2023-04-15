<?php

namespace Quatrevieux\Form\View;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provide default implementation of {@see LabelInterface::translatedLabel()}
 * To use this trait, the class must implement interface {@see LabelInterface}
 */
trait LabelTrait
{
    abstract public function label(): string;

    public function translatedLabel(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), [], null, $locale);
    }
}
