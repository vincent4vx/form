<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;

final class RuntimeFormTransformer implements FormTransformerInterface
{
    public function __construct(
        /**
         * @var array<string, list<FieldTransformerInterface>>
         */
        private readonly array $fieldsTransformers,
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

            /** @var FieldTransformerInterface $transformer */
            foreach ($transformers as $transformer) {
                $fieldValue = $transformer->transformFromHttp($fieldValue);
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
        // @todo
        return $value;
    }
}
