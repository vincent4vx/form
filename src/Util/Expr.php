<?php

namespace Quatrevieux\Form\Util;

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
}
