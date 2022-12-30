<?php

namespace Quatrevieux\Form\Validator;

use JsonSerializable;
use Quatrevieux\Form\DummyTranslator;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Store error for a field
 * This class can be serialized to JSON
 */
final class FieldError implements JsonSerializable, TranslatableInterface
{
    public function __construct(
        /**
         * Error message
         *
         * Placeholders can be used, with the following format: `{{ placeholder }}`
         * and values can be passed in $parameters constructor argument
         */
        public readonly string $message,

        /**
         * Parameters to replace placeholders in $message
         * Takes as key the placeholder name and as value the value to replace
         *
         * @var array<string, mixed>
         */
        public readonly array $parameters = [],

        /**
         * Error code used to identify the error ignoring localized or parameterized messages
         */
        public readonly string $code = ConstraintInterface::CODE,

        /**
         * Translator to use to translate the message and replace placeholders
         * If null, a DummyTranslator will be used
         */
        private ?TranslatorInterface $translator = null,
    ) {
    }

    /**
     * Set the translator instance
     * This method is called by the validator
     *
     * @return self A new instance with the translator set
     *
     * @internal
     */
    public function withTranslator(?TranslatorInterface $translator): self
    {
        if ($this->translator === $translator) {
            return $this;
        }

        $error = clone $this;
        $error->translator = $translator;

        return $error;
    }

    /**
     * {@inheritdoc}
     *
     * @return array{code: string, message: string, parameters?: array<string, mixed>}
     */
    public function jsonSerialize(): array
    {
        $json = [
            'code' => $this->code,
            'message' => (string) $this,
        ];

        if ($this->parameters) {
            $json['parameters'] = $this->parameters;
        }

        return $json;
    }

    /**
     * Translates the error message to the given locale
     */
    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        // Normalize placeholders parameters
        if ($parameters = $this->parameters) {
            $normalized = [];

            foreach ($parameters as $key => $value) {
                $normalized['{{ ' . $key . ' }}'] = $value;
            }

            $parameters = $normalized;
        }

        return $translator->trans($this->message, $parameters, null, $locale);
    }

    /**
     * Get the translated error message
     * All placeholders will be replaced by their value
     *
     * @param string|null $locale Locale to translate the message to
     * @return string Translated error message
     */
    public function localizedMessage(?string $locale = null): string
    {
        return $this->trans($this->translator ?? DummyTranslator::instance(), $locale);
    }

    /**
     * Get the translated error message
     * All placeholders will be replaced by their value
     */
    public function __toString(): string
    {
        return $this->localizedMessage();
    }
}
