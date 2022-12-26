<?php

namespace Quatrevieux\Form\Validator;

final class FieldError
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
    ) {
    }

    /**
     * Get the translated error message
     * All placeholders will be replaced by their value
     */
    public function __toString(): string
    {
        if (!$parameters = $this->parameters) {
            return $this->message;
        }

        $replacements = [];

        foreach ($parameters as $key => $value) {
            $replacements["{{ {$key} }}"] = $value;
        }

        return strtr($this->message, $replacements);
    }
}
