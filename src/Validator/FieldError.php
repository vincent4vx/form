<?php

namespace Quatrevieux\Form\Validator;

class FieldError
{
    // @todo parameters
    public function __construct(
        public readonly string $message,
    ) {

    }

    public function __toString(): string
    {
        // @todo replace parameters
        return $this->message;
    }
}
