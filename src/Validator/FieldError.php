<?php

namespace Quatrevieux\Form\Validator;

use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;

/**
 * Store error for a field
 * This class can be serialized to JSON
 */
final class FieldError implements \JsonSerializable
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
    ) {
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
