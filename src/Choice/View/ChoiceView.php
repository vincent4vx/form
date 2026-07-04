<?php

namespace Quatrevieux\Form\Choice\View;

use Quatrevieux\Form\Choice\Label\LabelInterface;
use Quatrevieux\Form\DummyTranslator;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Represent a choice in a field with choices
 */
final class ChoiceView
{
    private ?TranslatorInterface $translator = null;

    public function __construct(
        /**
         * Choice value
         * The value is the HTTP value, not the model one.
         *
         * @var scalar
         */
        public readonly mixed $value,

        /**
         * Choice label
         * The label will be translated if a translator is set
         */
        public readonly string|LabelInterface|TranslatableInterface|null $label = null,

        /**
         * Is the selected choice ?
         * Note: multiple choice can be selected in case of array input
         */
        public readonly bool $selected = false,
    ) {}

    /**
     * Get the translated label
     * If no translator is set, the label is returned as is
     *
     * @param string|null $locale Locale to translate the message to
     * @return string|null Translated label, or null if no label is defined
     */
    public function localizedLabel(?string $locale = null): ?string
    {
        if ($this->label === null) {
            return null;
        }

        $translator = $this->translator;

        if ($this->label instanceof LabelInterface) {
            return $translator ? $this->label->translatedLabel($translator, $locale) : $this->label->label();
        }

        if ($this->label instanceof TranslatableInterface) {
            return $this->label->trans($translator ?? DummyTranslator::instance(), $locale);
        }

        return $translator ? $translator->trans($this->label, [], null, $locale) : $this->label;
    }

    /**
     * Define the translator used to translate the label
     *
     * @internal This method is used by the form view instantiator
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
