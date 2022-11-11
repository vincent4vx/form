<?php

namespace Quatrevieux\Form\Instantiator;

use ReflectionClass;

/**
 * Factory returning runtime instantiator
 */
final class RuntimeInstantiatorFactory implements InstantiatorFactoryInterface
{
    /**
     * @var array<string, callable>
     * @psalm-var class-string-map<T, callable(class-string):T>
     */
    private array $factories = [];

    public function __construct()
    {
        $this->factories[PublicPropertyInstantiator::class] = fn (string $className) => new PublicPropertyInstantiator($className);
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): InstantiatorInterface
    {
        $instantiatorClassName = PublicPropertyInstantiator::class;

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
