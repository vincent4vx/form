<?php

namespace Quatrevieux\Form\DataMapper;

use Quatrevieux\Form\RegistryInterface;
use ReflectionAttribute;
use ReflectionClass;

/**
 * Factory returning runtime data mapper
 * Resolve the data mapper to use from attributes
 */
final class RuntimeDataMapperFactory implements DataMapperFactoryInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): DataMapperInterface
    {
        $dataMapper = null;
        $reflection = new ReflectionClass($dataClass);

        foreach ($reflection->getAttributes(DataMapperProviderInterface::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $dataMapper = $attribute->newInstance()->getDataMapper($dataClass, $this->registry);
        }

        if ($dataMapper) {
            return $dataMapper;
        }

        if ($this->shouldUseConstructor($reflection)) {
            return new ConstructorDataMapper($dataClass, $this->registry);
        }

        return new PublicPropertyDataMapper($dataClass);
    }

    /**
     * Constructor will be used to instantiate the class if:
     *
     * - A constructor is present
     * - It has at least on required arguments
     * - Or it declares at least on promoted property
     *
     * @param ReflectionClass $class
     * @return bool
     */
    private function shouldUseConstructor(ReflectionClass $class): bool
    {
        $constructor = $class->getConstructor();

        if (!$constructor) {
            return false;
        }

        if ($constructor->getNumberOfRequiredParameters() > 0) {
            return true;
        }

        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->isPromoted()) {
                return true;
            }
        }

        return false;
    }
}
