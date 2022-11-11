<?php

namespace Quatrevieux\Form\Instantiator;

use Closure;
use Quatrevieux\Form\Instantiator\Generator\InstantiatorGenerator;

/**
 * Implentation of InstantiatorFactoryInterface using generated instantiator instead of runtime one
 */
final class GeneratedInstantiatorFactory implements InstantiatorFactoryInterface
{
    /**
     * Resolve instatiator class file path using instantiator class name as parameter
     *
     * @var Closure(string):string
     */
    private readonly Closure $savePathResolver;

    /**
     * Resolve instantiator class name using DTO class name as parameter
     *
     * @var Closure(string):string
     */
    private readonly Closure $classNameGenerator;

    /**
     * Fallback instantiator factory
     *
     * @var InstantiatorFactoryInterface
     */
    private readonly InstantiatorFactoryInterface $factory;

    /**
     * Code generator
     *
     * @var InstantiatorGenerator
     */
    private readonly InstantiatorGenerator $generator;

    /**
     * @param (Closure(string):string)|null $savePathResolver Resolve instatiator class file path using instantiator class name as parameter. By default, save into `sys_get_temp_dir()`
     * @param (Closure(string):string)|null $classNameGenerator Resolve instantiator class name using DTO class name as parameter. By default, replace namespace seprator by "_", and add "Instantiator" suffix
     * @param InstantiatorFactoryInterface|null $factory Fallback instantiator factory.
     * @param InstantiatorGenerator|null $generator Code generator instance.
     */
    public function __construct(?Closure $savePathResolver = null, ?Closure $classNameGenerator = null, ?InstantiatorFactoryInterface $factory = null, ?InstantiatorGenerator $generator = null)
    {
        // @todo factoriser les closures par défaut
        $this->savePathResolver = $savePathResolver ?? fn (string $className) => sys_get_temp_dir() . DIRECTORY_SEPARATOR . str_replace('\\', '_', $className) . '.php';
        $this->classNameGenerator = $classNameGenerator ?? fn (string $dataClassName) => str_replace('\\', '_', $dataClassName) . 'Instantiator';
        $this->factory = $factory ?? new RuntimeInstantiatorFactory();
        $this->generator = $generator ?? new InstantiatorGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): InstantiatorInterface
    {
        $className = $this->resolveClassName($dataClass);

        if ($instantiator = $this->instantiate($className)) {
            return $instantiator;
        }

        $fileName = ($this->savePathResolver)($className);

        if (is_file($fileName)) {
            require_once $fileName;

            if ($instantiator = $this->instantiate($className)) {
                return $instantiator;
            }
        }

        $instantiator = $this->factory->create($dataClass);
        $code = $this->generator->generate($instantiator, $this);

        if ($code) {
            if (!is_dir(dirname($fileName))) {
                mkdir(dirname($fileName), 0777, true);
            }

            file_put_contents($fileName, $code);
        }

        return $instantiator;
    }

    /**
     * Resolve generated instantiator class name
     *
     * @param class-string $dataClassName DTO class name to handle
     * @return class-string<InstantiatorInterface<T>>
     *
     * @template T as object
     */
    public function resolveClassName(string $dataClassName): string
    {
        return ($this->classNameGenerator)($dataClassName);
    }

    private function instantiate(string $instantiatorClass): ?InstantiatorInterface
    {
        if (class_exists($instantiatorClass, false) && is_subclass_of($instantiatorClass, InstantiatorInterface::class)) {
            return new $instantiatorClass();
        }

        return null;
    }
}
