<?php

namespace Quatrevieux\Form\Transformer\Field;

use Exception;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Transformer\TransformerException;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;

use function array_reverse;

/**
 * Transformer implementation for {@see TransformEach}
 * This class must be instantiated by the {@see TransformEach::getTransformer()} method for allowing the injection of transformer registry
 *
 * @internal
 * @implements ConfigurableFieldTransformerInterface<TransformEach>
 * @implements FieldTransformerGeneratorInterface<TransformEach>
 */
final class TransformEachImpl implements ConfigurableFieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
    ) {}

    /**
     * {@inheritdoc}
     *
     * @param TransformEach $configuration
     * @return mixed[]|null
     */
    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $transformers = $configuration->transformers;
        $handleElementErrors = $configuration->handleElementsErrors;
        $errors = [];
        $transformed = [];

        foreach ((array) $value as $key => $item) {
            if (!$handleElementErrors) {
                $transformed[$key] = $this->callFromHttpTransformers($transformers, $item);
                continue;
            }

            try {
                $transformed[$key] = $this->callFromHttpTransformers($transformers, $item);
            } catch (TransformerException $e) {
                $errors[$key] = $e->errors;
            } catch (Exception $e) {
                $errors[$key] = new FieldError(
                    message: $e->getMessage(),
                    code: TransformationError::CODE,
                    translator: $this->registry->getTranslator(),
                );
            }
        }

        if ($errors) {
            throw new TransformerException('Some elements of the array are invalid', $errors);
        }

        return $transformed;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransformEach $configuration
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

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $expression = '$item';

        foreach ($transformer->transformers as $elementTransformer) {
            $expression = $generator->generateTransformFromHttp($elementTransformer, $expression);
        }

        if (!$transformer->handleElementsErrors) {
            return Code::expr($previousExpression)->storeAndFormat(
                '{} === null ? null : \array_map(fn ($item) => {expression}, (array) {})',
                expression: Code::raw($expression),
            );
        }

        $fieldErrorFromException = Code::new('FieldError', [
            Code::raw('$e->getMessage()'),
            [],
            TransformationError::CODE,
            Code::raw('$translator'),
        ]);

        $expression = 'function ($values) use ($translator) { '
            . '$errors = []; '
            . '$transformed = []; '
            . 'foreach ($values as $key => $item) { '
                . 'try { '
                    . '$transformed[$key] = ' . $expression . '; '
                . '} catch (\\' . TransformerException::class . ' $e) { '
                    . '$errors[$key] = $e->errors; '
                . '} catch (\Exception $e) { '
                    . '$errors[$key] = ' . $fieldErrorFromException . '; '
                . '} '
            . '} '
            . 'if ($errors) { '
                . 'throw ' . Code::new(TransformerException::class, ['Some elements of the array are invalid', Code::raw('$errors')]) . '; '
            . '} '
            . 'return $transformed; '
        . '}';

        return Code::expr($previousExpression)->storeAndFormat(
            '{} === null ? null : ({expression})((array) {})',
            expression: Code::raw($expression),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $expression = '$item';

        foreach (array_reverse($transformer->transformers) as $transformer) {
            $expression = $generator->generateTransformToHttp($transformer, $expression);
        }

        return Code::expr($previousExpression)->storeAndFormat(
            '{} === null ? null : \array_map(fn ($item) => {expression}, (array) {})',
            expression: Code::raw($expression),
        );
    }

    /**
     * Call all transformers on the given value
     *
     * @param list<FieldTransformerInterface|DelegatedFieldTransformerInterface> $transformers
     * @param mixed $value Base item value
     *
     * @return mixed Transformed value
     */
    private function callFromHttpTransformers(array $transformers, mixed $value): mixed
    {
        foreach ($transformers as $transformer) {
            $value = $transformer instanceof DelegatedFieldTransformerInterface
                ? $transformer->getTransformer($this->registry)->transformFromHttp($transformer, $value)
                : $transformer->transformFromHttp($value)
            ;
        }

        return $value;
    }
}
