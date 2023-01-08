<?php

namespace Quatrevieux\Form\Embedded;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Transformer\TransformerException;

use Quatrevieux\Form\Util\Call;

use Quatrevieux\Form\Util\Code;

use function is_array;

/**
 * @implements ConfigurableFieldTransformerInterface<ArrayOf>
 * @implements FieldTransformerGeneratorInterface<ArrayOf>
 *
 * @internal Instantiated by {@see ArrayOf::getTransformer()}
 */
final class ArrayOfTransformer implements ConfigurableFieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]|null
     */
    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        $className = $configuration->class;

        $transformer = $this->registry->getTransformerFactory()->create($className);
        $instantiator = $this->registry->getInstantiatorFactory()->create($className);

        $result = [];
        $errors = [];

        foreach ($value as $key => $item) {
            $transformationResult = $transformer->transformFromHttp((array) $item);

            if ($transformationResult->errors) {
                $errors[$key] = $transformationResult->errors;
            } else {
                $result[$key] = $instantiator->instantiate($transformationResult->values);
            }
        }

        if ($errors) {
            throw new TransformerException('Some elements are invalid', $errors);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]|null
     */
    public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        $className = $configuration->class;

        $transformer = $this->registry->getTransformerFactory()->create($className);
        $instantiator = $this->registry->getInstantiatorFactory()->create($className);

        $result = [];

        foreach ($value as $key => $item) {
            $result[$key] = $transformer->transformToHttp($instantiator->export($item));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Code::varName($previousExpression);
        $body = 'function ($value) {' .
            '$transformer = ' . Call::object('$this->registry->getTransformerFactory()')->create($transformer->class) . ';' .
            '$instantiator = ' . Call::object('$this->registry->getInstantiatorFactory()')->create($transformer->class) . ';' .
            '$result = [];' .
            '$errors = [];' .
            'foreach ($value as $key => $item) {' .
                '$transformationResult = $transformer->transformFromHttp((array) $item);' .
                'if ($transformationResult->errors) {' .
                    '$errors[$key] = $transformationResult->errors;' .
                '} else {' .
                    '$result[$key] = $instantiator->instantiate($transformationResult->values);' .
                '}' .
            '}' .
            'if ($errors) {' .
                'throw ' . Code::new(TransformerException::class, ['Some elements are invalid', Code::raw('$errors')]) . ';' .
            '}' .
            'return $result;' .
        '}';

        return "!is_array({$varName} = {$previousExpression}) ? null : ({$body})({$varName})";
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Code::varName($previousExpression);
        $body = 'function ($value) {' .
            '$transformer = ' . Call::object('$this->registry->getTransformerFactory()')->create($transformer->class) . ';' .
            '$instantiator = ' . Call::object('$this->registry->getInstantiatorFactory()')->create($transformer->class) . ';' .
            '$result = [];' .
            'foreach ($value as $key => $item) {' .
                '$result[$key] = $transformer->transformToHttp($instantiator->export($item));' .
            '}' .
            'return $result;' .
        '}';

        return "!is_array({$varName} = {$previousExpression}) ? null : ({$body})({$varName})";
    }
}
