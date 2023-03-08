<?php

namespace Quatrevieux\Form\Embedded;

use Quatrevieux\Form\DataMapper\DataMapperFactoryInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\FormTransformerFactoryInterface;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Transformer\TransformerException;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;

use Quatrevieux\Form\Util\Expr;

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
        private readonly DataMapperFactoryInterface $dataMapperFactory
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
        $dataMapper = $this->dataMapperFactory->create($configuration->class);

        $transformationResult = $transformer->transformFromHttp($value);

        if ($transformationResult->errors) {
            throw new TransformerException('Embedded form has errors', $transformationResult->errors);
        }

        return $dataMapper->toDataObject($transformationResult->values);
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

        $dataMapper = $this->dataMapperFactory->create($configuration->class);
        $transformer = $this->transformerFactory->create($configuration->class);

        return $transformer->transformToHttp($dataMapper->toArray($value));
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Expr::varName($previousExpression);
        $dataMapper = Expr::this()->registry->getDataMapperFactory()->create($transformer->class);
        $transformer = Expr::this()->registry->getTransformerFactory()->create($transformer->class);
        $transformationResult = $transformer->transformFromHttp($varName);
        $transformationResultVarName = Expr::varName($transformationResult);
        $transformerException = Code::new(TransformerException::class, ['Embedded form has errors', $transformationResultVarName->errors]);

        return "is_array({$varName} = {$previousExpression}) ? {$dataMapper}->toDataObject(({$transformationResultVarName} = {$transformationResult})->errors ? throw {$transformerException} : {$transformationResultVarName}->values) : null";
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Code::varName($previousExpression);
        $dataMapper = Expr::this()->registry->getDataMapperFactory()->create($transformer->class);
        $transformer = Expr::this()->registry->getTransformerFactory()->create($transformer->class);

        return "is_object({$varName} = {$previousExpression}) ? {$transformer}->transformToHttp({$dataMapper}->toArray({$varName})) : null";
    }
}
