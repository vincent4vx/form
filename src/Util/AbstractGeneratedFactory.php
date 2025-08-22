<?php

namespace Quatrevieux\Form\Util;

use Closure;

/**
 * Base type for the generated factory implementations
 *
 * @template T as object
 */
abstract class AbstractGeneratedFactory
{
    public function __construct(
        /**
         * Resolve class file path using class name as parameter
         *
         * @var Closure(string):string
         */
        private readonly Closure $savePathResolver,

        /**
         * Resolve generated class name using DTO class name as parameter
         *
         * @var Closure(class-string):string
         */
        private readonly Closure $classNameResolver,

        /**
         * Base type of the generated module
         *
         * @var class-string<T>
         */
        private readonly string $type,
    ) {}

    /**
     * Call constructor of the generated class to create a new instance
     *
     * @param class-string<T> $generatedClass Generated class name
     *
     * @return T
     */
    abstract protected function callConstructor(string $generatedClass): object;

    /**
     * Create the runtime instance
     *
     * @param class-string $dataClass DTO class name
     *
     * @return T
     */
    abstract protected function createRuntime(string $dataClass): object;

    /**
     * Generate the class code related to the given runtime instance
     *
     * @param string $generatedClassName Class name
     * @param T $runtime Runtime instance for metadata
     *
     * @return string|null Generated code, or null if cannot be generated
     */
    abstract protected function generate(string $generatedClassName, object $runtime): ?string;

    /**
     * Resolve generated class name
     *
     * @param class-string $dataClassName DTO class name to handle
     * @return string
     */
    final public function resolveClassName(string $dataClassName): string
    {
        return ($this->classNameResolver)($dataClassName);
    }

    /**
     * Create instance of the requested module for the given DTO class
     * If generated implementation do not exist, try to generate and save it
     *
     * @param class-string $dataClass DTO class name
     *
     * @return T Requested module instance
     */
    final protected function createOrGenerate(string $dataClass): object
    {
        $className = $this->resolveClassName($dataClass);

        if ($instance = $this->instantiate($className)) {
            return $instance;
        }

        $fileName = ($this->savePathResolver)($className);

        if (is_file($fileName)) {
            require_once $fileName;

            if ($instance = $this->instantiate($className)) {
                return $instance;
            }
        }

        $instance = $this->createRuntime($dataClass);
        $code = $this->generate($className, $instance);

        if ($code) {
            if (!is_dir(dirname($fileName))) {
                mkdir(dirname($fileName), 0777, true);
            }

            file_put_contents($fileName, $code);
            require_once $fileName;

            if ($generated = $this->instantiate($className)) {
                return $generated;
            }
        }

        return $instance;
    }

    /**
     * @param string $generatedClass
     * @return T|null
     */
    private function instantiate(string $generatedClass): ?object
    {
        if (class_exists($generatedClass, false) && is_subclass_of($generatedClass, $this->type)) {
            return $this->callConstructor($generatedClass);
        }

        return null;
    }
}
