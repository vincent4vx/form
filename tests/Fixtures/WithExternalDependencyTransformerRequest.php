<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;

class WithExternalDependencyTransformerRequest
{
    #[FooTransformer('aqw')]
    public string $foo;
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FooTransformer implements DelegatedFieldTransformerInterface
{
    public function __construct(
        public string $bar,
    ) {

    }

    public function getTransformer(FieldTransformerRegistryInterface $registry): ConfigurableFieldTransformerInterface
    {
        return $registry->getTransformer(FooImplementation::class);
    }
}

class FooImplementation implements ConfigurableFieldTransformerInterface
{

    public function __construct(
        public string $bar,
    ) {

    }

    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
    {
        return $this->bar . $value . $configuration->bar;
    }

    public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
    {
        return $value;
    }
}
