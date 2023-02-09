<?php

namespace Quatrevieux\Form\DataMapper;

use Closure;
use Quatrevieux\Form\DataMapper\Generator\InstantiatorGenerator;
use Quatrevieux\Form\Util\AbstractGeneratedFactory;
use Quatrevieux\Form\Util\Functions;

/**
 * Implentation of InstantiatorFactoryInterface using generated instantiator instead of runtime one
 *
 * @extends AbstractGeneratedFactory<DataMapperInterface>
 */
final class GeneratedInstantiatorFactory extends AbstractGeneratedFactory implements InstantiatorFactoryInterface
{
    /**
     * Fallback instantiator factory
     * Will be lazily instantiated to {@see RuntimeDataMapperFactory} if not provided in constructor
     *
     * @var InstantiatorFactoryInterface
     */
    private readonly InstantiatorFactoryInterface $factory;

    /**
     * Code generator
     * Will be lazily instantiated if not provided in constructor
     *
     * @var InstantiatorGenerator
     */
    private readonly InstantiatorGenerator $generator;

    /**
     * @param InstantiatorFactoryInterface|null $factory Fallback instantiator factory. If not provided, will be lazily instantiated to {@see RuntimeDataMapperFactory}.
     * @param InstantiatorGenerator|null $generator Code generator instance. If not provided, will be lazily instantiated.
     * @param (Closure(string):string)|null $savePathResolver Resolve instatiator class file path using instantiator class name as parameter. By default, save into `sys_get_temp_dir()`
     * @param (Closure(string):string)|null $classNameResolver Resolve instantiator class name using DTO class name as parameter. By default, replace namespace seprator by "_", and add "Instantiator" suffix
     */
    public function __construct(?InstantiatorFactoryInterface $factory = null, ?InstantiatorGenerator $generator = null, ?Closure $savePathResolver = null, ?Closure $classNameResolver = null)
    {
        parent::__construct(
            $savePathResolver ?? Functions::savePathResolver(),
            $classNameResolver ?? Functions::classNameResolver('Instantiator'),
            DataMapperInterface::class
        );

        if ($factory) {
            $this->factory = $factory;
        }

        if ($generator) {
            $this->generator = $generator;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): DataMapperInterface
    {
        return $this->createOrGenerate($dataClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function callConstructor(string $generatedClass): DataMapperInterface
    {
        return new $generatedClass();
    }

    /**
     * {@inheritdoc}
     */
    protected function createRuntime(string $dataClass): DataMapperInterface
    {
        // @phpstan-ignore-next-line
        $factory = $this->factory ??= new RuntimeDataMapperFactory();
        return $factory->create($dataClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function generate(string $generatedClassName, object $runtime): ?string
    {
        // @phpstan-ignore-next-line
        $generator = $this->generator ??= new InstantiatorGenerator();
        return $generator->generate($generatedClassName, $runtime);
    }
}
