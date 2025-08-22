<?php

namespace Quatrevieux\Form\Util;

use DateTimeZone;
use Quatrevieux\Form\Validator\FieldError;
use ReflectionClass;
use stdClass;
use UnitEnum;

use function array_is_list;
use function array_map;
use function class_exists;
use function get_class;
use function implode;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function md5;
use function preg_match;
use function str_replace;
use function str_starts_with;
use function var_export;

/**
 * Code generator utility class
 */
final class Code
{
    /**
     * Map a class name to a custom instantiation expression generator
     *
     * @var array<class-string, callable(object): string>
     */
    private static ?array $customObjectExpressions = null;

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
            $value instanceof PhpExpressionInterface => (string) $value,
            // Replace LF by PHP_EOL constant to ensure that the generated string will be written on a single line
            is_string($value) => str_replace(PHP_EOL, '\' . PHP_EOL . \'', var_export($value, true)),
            is_object($value) => self::dumpObject($value),
            is_array($value) => self::dumpArray($value),
            $value === null => 'null',
            default => var_export($value, true),
        };
    }

    /**
     * Generate the `new XXX()` PHP expression for construct given object
     * Default values of promoted properties will be ignored
     *
     * Note: promoted properties will be used for instantiate the object, so the constructor must use it to works
     *
     * @param object $o
     *
     * @return string
     *
     * @see Code::new() for a more generic way to instantiate an object, by passing arguments manually
     */
    public static function instantiate(object $o): string
    {
        $reflection = new ReflectionClass($o);

        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPromoted()) {
                $value = $property->getValue($o);

                if ($value !== $property->getDefaultValue()) {
                    $properties[$property->name] = $property->getValue($o);
                }
            }
        }

        return self::new(get_class($o), $properties);
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
     * Generate a function or method call expression
     *
     * Example:
     * - `Code::call('Foo::bar', ['arg1', 'arg2'])` will generate `Foo::bar('arg1', 'arg2')`
     * - `Code::call('substr', ['azerty', 'length' => 3])` will generate `substr('azerty', length: 3)`
     *
     * @param string $function Function name or method call expression
     * @param array<string|int, mixed> $arguments Arguments to pass to the function.
     *     All arguments will be converted to PHP expression using `Code::value()`.
     *     If an associative array is given, the keys will be used as named argument.
     *     Use {@see Code::raw()} to ignore the conversion.
     *
     * @return string
     *
     * @see Code::callStatic() For generate a static method call expression
     * @see Code::callMethod() For generate a method call expression
     * @see Code::new() For generate a `new` expression
     */
    public static function call(string $function, array $arguments = []): string
    {
        $indexedArguments = [];
        $namedArguments = [];

        foreach ($arguments as $key => $value) {
            if (is_int($key)) {
                $indexedArguments[] = self::value($value);
            } else {
                $namedArguments[] = $key . ': ' . self::value($value);
            }
        }

        return $function . '(' . implode(', ', [...$indexedArguments, ...$namedArguments]) . ')';
    }

    /**
     * Generate a static method call expression
     *
     * Example:
     * - `Code::staticCall('Foo', 'bar', ['arg1', 'arg2'])` will generate `Foo::bar('arg1', 'arg2')`
     *
     * @param string $class Class name. If a FQCN is given, it will be prefixed with a backslash to ensure that the class will be resolved from the global namespace.
     * @param string $method The method name
     * @param array<string|int, mixed> $arguments Arguments to pass to the function.
     *     All arguments will be converted to PHP expression using `Code::value()`.
     *     If an associative array is given, the keys will be used as named argument.
     *     Use {@see Code::raw()} to ignore the conversion.
     *
     * @return string
     */
    public static function callStatic(string $class, string $method, array $arguments = []): string
    {
        // Prefix the class name with a backslash to ensure that the class will be resolved from the global namespace
        if ($class[0] !== '\\' && class_exists($class)) {
            $class = '\\' . $class;
        }

        return self::call($class . '::' . $method, $arguments);
    }

    /**
     * Generate an object method call expression
     *
     * Example:
     * - `Code::callMethod('$foo', 'bar', ['arg1', 'arg2'])` will generate `$foo->bar('arg1', 'arg2')`
     *
     * @param string $object The object accessor expression
     * @param string $method The method name
     * @param array<string|int, mixed> $arguments Arguments to pass to the function.
     *     All arguments will be converted to PHP expression using `Code::value()`.
     *     If an associative array is given, the keys will be used as named argument.
     *     Use {@see Code::raw()} to ignore the conversion.
     *
     * @return string
     */
    public static function callMethod(string $object, string $method, array $arguments = []): string
    {
        if (str_starts_with($object, 'new ')) {
            $object = '(' . $object . ')';
        }

        return self::call($object . '->' . $method, $arguments);
    }

    /**
     * Generate a call to class constructor
     *
     * @param string $class Class name. Can be a fully qualified class name.
     * @param array<string|int, mixed> $arguments Arguments to pass to the constructor.
     *     All arguments will be converted to PHP expression using `Code::value()`.
     *     If an associative array is given, the keys will be used as named argument.
     *     Use {@see Code::raw()} to ignore the conversion.
     *
     * @return string The `new XXX()` PHP expression
     *
     * @see Code::instantiate() To generate a `new XXX()` expression for instantiate an existing object
     */
    public static function new(string $class, array $arguments = []): string
    {
        if ($class[0] !== '\\' && class_exists($class)) {
            $class = '\\' . $class;
        }

        return self::call('new ' . $class, $arguments);
    }

    /**
     * Generate an expression that check the instance of the given expression, and return null if the type does not match
     *
     * Example:
     * `Code::instanceOfOrNull('$foo["bar"] ?? null', Foo::class)`
     * will generate a code like `($tmp = $foo["bar"] ?? null) instanceof Foo ? $foo : null`
     *
     * @param string $expression PHP value expression
     * @param class-string $className
     *
     * @return string
     */
    public static function instanceOfOrNull(string $expression, string $className): string
    {
        return self::instanceOfOr($expression, $className, Code::raw('null'));
    }

    /**
     * Generate an expression that check the instance of the given expression,
     * and return a fallback value if the type does not match
     *
     * Example:
     * - `Code::instanceOfOr('$foo["bar"] ?? null', Foo::class, null)` * will generate a code like `($tmp = $foo["bar"] ?? null) instanceof Foo ? $foo : null`
     * - `Code::instanceOfOr('$foo["bar"] ?? null', Foo::class, new NullFoo())` * will generate a code like `($tmp = $foo["bar"] ?? null) instanceof Foo ? $foo : new NullFoo()`
     *
     * @param string $expression PHP value expression
     * @param class-string $className
     * @param mixed $fallback Fallback value to use if the type does not match
     *
     * @return string
     */
    public static function instanceOfOr(string $expression, string $className, mixed $fallback): string
    {
        $fallback = self::value($fallback);

        if ($expression === 'null') {
            return $fallback;
        }

        // Check if the expression is a variable
        if (preg_match('/^\$[a-z][a-z0-9_]*$/i', $expression)) {
            return "{$expression} instanceof \\{$className} ? {$expression} : {$fallback}";
        }

        // If the expression is more complex, we need to generate a temporary variable
        $varName = self::varName($expression);

        return "({$varName} = {$expression}) instanceof \\{$className} ? {$varName} : {$fallback}";
    }

    /**
     * Generate an expression that check is the given expression is an array,
     * and return a fallback value if the type does not match
     *
     * Example:
     * - `Code::instanceOfOr('$foo["bar"] ?? null', Foo::class, null)` * will generate a code like `($tmp = $foo["bar"] ?? null) instanceof Foo ? $foo : null`
     * - `Code::instanceOfOr('$foo["bar"] ?? null', Foo::class, new NullFoo())` * will generate a code like `($tmp = $foo["bar"] ?? null) instanceof Foo ? $foo : new NullFoo()`
     *
     * @param string $expression PHP value expression
     * @param mixed[]|PhpExpressionInterface|null $fallback Fallback value to use if the value is not an array
     *
     * @return string
     */
    public static function isArrayOr(string $expression, array|PhpExpressionInterface|null $fallback): string
    {
        $fallback = self::value($fallback);

        if ($expression === 'null') {
            return $fallback;
        }

        $varName = Code::varName($expression);

        return "(is_array({$varName} = {$expression}) ? {$varName} : {$fallback})";
    }

    /**
     * Wrap a PHP expression into to ensure that it will not be converted to a string expression by `Code::value()`
     *
     * @param string $code PHP code to wrap
     *
     * @return PhpExpressionInterface
     */
    public static function raw(string $code): PhpExpressionInterface
    {
        return new class ($code) implements PhpExpressionInterface {
            public function __construct(private readonly string $code) {}

            public function __toString(): string
            {
                return $this->code;
            }
        };
    }

    /**
     * Create an expression builder for the given PHP expression
     *
     * @param string $expression PHP code to wrap
     *
     * @return Expr
     */
    public static function expr(string $expression): PhpExpressionInterface
    {
        return new Expr($expression);
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
        // @phpstan-ignore-next-line
        $custom = self::$customObjectExpressions ??= [
            stdClass::class => fn(stdClass $value) => '(object) ' . self::dumpArray((array) $value),
            UnitEnum::class => fn(UnitEnum $value) => '\\' . get_class($value) . '::' . $value->name,
            DateTimeZone::class => fn(DateTimeZone $value) => self::new(DateTimeZone::class, [$value->getName()]),
            FieldError::class => fn(FieldError $value) => self::new(FieldError::class, [$value->message, $value->parameters, $value->code]),
        ];

        foreach ($custom as $class => $callback) {
            if ($value instanceof $class) {
                return $callback($value);
            }
        }

        return self::instantiate($value);
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
