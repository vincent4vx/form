<?php

namespace Quatrevieux\Form;

use Closure;
use Quatrevieux\Form\DataMapper\GeneratedDataMapperFactory;
use Quatrevieux\Form\DataMapper\DataMapperFactoryInterface;
use Quatrevieux\Form\DataMapper\RuntimeDataMapperFactory;
use Quatrevieux\Form\Transformer\FormTransformerFactoryInterface;
use Quatrevieux\Form\Transformer\GeneratedFormTransformerFactory;
use Quatrevieux\Form\Transformer\RuntimeFormTransformerFactory;
use Quatrevieux\Form\Validator\GeneratedValidatorFactory;
use Quatrevieux\Form\Validator\RuntimeValidatorFactory;
use Quatrevieux\Form\Validator\ValidatorFactoryInterface;
use Quatrevieux\Form\View\FormViewInstantiatorFactoryInterface;
use Quatrevieux\Form\View\GeneratedFormViewInstantiatorFactory;
use Quatrevieux\Form\View\RuntimeFormViewInstantiatorFactory;

use function get_class;

/**
 * Default implementation of FormFactoryInterface
 */
final class DefaultFormFactory implements FormFactoryInterface
{
    /**
     * @var array<class-string, FormInterface>
     * @psalm-var class-string-map<T, FormInterface<T>>
     */
    private array $cache = [];

    public function __construct(
        private readonly DataMapperFactoryInterface $dataMapperFactory,
        private readonly ValidatorFactoryInterface $validatorFactory,
        private readonly FormTransformerFactoryInterface $transformerFactory,
        private readonly ?FormViewInstantiatorFactoryInterface $formViewInstantiatorFactory = null,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): FormInterface
    {
        return $this->cache[$dataClass] ??= new Form(
            $this->transformerFactory->create($dataClass),
            $this->dataMapperFactory->create($dataClass),
            $this->validatorFactory->create($dataClass),
            $this->formViewInstantiatorFactory?->create($dataClass),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function import(object $data): FormInterface
    {
        return $this->create(get_class($data))->import($data);
    }

    /**
     * Create DefaultFormFactory using runtime factories
     *
     * @param RegistryInterface|null $registry Registry instance. If null, a new DefaultRegistry will be created.
     * @param bool $enabledView If true, view system will be enabled.
     *
     * @return DefaultFormFactory
     */
    public static function runtime(?RegistryInterface $registry = null, bool $enabledView = true): DefaultFormFactory
    {
        $registry ??= new DefaultRegistry();

        $registry->setDataMapperFactory($dataMapperFactory = new RuntimeDataMapperFactory());
        $registry->setValidatorFactory($validatorFactory = new RuntimeValidatorFactory($registry));
        $registry->setTransformerFactory($transformerFactory = new RuntimeFormTransformerFactory($registry));

        if ($enabledView) {
            $registry->setFormViewInstantiatorFactory($viewInstantiatorFactory = new RuntimeFormViewInstantiatorFactory($registry));
        } else {
            $viewInstantiatorFactory = null;
        }

        return new DefaultFormFactory($dataMapperFactory, $validatorFactory, $transformerFactory, $viewInstantiatorFactory);
    }

    /**
     * Create DefaultFormFactory using generated factories
     *
     * @param RegistryInterface|null $registry Registry instance. If null, a new DefaultRegistry will be created.
     * @param (Closure(string):string)|null $savePathResolver Resolve generated file path using generated class name as parameter. By default, save into `sys_get_temp_dir()`
     * @param bool $enabledView If true, view system will be enabled.
     *
     * @return DefaultFormFactory
     */
    public static function generated(?RegistryInterface $registry = null, ?Closure $savePathResolver = null, bool $enabledView = true): DefaultFormFactory
    {
        $registry ??= new DefaultRegistry();

        $registry->setDataMapperFactory($dataMapperFactory = new GeneratedDataMapperFactory(
            savePathResolver: $savePathResolver,
        ));
        $registry->setValidatorFactory($validatorFactory = new GeneratedValidatorFactory(
            registry: $registry,
            savePathResolver: $savePathResolver,
        ));
        $registry->setTransformerFactory($transformerFactory = new GeneratedFormTransformerFactory(
            registry: $registry,
            savePathResolver: $savePathResolver,
        ));

        if ($enabledView) {
            $registry->setFormViewInstantiatorFactory($viewInstantiatorFactory = new GeneratedFormViewInstantiatorFactory(
                registry: $registry,
                savePathResolver: $savePathResolver,
            ));
        } else {
            $viewInstantiatorFactory = null;
        }

        return new DefaultFormFactory($dataMapperFactory, $validatorFactory, $transformerFactory, $viewInstantiatorFactory);
    }
}
