<?php

namespace Quatrevieux\Form\Util;

/**
 * Code generator utility class for generating function or method call
 *
 * Usage:
 * - `Call::foo('bar', 'baz')` will generate `foo('bar', 'baz')`
 * - `Call::static('Foo')->bar('baz')` will generate `Foo::bar('baz')`
 * - `Call::object('$foo')->bar('baz')` will generate `$foo->bar('baz')`
 */
final class Call
{
    private function __construct(
        private readonly string $object,
        private readonly bool $static,
    ) {
    }

    /**
     * Configure a static method call
     *
     * @param class-string|string $class The class name. If a FQCN is given, it will be prefixed with a backslash to ensure that the class will be resolved from the global namespace.
     * @return Call
     */
    public static function static(string $class): Call
    {
        return new self($class, true);
    }

    /**
     * Configure an object method call
     *
     * @param string $object The object accessor expression
     * @return Call
     */
    public static function object(string $object): Call
    {
        return new self($object, false);
    }

    /**
     * Generate a method call expression
     *
     * @param string $name Method name
     * @param mixed[] $arguments Arguments to pass to the function.
     *
     * @return string
     *
     * @see Code::callMethod()
     * @see Code::callStatic()
     */
    public function __call(string $name, array $arguments): string
    {
        return $this->static
            ? Code::callStatic($this->object, $name, $arguments)
            : Code::callMethod($this->object, $name, $arguments)
        ;
    }

    /**
     * Generate a function call expression
     *
     * @param string $name The function name
     * @param mixed[] $arguments Arguments to pass to the function
     *
     * @return string
     *
     * @see Code::call()
     */
    public static function __callStatic(string $name, array $arguments): string
    {
        return Code::call($name, $arguments);
    }
}
