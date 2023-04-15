<?php

namespace Quatrevieux\Form\View;

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
        public readonly ?string $label = null,

        /**
         * Is the selected choice ?
         * Note: multiple choice can be selected in case of array input
         */
        public readonly bool $selected = false,
    ) {
    }

    /**
     * Get the translated label
     * If no translator is set, the label is returned as is
     *
     * @todo implements TranslatableInterface ?
     *
     * @param string|null $locale Locale to translate the message to
     * @return string|null Translated label, or null if no label is defined
     */
    public function localizedLabel(?string $locale = null): ?string
    {
        if ($this->label === null) {
            return null;
        }

        return $this->translator ? $this->translator->trans($this->label, [], null, $locale) : $this->label;
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
