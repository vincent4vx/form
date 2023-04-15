<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\RegistryInterface;

use function array_reverse;

/**
 * Adapt list of transformers to a single {@see FieldTransformerInterface}
 *
 * @internal
 */
final class FieldTransformersAggregate implements FieldTransformerInterface
{
    public function __construct(
        /**
         * @var list<FieldTransformerInterface|DelegatedFieldTransformerInterface>
         */
        private readonly array $transformers,
        private readonly RegistryInterface $registry,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(mixed $value): mixed
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer instanceof DelegatedFieldTransformerInterface) {
                $value = $transformer->getTransformer($this->registry)->transformFromHttp($transformer, $value);
            } else {
                $value = $transformer->transformFromHttp($value);
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function transformToHttp(mixed $value): mixed
    {
        foreach (array_reverse($this->transformers) as $transformer) {
            if ($transformer instanceof DelegatedFieldTransformerInterface) {
                $value = $transformer->getTransformer($this->registry)->transformToHttp($transformer, $value);
            } else {
                $value = $transformer->transformToHttp($value);
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function canThrowError(): bool
    {
        // Do the same as code generation
        // Currently, this value is not used
        return true;
    }
}
