<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\Validator\FieldError;

/**
 * Store result of transformation process from HTTP to PHP values
 *
 * @see FormTransformerInterface::transformFromHttp()
 */
final class TransformationResult
{
    public function __construct(
        /**
         * Transformed properties values
         *
         * @var array<string, mixed>
         */
        public readonly array $values,

        /**
         * Transformation errors, indexed by field name
         *
         * @var array<string, FieldError|mixed[]>
         */
        public readonly array $errors,
    ) {
    }
}
