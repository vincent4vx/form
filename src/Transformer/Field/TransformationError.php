<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Transformer\TransformerException;

/**
 * Configure error handling of transformation error
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     // You can customize the error message, and code, just like validation errors
 *     #[TransformationError(message: 'This JSON is invalid', code: 'f59e2415-0b70-4177-9bc1-66ebbb65c75c'), Json]
 *     public string $json;
 *
 *     // Fail silently: the field will be set to null, and no error will be displayed
 *     #[TransformationError(ignore: true), Json]
 *     public ?string $foo;
 *
 *     // Keep the original value instead of setting it to null
 *     #[TransformationError(ignore: true, keepOriginalValue: true), Json]
 *     public mixed $bar;
 * }
 * </code>
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
