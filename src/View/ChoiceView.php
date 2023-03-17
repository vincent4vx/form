<?php

namespace Quatrevieux\Form\View;

final class ChoiceView
{
    public function __construct(
        /**
         * Choice value
         * The value is the HTTP value, not the model one.
         */
        public readonly mixed $value,

        /**
         * Choice label
         */
        public readonly ?string $label = null,

        /**
         * Is the choice selected ?
         */
        public readonly bool $selected = false,
    ) {
    }
}
