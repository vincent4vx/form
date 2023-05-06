<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;

/**
 * Implementation of RFC-4180 CSV format
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     // Will transform "foo,bar,baz" to ["foo", "bar", "baz"]
 *     #[Csv]
 *     public array $foo;
 *
 *     // You can specify separator
 *     #[Csv(separator: ';')]
 *     public array $bar;
 *
 *     // You can use ArrayCast to cast values
 *     #[Csv, ArrayCast(CastType::INT)]
 *     public array $baz;
 * }
 * </code>
 *
 * @see https://www.rfc-editor.org/rfc/rfc4180
 *
 * @implements FieldTransformerGeneratorInterface<self>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Csv implements FieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        /**
         * Separator character
         * It must be a single character
         */
        private readonly string $separator = ',',

        /**
         * Enclosure character
         * It must be a single character, or an empty string
         *
         * This character is used to enclose fields containing special characters
         */
        private readonly string $enclosure = '',
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(mixed $value): mixed
    {
        if (!is_string($value)) {
            return null;
        }

        return str_getcsv($value, $this->separator, $this->enclosure, '');
    }

    /**
     * {@inheritdoc}
     */
    public function transformToHttp(mixed $value): mixed
    {
        if (!is_array($value)) {
            return null;
        }

        if (!$this->enclosure) {
            return implode($this->separator, $value);
        }

        return self::toCsv($value, $this->separator, $this->enclosure);
    }

    /**
     * {@inheritdoc}
     */
    public function canThrowError(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        return Code::expr($previousExpression)->storeAndFormat(
            "(is_string({}) ? str_getcsv({}, {separator}, {enclosure}, '') : null)",
            separator: $transformer->separator,
            enclosure: $transformer->enclosure,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $expressionVarName = Expr::varName($previousExpression);

        if ($transformer->enclosure) {
            $expression = Call::static(self::class)->toCsv($expressionVarName, $transformer->separator, $transformer->enclosure);
        } else {
            $expression = Call::implode($transformer->separator, $expressionVarName);
        }

        return "(is_array($expressionVarName = $previousExpression) ? $expression : null)";
    }

    /**
     * Create CSV with enclosure from array
     *
     * @param scalar[] $fields
     * @param string $separator
     * @param string $enclosure
     *
     * @internal Used by generated transformer
     */
    public static function toCsv(array $fields, string $separator, string $enclosure): string
    {
        $csv = '';

        foreach ($fields as $item) {
            if ($csv) {
                $csv .= $separator;
            }

            if (is_string($item)) {
                $shouldBeEnclosed = str_contains($item, PHP_EOL) || str_contains($item, $separator) || str_contains($item, $enclosure);
                $item = str_replace($enclosure, $enclosure . $enclosure, $item);
            } else {
                $shouldBeEnclosed = false;
            }

            if ($shouldBeEnclosed) {
                $csv .= $enclosure . $item . $enclosure;
            } else {
                $csv .= $item;
            }
        }

        return $csv;
    }
}
