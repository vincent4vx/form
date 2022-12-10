<?php

namespace Quatrevieux\Form\Transformer;

use Exception;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;
use Quatrevieux\Form\Validator\FieldError;

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
        private readonly FieldTransformerRegistryInterface $registry,
        /**
         * @var array<string, list<FieldTransformerInterface|DelegatedFieldTransformerInterface>>
         */
        private readonly array $fieldsTransformers,
        /**
         * @var array<string, string>
         */
        private readonly array $fieldsNameMapping,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @todo customize error handling
     */
    public function transformFromHttp(array $value): TransformationResult
    {
        $normalized = [];
        $errors = [];

        foreach ($this->fieldsTransformers as $fieldName => $transformers) {
            $httpFieldName = $this->fieldsNameMapping[$fieldName] ?? $fieldName;

            try {
                $fieldValue = $this->callFromHttpTransformer($value[$httpFieldName] ?? null, $transformers);
                $normalized[$fieldName] = $fieldValue;
            } catch (Exception $e) {
                $errors[$fieldName] = new FieldError($e->getMessage());
                $normalized[$fieldName] = null;
            }
        }

        return new TransformationResult($normalized, $errors);
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

            $httpFieldName = $this->fieldsNameMapping[$fieldName] ?? $fieldName;
            $normalized[$httpFieldName] = $fieldValue;
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

    /**
     * @return array<string, string>
     */
    public function getFieldsNameMapping(): array
    {
        return $this->fieldsNameMapping;
    }

    /**
     * @param mixed $fieldValue
     * @param list<FieldTransformerInterface|DelegatedFieldTransformerInterface> $transformers
     * @return mixed
     */
    private function callFromHttpTransformer(mixed $fieldValue, array $transformers): mixed
    {
        foreach ($transformers as $transformer) {
            if ($transformer instanceof DelegatedFieldTransformerInterface) {
                $fieldValue = $transformer->getTransformer($this->registry)->transformFromHttp($transformer, $fieldValue);
            } else {
                $fieldValue = $transformer->transformFromHttp($fieldValue);
            }
        }

        return $fieldValue;
    }
}
