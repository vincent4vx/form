<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Instantiator\InstantiatorFactoryInterface;
use Quatrevieux\Form\Transformer\FormTransformerFactoryInterface;
use Quatrevieux\Form\Validator\ValidatorFactoryInterface;

/**
 * Add default implementations for factories getters and setters
 */
trait RegistryTrait
{
    private InstantiatorFactoryInterface $instantiatorFactory;
    private FormTransformerFactoryInterface $transformerFactory;
    private ValidatorFactoryInterface $validatorFactory;

    /**
     * Implements {@see RegistryInterface::getInstantiatorFactory()}
     */
    public function getInstantiatorFactory(): InstantiatorFactoryInterface
    {
        return $this->instantiatorFactory;
    }

    /**
     * Implements {@see RegistryInterface::getTransformerFactory()}
     */
    public function getTransformerFactory(): FormTransformerFactoryInterface
    {
        return $this->transformerFactory;
    }

    /**
     * Implements {@see RegistryInterface::getValidatorFactory()}
     */
    public function getValidatorFactory(): ValidatorFactoryInterface
    {
        return $this->validatorFactory;
    }

    /**
     * Implements {@see RegistryInterface::setInstantiatorFactory()}
     */
    public function setInstantiatorFactory(InstantiatorFactoryInterface $factory): void
    {
        $this->instantiatorFactory = $factory;
    }

    /**
     * Implements {@see RegistryInterface::setTransformerFactory()}
     */
    public function setTransformerFactory(FormTransformerFactoryInterface $factory): void
    {
        $this->transformerFactory = $factory;
    }

    /**
     * Implements {@see RegistryInterface::setValidatorFactory()}
     */
    public function setValidatorFactory(ValidatorFactoryInterface $factory): void
    {
        $this->validatorFactory = $factory;
    }
}
