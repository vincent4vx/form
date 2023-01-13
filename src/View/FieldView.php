<?php

namespace Quatrevieux\Form\View;

use Quatrevieux\Form\Validator\FieldError;

/**
 * Structure of a single field view
 * Note: Unlike most form components, this class is mutable. So it can be configured from the view.
 */
final class FieldView
{
    public function __construct(
        /**
         * HTTP field name
         * This name may be different from the form field name in case of embedded forms (e.g. "user[username]" for a "username" field in a "user" form).
         */
        public readonly string $name,

        /**
         * Raw HTTP value
         */
        public mixed $value,

        /**
         * Field error, if any
         */
        public ?FieldError $error,

        /**
         * Input HTML attributes
         *
         * May contains HTML 5 validation attributes like "required" or "minlength".
         * In case of boolean value, it will be used as a flag (e.g. "required" => true will be rendered as "required").
         *
         * @var array<string, scalar>
         */
        public array $attributes = [],
    ) {
    }
}
