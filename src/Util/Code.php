<?php

namespace Quatrevieux\Form\Util;

use ReflectionClass;

use stdClass;
use UnitEnum;
use function get_class;
use function implode;
use function is_string;
use function md5;
use function var_export;

/**
 * Code generator utility class
 */
final class Code
{
    /**
     * Generate a variable name
     * The generated var name will be unique for the given expression and hint
     *
     * @param string $expression Expression that should be stored into the given variable
     * @param string $hint Hint for specify variable usage
     *
     * @return string
     */
    public static function varName(string $expression, string $hint = 'tmp'): string
    {
        return '$__' . $hint . '_' . md5($expression);
    }

    /**
     * Get the PHP expression of the given value
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function value(mixed $value): string
    {
        return match (true) {
            // Replace LF by PHP_EOL constant to ensure that the generated string will be written on a single line
            is_string($value) => str_replace(PHP_EOL, '\' . PHP_EOL . \'', var_export($value, true)),
            is_object($value) => self::dumpObject($value),
            is_array($value) => self::dumpArray($value),
            default => var_export($value, true),
        };
    }

    /**
     * Generate the `new XXX()` PHP expression for construct given object
     *
     * Note: promoted properties will be used for instantiate the object, so the constructor must use it to works
     *
     * @param object $o
     *
     * @return string
     */
    public static function newExpression(object $o): string
    {
        $reflection = new ReflectionClass($o);

        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPromoted()) {
                $properties[] = $property->name . ': ' . self::value($property->getValue($o));
            }
        }

        return 'new \\' . get_class($o) . '(' . implode(', ', $properties) . ')';
    }

    /**
     * Generate PHP string expression which perform an inline version of `strtr()` function, using PHP expression as replacement values
     *
     * Example:
     * `Code::inlineStrtr('Hello {{ name }} !', ['{{ name }}' => '$name'])` will generate `'Hello ' . $name . ' !'`
     *
     * @param string $string String to replace
     * @param array<string, string> $replacementPair Replacement pairs expressions. The key is the string to replace, the value is the PHP expression of the replacement value.
     *
     * @return string PHP expression
     */
    public static function inlineStrtr(string $string, array $replacementPair): string
    {
        $expression = self::value($string);

        foreach ($replacementPair as $search => $replace) {
            $expression = str_replace($search, "' . {$replace} . '", $expression);
        }

        // Optimise the generated expression by removing the concatenation of empty strings
        $expression = str_replace(' . \'\'', '', $expression);

        return $expression;
    }

    /**
     * Generate the PHP expression of the given object
     *
     * Handle the following cases:
     * - enum: generate `Enum::VALUE` expression
     * - stdClass: generate properties as array and then cast it to object : `(object) ['prop' => 'value']`
     * - other: generate the constructor call using promoted properties
     *
     * @param object $value
     * @return string
     */
    private static function dumpObject(object $value): string
    {
        if ($value instanceof stdClass) {
            return '(object) ' . self::value((array) $value);
        }

        if ($value instanceof UnitEnum) {
            return '\\' . get_class($value) . '::' . $value->name;
        }

        return self::newExpression($value);
    }

    /**
     * Dump PHP expression of the given array
     * Handle list and associative array
     *
     * @param mixed[] $value
     * @return string
     */
    private static function dumpArray(array $value): string
    {
        if (array_is_list($value)) {
            return '[' . implode(', ', array_map(self::value(...), $value)) . ']';
        }

        $items = [];

        foreach ($value as $key => $item) {
            $items[] = self::value($key) . ' => ' . self::value($item);
        };

        return '[' . implode(', ', $items) . ']';
    }
}
