<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Instantiator\InstantiatorFactoryInterface;
use Quatrevieux\Form\Instantiator\RuntimeInstantiatorFactory;
use Quatrevieux\Form\Transformer\FormTransformerFactoryInterface;
use Quatrevieux\Form\Transformer\RuntimeFormTransformerFactory;
use Quatrevieux\Form\Validator\RuntimeValidatorFactory;
use Quatrevieux\Form\Validator\ValidatorFactoryInterface;

/**
 * Default implementation of FormFactoryInterface
 *
 * @todo factory method with container and code generator config
 */
final class DefaultFormFactory implements FormFactoryInterface
{
    /**
     * @var array<class-string, FormInterface>
     */
    private array $cache = [];

    public function __construct(
        private readonly InstantiatorFactoryInterface $instantiatorFactory,
        private readonly ValidatorFactoryInterface $validatorFactory,
        private readonly FormTransformerFactoryInterface $transformerFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): FormInterface
    {
        return $this->cache[$dataClass] ??= new Form(
            $this->transformerFactory->create($dataClass),
            $this->instantiatorFactory->create($dataClass),
            $this->validatorFactory->create($dataClass),
        );
    }

    /**
     * Create DefaultFormFactory using runtime factories
     *
     * @param RegistryInterface|null $registry Registry instance. If null, a new DefaultRegistry will be created.
     *
     * @return DefaultFormFactory
     */
    public static function runtime(?RegistryInterface $registry = null): DefaultFormFactory
    {
        $registry ??= new DefaultRegistry();

        $registry->setInstantiatorFactory($instantiatorFactory = new RuntimeInstantiatorFactory());
        $registry->setValidatorFactory($validatorFactory = new RuntimeValidatorFactory($registry));
        $registry->setTransformerFactory($transformerFactory = new RuntimeFormTransformerFactory($registry));

        return new DefaultFormFactory($instantiatorFactory, $validatorFactory, $transformerFactory);
    }
}
