<?php

namespace Quatrevieux\Form\Util;

use InvalidArgumentException;

use function explode;
use function str_contains;
use function strtr;

/**
 * Fluent expression generator
 *
 * Usage:
 * - `Expr::this()->bar()` will generate `$this->bar()`
 * - `Expr::this()->foo->bar()` will generate `$this->foo->bar()`
 * - `(new Expr('function () {}'))(123, true)` will generate `(function () {})(123, true)`
 */
final class Expr implements PhpExpressionInterface
{
    public function __construct(
        private readonly string $expr,
    ) {
    }

    /**
     * Generate property access expression
     *
     * @param string $name Property name
     *
     * @return self The new expression
     */
    public function __get(string $name): self
    {
        return new self($this->expr . '->' . $name);
    }

    /**
     * Generate method call expression
     *
     * @param string $name Method name
     * @param mixed[] $arguments Arguments to pass to the function.
     *
     * @return self The new expression
     */
    public function __call(string $name, array $arguments): self
    {
        return new self(Code::callMethod($this->expr, $name, $arguments));
    }

    /**
     * Invoke the expression.
     * The expression must be a callable (e.g. a name or a closure)
     *
     * @param mixed ...$args Arguments to pass to the function.
     *
     * @return self The new expression
     */
    public function __invoke(...$args): self
    {
        return new self(Code::call('(' . $this->expr . ')', $args));
    }

    /**
     * Check if the expression is an array or return a default value
     * A temporary variable is used to avoid multiple evaluation of the expression.
     *
     * e.g. `is_array($tmp = expr()) ? $tmp : $default`
     *
     * @param mixed[]|PhpExpressionInterface|null $defaultValue The default value to return if the expression is not an array.
     *
     * @return self The new expression
     */
    public function isArrayOr(array|PhpExpressionInterface|null $defaultValue): self
    {
        return new self(Code::isArrayOr($this->expr, $defaultValue));
    }

    /**
     * Check if the expression is an instance of the given class or return a default value
     * A temporary variable is used to avoid multiple evaluation of the expression.
     *
     * e.g. `($tmp = expr() instanceof Foo) ? $tmp : new Foo()`
     *
     * @param class-string $className Expected class name
     * @param mixed $defaultValue The default value to return if the expression is not an array.
     *
     * @return self The new expression
     */
    public function isInstanceOfOr(string $className, mixed $defaultValue): self
    {
        return new self(Code::instanceOfOr($this->expr, $className, $defaultValue));
    }

    /**
     * Wrap an expression, following the given format
     *
     * The placeholder '{}' will be replaced by the current expression.
     * Other placeholders will be replaced by variadic arguments, using the argument index or name, wrapped around {} as placeholder.
     *
     * Example:
     * - `Expr::this()->var->format('{} instanceof Foo ? {} : new Foo({})')` will generate `$this->var instanceof Foo ? $this->var : new Foo($this->var)`
     * - `Code::expr('$foo')->format('is_array({}) ? ({obj})->perform({}) : {default}', obj: new Foo(), default: [])` will generate `is_array($foo) ? (new Foo())->perform($foo) : []`
     *
     * @param string $format Format to use. Must contain at least one placeholder '{}'.
     * @param mixed ...$values Values to use as replacement. Named arguments are supported.
     *
     * @return self The new expression
     *
     * @see Expr::storeAndFormat() When the expression must be stored in a temporary variable
     */
    public function format(string $format, ...$values): self
    {
        if (!str_contains($format, '{}')) {
            throw new InvalidArgumentException('Format must contain at least one placeholder "{}"');
        }

        $replacements = [
            '{}' => $this->expr,
        ];

        foreach ($values as $key => $value) {
            $replacements['{' . $key . '}'] = Code::value($value);
        }

        return new self(strtr($format, $replacements));
    }

    /**
     * Store the given expression into a temporary variable, and wrap it in an expression, following the given format
     *
     * Unlike `Expr::format()`, the expression is only evaluated once.
     *
     * The first placeholder '{}' will be replaced by assignment of the current expression to a temporary variable.
     * Following placeholders will be replaced by the temporary variable.
     *
     * Other placeholders will be replaced by variadic arguments, using the argument index or name, wrapped around {} as placeholder.
     *
     * > Note: Parentheses are added around the assignation if needed.
     *
     * Example:
     * `Code::expr('$foo->heavyCalculation()')->storeAndFormat('is_array({}) ? ({obj})->perform({}) : {default}', obj: new Foo(), default: [])` will generate `is_array($__tmp_xxx = $foo->heavyCalculation()) ? (new Foo())->perform($__tmp_xxx) : []`
     *
     * @param string $format Format to use. Must contain at least one placeholder '{}'.
     * @param mixed ...$values Values to use as replacement. Named arguments are supported.
     *
     * @return self The new expression
     */
    public function storeAndFormat(string $format, ...$values): self
    {
        $varName = self::varName($this->expr);
        $parts = explode('{}', $format, 2);

        if (!isset($parts[1])) {
            throw new InvalidArgumentException('Format must contain at least one placeholder "{}"');
        }

        $store = '{} = ' . $this->expr;

        // Wrap the expression in parentheses if needed
        if (($parts[0][0] ?? '') !== '(' || ($parts[1][0] ?? '') !== ')') {
            $store = '(' . $store . ')';
        }

        $expression = $parts[0] . $store . $parts[1];

        return $varName->format($expression, ...$values);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->expr;
    }

    /**
     * Wrap '$this' in an expression
     *
     * @return static
     */
    public static function this(): self
    {
        return new self('$this');
    }

    /**
     * Generate the constructor call of the given class, and wrap it in an expression
     *
     * @param string $className Class name. Can be a fully qualified class name.
     * @param list<mixed> $args Arguments to pass to the constructor.
     *     All arguments will be converted to PHP expression using `Code::value()`.
     *     If an associative array is given, the keys will be used as named argument.
     *     Use {@see Code::raw()} to ignore the conversion.
     *
     * @return static The `new XXX()` PHP expression
     *
     * @see Code::new() for more details
     */
    public static function new(string $className, array $args = []): self
    {
        return new self(Code::new($className, $args));
    }

    /**
     * Generate a variable name
     * The generated var name will be unique for the given expression and hint
     *
     * @param string $expression Expression that should be stored into the given variable
     * @param string $hint Hint for specify variable usage
     *
     * @return self
     *
     * @see Code::varName() for more details
     */
    public static function varName(string $expression, string $hint = 'tmp'): self
    {
        return new self(Code::varName($expression, $hint));
    }

    /**
     * Convert a PHP value to a PHP expression
     * This method is a shortcut for `Code::expr(Code::value($value))`
     *
     * @param mixed $value The value to convert. Use {@see Code::raw()} to ignore the conversion.
     *
     * @return self
     *
     * @see Code::value() for more details
     */
    public static function value(mixed $value): self
    {
        return new self(Code::value($value));
    }
}
