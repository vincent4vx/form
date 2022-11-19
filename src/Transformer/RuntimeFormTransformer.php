<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;

/**
 * Transformer implementation using transformer instance resolved at runtime using reflection API and Attributes
 *
 * Transformers are called in order for the `transformFromHttp()` method,
 * and in reverse order for the `transformToHttp()` method.
 *
 * @see RuntimeFormTransformerFactory Factory for this transformer
 */
final class RuntimeFormTransformer implements FormTransformerInterface
{
    public function __construct(
        /**
         * @var array<string, list<FieldTransformerInterface|DelegatedFieldTransformerInterface>>
         */
        private readonly array $fieldsTransformers,
        private readonly FieldTransformerRegistryInterface $registry,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @todo handle exception
     */
    public function transformFromHttp(array $value): array
    {
        $normalized = [];

        foreach ($this->fieldsTransformers as $fieldName => $transformers) {
            $fieldValue = $value[$fieldName] ?? null;

            /** @var FieldTransformerInterface|DelegatedFieldTransformerInterface $transformer */
            foreach ($transformers as $transformer) {
                if ($transformer instanceof DelegatedFieldTransformerInterface) {
                    $fieldValue = $transformer->getTransformer($this->registry)->transformFromHttp($transformer, $fieldValue);
                } else {
                    $fieldValue = $transformer->transformFromHttp($fieldValue);
                }
            }

            $normalized[$fieldName] = $fieldValue;
        }

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function transformToHttp(array $value): array
    {
        $normalized = [];

        foreach ($this->fieldsTransformers as $fieldName => $transformers) {
            $fieldValue = $value[$fieldName] ?? null;

            /** @var FieldTransformerInterface|DelegatedFieldTransformerInterface $transformer */
            foreach (array_reverse($transformers) as $transformer) {
                if ($transformer instanceof DelegatedFieldTransformerInterface) {
                    $fieldValue = $transformer->getTransformer($this->registry)->transformToHttp($transformer, $fieldValue);
                } else {
                    $fieldValue = $transformer->transformToHttp($fieldValue);
                }
            }

            $normalized[$fieldName] = $fieldValue;
        }

        return $normalized;
    }

    /**
     * Get loaded transformers
     *
     * @return array<string, list<FieldTransformerInterface|DelegatedFieldTransformerInterface>>
     */
    public function getFieldsTransformers(): array
    {
        return $this->fieldsTransformers;
    }
}
