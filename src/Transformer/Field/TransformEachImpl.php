<?php

namespace Quatrevieux\Form\Transformer\Field;

/**
 * Transformer implementation for {@see TransformEach}
 * This class must be instantiated by the {@see TransformEach::getTransformer()} method for allowing the injection of transformer registry
 *
 * @internal
 * @implements ConfigurableFieldTransformerInterface<TransformEach>
 */
final class TransformEachImpl implements ConfigurableFieldTransformerInterface
{
    public function __construct(
        private FieldTransformerRegistryInterface $registry,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]|null
     */
    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $transformers = $configuration->transformers;
        $transformed = [];

        foreach ((array) $value as $key => $item) {
            foreach ($transformers as $transformer) {
                $item = $transformer instanceof DelegatedFieldTransformerInterface
                    ? $transformer->getTransformer($this->registry)->transformFromHttp($transformer, $item)
                    : $transformer->transformFromHttp($item)
                ;
            }

            $transformed[$key] = $item;
        }

        return $transformed;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]|null
     */
    public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $transformers = array_reverse($configuration->transformers);
        $transformed = [];

        foreach ((array) $value as $key => $item) {
            foreach ($transformers as $transformer) {
                $item = $transformer instanceof DelegatedFieldTransformerInterface
                    ? $transformer->getTransformer($this->registry)->transformToHttp($transformer, $item)
                    : $transformer->transformToHttp($item)
                ;
            }

            $transformed[$key] = $item;
        }

        return $transformed;
    }
}
