<?php

namespace Quatrevieux\Form\Transformer;

use Closure;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;

/**
 * Implementation of FormTransformerFactoryInterface using generated transformer instead of runtime one
 */
final class GeneratedFormTransformerFactory implements FormTransformerFactoryInterface
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
     * @var FormTransformerFactoryInterface
     */
    private readonly FormTransformerFactoryInterface $factory;

    /**
     * Code generator
     *
     * @var FormTransformerGenerator
     */
    private readonly FormTransformerGenerator $generator;
    private FieldTransformerRegistryInterface $registry;

    /**
     * @param (Closure(string):string)|null $savePathResolver Resolve instatiator class file path using instantiator class name as parameter. By default, save into `sys_get_temp_dir()`
     * @param (Closure(string):string)|null $classNameGenerator Resolve instantiator class name using DTO class name as parameter. By default, replace namespace seprator by "_", and add "Instantiator" suffix
     * @param FormTransformerFactoryInterface|null $factory Fallback instantiator factory.
     * @param FormTransformerGenerator|null $generator Code generator instance.
     */
    public function __construct(FieldTransformerRegistryInterface $registry, ?FormTransformerFactoryInterface $factory = null, ?FormTransformerGenerator $generator = null, ?Closure $savePathResolver = null, ?Closure $classNameGenerator = null)
    {
        // @todo factoriser les closures par défaut
        $this->savePathResolver = $savePathResolver ?? fn (string $className) => sys_get_temp_dir() . DIRECTORY_SEPARATOR . str_replace('\\', '_', $className) . '.php';
        $this->classNameGenerator = $classNameGenerator ?? fn (string $dataClassName) => str_replace('\\', '_', $dataClassName) . 'Transformer';
        $this->factory = $factory ?? new RuntimeFormTransformerFactory($registry);
        $this->generator = $generator ?? new FormTransformerGenerator();
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): FormTransformerInterface
    {
        $className = $this->resolveClassName($dataClass);

        if ($transformer = $this->instantiate($className)) {
            return $transformer;
        }

        $fileName = ($this->savePathResolver)($className);

        if (is_file($fileName)) {
            require_once $fileName;

            if ($transformer = $this->instantiate($className)) {
                return $transformer;
            }
        }

        $transformer = $this->factory->create($dataClass);
        $code = $this->generator->generate($className, $transformer);

        if ($code) {
            if (!is_dir(dirname($fileName))) {
                mkdir(dirname($fileName), 0777, true);
            }

            file_put_contents($fileName, $code);
        }

        return $transformer;
    }

    /**
     * Resolve generated instantiator class name
     *
     * @param class-string $dataClassName DTO class name to handle
     * @return class-string<FormTransformerInterface>
     *
     * @template T as object
     */
    public function resolveClassName(string $dataClassName): string
    {
        return ($this->classNameGenerator)($dataClassName);
    }

    private function instantiate(string $transformerClass): ?FormTransformerInterface
    {
        if (class_exists($transformerClass, false) && is_subclass_of($transformerClass, FormTransformerInterface::class)) {
            return new $transformerClass($this->registry);
        }

        return null;
    }
}
