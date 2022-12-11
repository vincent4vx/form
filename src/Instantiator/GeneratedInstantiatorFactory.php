<?php

namespace Quatrevieux\Form\Instantiator;

use Closure;
use Quatrevieux\Form\Instantiator\Generator\InstantiatorGenerator;
use Quatrevieux\Form\Util\AbstractGeneratedFactory;
use Quatrevieux\Form\Util\Functions;

/**
 * Implentation of InstantiatorFactoryInterface using generated instantiator instead of runtime one
 *
 * @extends AbstractGeneratedFactory<InstantiatorInterface>
 */
final class GeneratedInstantiatorFactory extends AbstractGeneratedFactory implements InstantiatorFactoryInterface
{
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
     * @param (Closure(string):string)|null $classNameResolver Resolve instantiator class name using DTO class name as parameter. By default, replace namespace seprator by "_", and add "Instantiator" suffix
     * @param InstantiatorFactoryInterface|null $factory Fallback instantiator factory.
     * @param InstantiatorGenerator|null $generator Code generator instance.
     */
    public function __construct(?Closure $savePathResolver = null, ?Closure $classNameResolver = null, ?InstantiatorFactoryInterface $factory = null, ?InstantiatorGenerator $generator = null)
    {
        parent::__construct(
            $savePathResolver ?? Functions::savePathResolver(),
            $classNameResolver ?? Functions::classNameResolver('Instantiator'),
            InstantiatorInterface::class
        );

        $this->factory = $factory ?? new RuntimeInstantiatorFactory();
        $this->generator = $generator ?? new InstantiatorGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): InstantiatorInterface
    {
        return $this->createOrGenerate($dataClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function callConstructor(string $generatedClass): InstantiatorInterface
    {
        return new $generatedClass();
    }

    /**
     * {@inheritdoc}
     */
    protected function createRuntime(string $dataClass): InstantiatorInterface
    {
        return $this->factory->create($dataClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function generate(string $generatedClassName, object $runtime): ?string
    {
        return $this->generator->generate($generatedClassName, $runtime);
    }
}
