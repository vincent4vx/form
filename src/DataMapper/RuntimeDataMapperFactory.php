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

        foreach ((new ReflectionClass($dataClass))->getAttributes(DataMapperProviderInterface::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $dataMapper = $attribute->newInstance()->getDataMapper($dataClass, $this->registry);
        }

        return $dataMapper ?? new PublicPropertyDataMapper($dataClass);
    }
}
