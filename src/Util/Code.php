<?php

namespace Quatrevieux\Form\Util;

use ReflectionClass;

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
        return var_export($value, true);
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

        return 'new \\'.get_class($o).'(' . implode(', ', $properties) . ')';
    }
}
