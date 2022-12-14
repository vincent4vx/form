<?php

namespace Quatrevieux\Form\Embedded;

use Quatrevieux\Form\Instantiator\InstantiatorFactoryInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\FormTransformerFactoryInterface;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Transformer\TransformerException;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;

use function is_array;
use function is_object;

/**
 * @implements ConfigurableFieldTransformerInterface<Embedded>
 * @implements FieldTransformerGeneratorInterface<Embedded>
 *
 * @internal Used and instantiated by {@see Embedded::getTransformer()}
 */
final class EmbeddedTransformer implements ConfigurableFieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        private readonly FormTransformerFactoryInterface $transformerFactory,
        private readonly InstantiatorFactoryInterface $instantiatorFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): ?object
    {
        if (!is_array($value)) {
            return null;
        }

        $transformer = $this->transformerFactory->create($configuration->class);
        $instantiator = $this->instantiatorFactory->create($configuration->class);

        $transformationResult = $transformer->transformFromHttp($value);

        if ($transformationResult->errors) {
            throw new TransformerException('Embedded form has errors', $transformationResult->errors);
        }

        return $instantiator->instantiate($transformationResult->values);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]|null
     */
    public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): ?array
    {
        if (!is_object($value)) {
            return null;
        }

        $instantiator = $this->instantiatorFactory->create($configuration->class);
        $transformer = $this->transformerFactory->create($configuration->class);

        return $transformer->transformToHttp($instantiator->export($value));
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Code::varName($previousExpression);
        $instantiator = Call::object('$this->registry->getInstantiatorFactory()')->create($transformer->class);
        $transformer = Call::object('$this->registry->getTransformerFactory()')->create($transformer->class);
        $transformationResult = Call::object($transformer)->transformFromHttp(Code::raw($varName));
        $transformationResultVarName = Code::varName($transformationResult);
        $transformerException = Code::new(TransformerException::class, ['Embedded form has errors', Code::raw($transformationResultVarName . '->errors')]);

        return "is_array({$varName} = {$previousExpression}) ? {$instantiator}->instantiate(({$transformationResultVarName} = {$transformationResult})->errors ? throw {$transformerException} : {$transformationResultVarName}->values) : null";
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Code::varName($previousExpression);
        $instantiator = Call::object('$this->registry->getInstantiatorFactory()')->create($transformer->class);
        $transformer = Call::object('$this->registry->getTransformerFactory()')->create($transformer->class);

        return "is_object({$varName} = {$previousExpression}) ? {$transformer}->transformToHttp({$instantiator}->export({$varName})) : null";
    }
}
