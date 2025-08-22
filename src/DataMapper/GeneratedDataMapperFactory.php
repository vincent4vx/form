<?php

namespace Quatrevieux\Form\DataMapper;

use Closure;
use Quatrevieux\Form\DataMapper\Generator\DataMapperGenerator;
use Quatrevieux\Form\Util\AbstractGeneratedFactory;
use Quatrevieux\Form\Util\Functions;

/**
 * Create generated data mapper
 *
 * @extends AbstractGeneratedFactory<DataMapperInterface>
 */
final class GeneratedDataMapperFactory extends AbstractGeneratedFactory implements DataMapperFactoryInterface
{
    /**
     * Fallback data mapper factory
     * Will be lazily instantiated to {@see RuntimeDataMapperFactory} if not provided in constructor
     *
     * @var DataMapperFactoryInterface|null
     */
    private ?DataMapperFactoryInterface $factory = null;

    /**
     * Code generator
     * Will be lazily instantiated if not provided in constructor
     *
     * @var DataMapperGenerator|null
     */
    private ?DataMapperGenerator $generator = null;

    /**
     * @param DataMapperFactoryInterface|null $factory Fallback data mapper factory. If not provided, will be lazily instantiated to {@see RuntimeDataMapperFactory}.
     * @param DataMapperGenerator|null $generator Code generator instance. If not provided, will be lazily instantiated.
     * @param (Closure(string):string)|null $savePathResolver Resolve data mapper class file path using data mapper class name as parameter. By default, save into `sys_get_temp_dir()`
     * @param (Closure(string):string)|null $classNameResolver Resolve data mapper class name using DTO class name as parameter. By default, replace namespace seprator by "_", and add "DataMapper" suffix
     */
    public function __construct(?DataMapperFactoryInterface $factory = null, ?DataMapperGenerator $generator = null, ?Closure $savePathResolver = null, ?Closure $classNameResolver = null)
    {
        parent::__construct(
            $savePathResolver ?? Functions::savePathResolver(),
            $classNameResolver ?? Functions::classNameResolver('DataMapper'),
            DataMapperInterface::class,
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
        $factory = $this->factory ??= new RuntimeDataMapperFactory();
        return $factory->create($dataClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function generate(string $generatedClassName, object $runtime): ?string
    {
        $generator = $this->generator ??= new DataMapperGenerator();
        return $generator->generate($generatedClassName, $runtime);
    }
}
