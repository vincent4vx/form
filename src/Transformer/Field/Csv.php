<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;

/**
 * Implementation of RFC-4180 CSV format
 *
 * @see https://www.rfc-editor.org/rfc/rfc4180
 *
 * @implements FieldTransformerGeneratorInterface<self>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Csv implements FieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        private readonly string $separator = ',',
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
        $expressionVarName = Code::varName($previousExpression);
        $separator = Code::value($transformer->separator);
        $enclosure = Code::value($transformer->enclosure);

        return "(is_string($expressionVarName = $previousExpression) ? str_getcsv($expressionVarName, $separator, $enclosure, '') : null)";
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $expressionVarName = Code::varName($previousExpression);

        if ($transformer->enclosure) {
            $expression = Call::static(self::class)->toCsv(Code::raw($expressionVarName), $transformer->separator, $transformer->enclosure);
        } else {
            $expression = Call::implode($transformer->separator, Code::raw($expressionVarName));
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
