<?php

namespace Quatrevieux\Form\View;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Base type for providing a label
 * Can be used on {@see ChoiceView::$label} instead of a string.
 */
interface LabelInterface
{
    /**
     * Get the label
     */
    public function label(): string;

    /**
     * Get the translated label
     *
     * @param TranslatorInterface $translator Translator to use
     * @param string|null $locale Locale to translate the message to. If null, the current locale is used.
     */
    public function translatedLabel(TranslatorInterface $translator, ?string $locale = null): string;
}
