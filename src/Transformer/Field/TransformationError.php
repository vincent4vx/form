<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use Quatrevieux\Form\Transformer\TransformationResult;

/**
 * Configure error handling of transformation error
 *
 * @see FieldTransformerInterface::transformFromHttp()
 * @see TransformationResult
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class TransformationError
{
    public function __construct(
        /**
         * Define a custom error message
         * If not set (i.e. null), use exception message as error message
         */
        public readonly ?string $message = null,

        /**
         * Do not mark the field as invalid when an error occur during transformation
         */
        public readonly bool $ignore = false,

        /**
         * Keep the untransformed value instead of setting it to null
         * Be careful, the value may cause a type error
         */
        public readonly bool $keepOriginalValue = false,
    ) {
    }
}
