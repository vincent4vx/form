<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use InvalidArgumentException;

use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;

use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;

use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;

use Quatrevieux\Form\Util\Expr;
use Quatrevieux\Form\Validator\Constraint\ArrayShape;

use function json_decode;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;

/**
 * Parse JSON string to PHP value.
 *
 * Note: Transformation will fail if the JSON is invalid. Use {@see TransformationError} to change this behavior.
 *
 * Usage:
 * <code>
 * class MyRequest
 * {
 *     #[Json] // By default, JSON objects will be returned as associative arrays.
 *     public ?array $myArray;
 *
 *     #[Json(assoc: false, depth: 5)] // JSON objects will be returned as stdClass, and limit the depth to 5.
 *     public ?object $myObject;
 *
 *     #[Json(encodeOptions: JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)] // Change the display of the JSON.
 *     public mixed $pretty;
 * }
 *
 * @see json_encode() Used when transforming to HTTP.
 * @see json_decode() Used when transforming from HTTP.
 * @see ArrayShape Can be used to validate the JSON structure.
 *
 * @implements FieldTransformerGeneratorInterface<Json>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Json implements FieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        /**
         * If true, JSON objects will be returned as associative arrays instead of stdClass.
         */
        private readonly bool $assoc = true,

        /**
         * Limits the depth (i.e. recursions) of the decoded object.
         * This option is not used on encode (e.g. transform to HTTP).
         *
         * Note: The depth starts at 1, so any non-empty object or array will have a depth of at least 2.
         *
         * @var positive-int
         */
        private readonly int $depth = 512,

        /**
         * Flags passed to json_decode(), used when transforming from HTTP.
         *
         * Available flags:
         * - JSON_BIGINT_AS_STRING
         * - JSON_INVALID_UTF8_IGNORE
         * - JSON_INVALID_UTF8_SUBSTITUTE
         * - JSON_OBJECT_AS_ARRAY (no effect since assoc is defined)
         * - JSON_THROW_ON_ERROR (will only slightly change the error message)
         *
         * @var int Bitmask of JSON_* constants.
         */
        private readonly int $parseOptions = 0,

        /**
         * Flags passed to json_encode(), used when transforming to HTTP.
         *
         * Available flags:
         * - JSON_HEX_AMP
         * - JSON_HEX_APOS
         * - JSON_HEX_QUOT
         * - JSON_HEX_TAG
         * - JSON_UNESCAPED_SLASHES
         * - JSON_UNESCAPED_UNICODE
         * - JSON_FORCE_OBJECT
         * - JSON_THROW_ON_ERROR
         * - JSON_PRETTY_PRINT
         * - JSON_THROW_ON_ERROR
         *
         * @var int Bitmask of JSON_* constants.
         */
        private readonly int $encodeOptions = 0,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException('The value must be a string');
        }

        $decoded = json_decode((string) $value, $this->assoc, $this->depth, $this->parseOptions);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('The value is not a valid JSON : ' . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * {@inheritdoc}
     */
    public function transformToHttp(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        return (string) json_encode($value, $this->encodeOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function canThrowError(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Expr::varName($previousExpression);
        $decode = Call::json_decode(
            $varName,
            $transformer->assoc,
            $transformer->depth,
            $transformer->parseOptions,
        );

        return "({$varName} = {$previousExpression}) === null ? null : " .
            "(!is_scalar({$varName}) ? throw new \InvalidArgumentException('The value must be a string') : " .
            "(!({$varName} = {$decode}) && json_last_error() !== JSON_ERROR_NONE ? throw new \InvalidArgumentException('The value is not a valid JSON : ' . json_last_error_msg()) : " .
            "{$varName}))"
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Expr::varName($previousExpression);
        $encode = Call::json_encode($varName, $transformer->encodeOptions);

        return "({$varName} = {$previousExpression}) === null ? null : {$encode}";
    }
}
