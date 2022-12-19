<?php

namespace Quatrevieux\Form\Util;

use ReflectionClass;

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
     * @todo handle object expressions ?
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function value(mixed $value): string
    {
        $transformed = var_export($value, true);

        // Replace LF by PHP_EOL constant to ensure that the generated string will be written on a single line
        if (is_string($value)) {
            $transformed = str_replace(PHP_EOL, '\' . PHP_EOL . \'', $transformed);
        }

        return $transformed;
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
}
