<?php

namespace Quatrevieux\Form\DataMapper;

use ReflectionClass;

/**
 * Factory returning runtime data mapper
 * Resolve the data mapper to use from attributes
 */
final class RuntimeDataMapperFactory implements DataMapperFactoryInterface
{
    /**
     * @var array<class-string<DataMapperInterface>, callable(class-string):DataMapperInterface>
     */
    private array $factories = [];

    public function __construct()
    {
        $this->factories[PublicPropertyDataMapper::class] = fn (string $className) => /* @phpstan-ignore-line */ new PublicPropertyDataMapper($className);
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): DataMapperInterface
    {
        $dataMapperClassName = PublicPropertyDataMapper::class;

        foreach ((new ReflectionClass($dataClass))->getAttributes(InstantiateWith::class) as $attribute) {
            $dataMapperClassName = $attribute->newInstance()->dataMapperClassName;
        }

        $factory = $this->factories[$dataMapperClassName] ?? null;

        if (!$factory) {
            return new $dataMapperClassName($dataClass);
        }

        return $factory($dataClass);
    }
}
