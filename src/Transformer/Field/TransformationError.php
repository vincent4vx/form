<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Transformer\TransformerException;

/**
 * Configure error handling of transformation error
 *
 * @see FieldTransformerInterface::transformFromHttp()
 * @see TransformationResult
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class TransformationError
{
    public const CODE = 'ec3b18d7-cb0a-5af9-b1cd-6f0b8fb00ffd';

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

        /**
         * Configure the error code to use when transformation error occur
         * This error should be a UUID
         *
         * @var string
         */
        public readonly string $code = self::CODE,

        /**
         * If true, transformation errors raised using {@see TransformerException} will be hidden,
         * and a generic error will be displayed instead.
         *
         * @var bool
         */
        public readonly bool $hideSubErrors = false,
    ) {
    }
}
