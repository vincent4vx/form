<?php

namespace Quatrevieux\Form\DataMapper;

use ReflectionClass;

/**
 * Factory returning runtime instantiator
 */
final class RuntimeInstantiatorFactory implements InstantiatorFactoryInterface
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
        $instantiatorClassName = PublicPropertyDataMapper::class;

        foreach ((new ReflectionClass($dataClass))->getAttributes(InstantiateWith::class) as $attribute) {
            $instantiatorClassName = $attribute->newInstance()->instantiatorClassName;
        }

        $factory = $this->factories[$instantiatorClassName] ?? null;

        if (!$factory) {
            return new $instantiatorClassName($dataClass);
        }

        return $factory($dataClass);
    }
}
