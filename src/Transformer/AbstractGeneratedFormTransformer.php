<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;

/**
 * Base class for generated form transformers
 *
 * @internal
 */
abstract class AbstractGeneratedFormTransformer implements FormTransformerInterface
{
    public function __construct(
        protected readonly RegistryInterface $registry,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    final public function fieldTransformer(string $fieldName): FieldTransformerInterface
    {
        return new class($this, $fieldName) implements FieldTransformerInterface {
            public function __construct(
                private readonly AbstractGeneratedFormTransformer $transformer,
                private readonly string $fieldName,
            ) {
            }

            public function transformFromHttp(mixed $value): mixed
            {
                return $this->transformer->transformFieldFromHttp($this->fieldName, $value);
            }

            public function transformToHttp(mixed $value): mixed
            {
                return $this->transformer->transformFieldToHttp($this->fieldName, $value);
            }

            public function canThrowError(): bool
            {
                return true;
            }
        };
    }

    /**
     * @internal Do not call this method directly, use {@see FormTransformerInterface::fieldTransformer()} instead
     */
    abstract public function transformFieldFromHttp(string $fieldName, mixed $value): mixed;

    /**
     * @internal Do not call this method directly, use {@see FormTransformerInterface::fieldTransformer()} instead
     */
    abstract public function transformFieldToHttp(string $fieldName, mixed $value): mixed;
}
