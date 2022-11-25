<?php

namespace Quatrevieux\Form\Validator;

use Closure;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

/**
 * Implentation of InstantiatorFactoryInterface using generated instantiator instead of runtime one
 */
final class GeneratedValidatorFactory implements ValidatorFactoryInterface
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
     * @var ValidatorFactoryInterface
     */
    private readonly ValidatorFactoryInterface $factory;

    /**
     * Code generator
     *
     * @var ValidatorGenerator
     */
    private readonly ValidatorGenerator $generator;
    private readonly ConstraintValidatorRegistryInterface $validatorRegistry;

    /**
     * @param ValidatorFactoryInterface $factory Fallback instantiator factory.
     * @param ValidatorGenerator $generator Code generator instance.
     * @param (Closure(string):string)|null $savePathResolver Resolve instatiator class file path using instantiator class name as parameter. By default, save into `sys_get_temp_dir()`
     * @param (Closure(string):string)|null $classNameGenerator Resolve instantiator class name using DTO class name as parameter. By default, replace namespace seprator by "_", and add "Instantiator" suffix
     */
    public function __construct(ValidatorFactoryInterface $factory, ValidatorGenerator $generator, ConstraintValidatorRegistryInterface $validatorRegistry, ?Closure $savePathResolver = null, ?Closure $classNameGenerator = null)
    {
        $this->factory = $factory;
        $this->generator = $generator;
        $this->validatorRegistry = $validatorRegistry;

        $this->savePathResolver = $savePathResolver ?? fn (string $className) => sys_get_temp_dir() . DIRECTORY_SEPARATOR . str_replace('\\', '_', $className) . '.php';
        $this->classNameGenerator = $classNameGenerator ?? fn (string $dataClassName) => str_replace('\\', '_', $dataClassName) . 'Validator';
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): ValidatorInterface
    {
        $className = $this->resolveClassName($dataClass);

        if ($validator = $this->instantiate($className)) {
            return $validator;
        }

        $fileName = ($this->savePathResolver)($className);

        if (is_file($fileName)) {
            require_once $fileName;

            if ($validator = $this->instantiate($className)) {
                return $validator;
            }
        }

        $validator = $this->factory->create($dataClass);
        $code = $this->generator->generate($className, $validator);

        if ($code) {
            if (!is_dir(dirname($fileName))) {
                mkdir(dirname($fileName), 0777, true);
            }

            file_put_contents($fileName, $code);
        }

        return $validator;
    }

    /**
     * Resolve generated instantiator class name
     *
     * @param class-string $dataClassName DTO class name to handle
     * @return class-string<ValidatorInterface<T>>
     *
     * @template T as object
     */
    public function resolveClassName(string $dataClassName): string
    {
        return ($this->classNameGenerator)($dataClassName);
    }

    private function instantiate(string $instantiatorClass): ?ValidatorInterface
    {
        if (class_exists($instantiatorClass, false) && is_subclass_of($instantiatorClass, ValidatorInterface::class)) {
            return new $instantiatorClass($this->validatorRegistry);
        }

        return null;
    }
}
