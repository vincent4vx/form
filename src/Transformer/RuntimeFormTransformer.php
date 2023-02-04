<?php

namespace Quatrevieux\Form\Transformer;

use Exception;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\TransformationError;
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
        private readonly RegistryInterface $registry,
        /**
         * Associate a field name with transformers to apply
         *
         * @var array<string, list<FieldTransformerInterface|DelegatedFieldTransformerInterface>>
         */
        public readonly array $fieldsTransformers,

        /**
         * Map DTO field name to HTTP field name
         *
         * @var array<string, string>
         */
        public readonly array $fieldsNameMapping,

        /**
         * Associate a field name with its error handling configuration
         *
         * @var array<string, TransformationError>
         */
        public readonly array $fieldsTransformationErrors,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(array $value): TransformationResult
    {
        $normalized = [];
        $errors = [];

        foreach ($this->fieldsTransformers as $fieldName => $transformers) {
            $httpFieldName = $this->fieldsNameMapping[$fieldName] ?? $fieldName;
            $originalValue = $value[$httpFieldName] ?? null;

            try {
                $fieldValue = $this->callFromHttpTransformer($originalValue, $transformers);
                $normalized[$fieldName] = $fieldValue;
            } catch (Exception $e) {
                $errorHandlingConfigurator = $this->fieldsTransformationErrors[$fieldName] ?? null;

                if (!$errorHandlingConfigurator?->ignore) {
                    $errors[$fieldName] = $this->toFieldErrors($e, $errorHandlingConfigurator);
                }

                $normalized[$fieldName] = $errorHandlingConfigurator?->keepOriginalValue ? $originalValue : null;
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

    /**
     * Transform an exception into a FieldError or array of FieldError
     *
     * @param Exception $exception Caught exception
     * @param TransformationError|null $errorHandlingConfigurator Error handling configuration
     * @return FieldError|array<string, FieldError|mixed[]>
     */
    private function toFieldErrors(Exception $exception, ?TransformationError $errorHandlingConfigurator): FieldError|array
    {
        if ($exception instanceof TransformerException && !$errorHandlingConfigurator?->hideSubErrors) {
            return $exception->errors;
        }

        return new FieldError(
            message: $errorHandlingConfigurator?->message ?? $exception->getMessage(),
            code: $errorHandlingConfigurator?->code ?? TransformationError::CODE,
            translator: $this->registry->getTranslator(),
        );
    }
}
