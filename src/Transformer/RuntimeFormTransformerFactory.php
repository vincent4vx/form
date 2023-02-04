<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\Cast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\DefaultValue;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\HttpField;
use Quatrevieux\Form\Transformer\Field\TransformationError;
use ReflectionClass;
use ReflectionProperty;

use function is_subclass_of;

/**
 * Factory for form transformer resolving in runtime transformers by using attributes and reflection API
 *
 * By default, a {@see Cast} transformer will be registered at end of transformers list on each field, if the property is typed.
 * If this transformer is already added, it will not be automatically added.
 * So to disable auto-cast mechanism, use Cast with type Mixed on the property : `#[Cast(CastType::Mixed))]`.
 *
 * @see RuntimeFormTransformer Created transformer type
 */
final class RuntimeFormTransformerFactory implements FormTransformerFactoryInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClassName): FormTransformerInterface
    {
        $reflectionClass = new ReflectionClass($dataClassName);
        $fieldsTransformers = [];
        $fieldsNameMapping = [];
        $fieldsTransformationErrors = [];

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $fieldName = $property->name;

            if ($httpField = $this->fieldNameMapping($property)) {
                $fieldsNameMapping[$fieldName] = $httpField;
            }

            if ($transformationErrorConfig = $this->transformationErrorConfiguration($property)) {
                $fieldsTransformationErrors[$fieldName] = $transformationErrorConfig;
            }

            $fieldsTransformers[$fieldName] = $this->fieldTransformers($property);
        }

        return new RuntimeFormTransformer($this->registry, $fieldsTransformers, $fieldsNameMapping, $fieldsTransformationErrors);
    }

    /**
     * Extract HTTP field name from property using attribute {@see HttpField}
     *
     * @param ReflectionProperty $property
     * @return string|null
     */
    private function fieldNameMapping(ReflectionProperty $property): ?string
    {
        foreach ($property->getAttributes(HttpField::class) as $attribute) {
            return $attribute->newInstance()->name;
        }

        return null;
    }

    /**
     * Extract attribute value of {@see TransformationError}
     *
     * @param ReflectionProperty $property
     * @return TransformationError|null
     */
    private function transformationErrorConfiguration(ReflectionProperty $property): ?TransformationError
    {
        foreach ($property->getAttributes(TransformationError::class) as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }

    /**
     * Extract transformers from property attributes
     *
     * Note: Extract all attributes and filter them using `is_subclass_of` instead of using `getAttributes` with a class name
     *       to ensure than the order of attributes is respected between FieldTransformerInterface and DelegatedFieldTransformerInterface
     *
     * @param ReflectionProperty $property
     * @return list<FieldTransformerInterface|DelegatedFieldTransformerInterface>
     */
    private function fieldTransformers(ReflectionProperty $property): array
    {
        $transformers = [];
        $needCast = $property->hasType();
        $needDefaultValue = $property->getDefaultValue() !== null;

        foreach ($property->getAttributes() as $attribute) {
            $className = $attribute->getName();

            if (!is_subclass_of($className, FieldTransformerInterface::class) && !is_subclass_of($className, DelegatedFieldTransformerInterface::class)) {
                continue;
            }

            /** @var FieldTransformerInterface|DelegatedFieldTransformerInterface $transformer */
            $transformer = $attribute->newInstance();
            $transformers[] = $transformer;

            if ($needCast && $className === Cast::class) {
                $needCast = false;
            }

            if ($needDefaultValue && $className === DefaultValue::class) {
                $needDefaultValue = false;
            }
        }

        if ($needCast) {
            /** @phpstan-ignore-next-line $property->getType() is not null */
            $transformers[] = new Cast(CastType::fromReflectionType($property->getType()));
        }

        if ($needDefaultValue) {
            $transformers[] = new DefaultValue($property->getDefaultValue());
        }

        return $transformers;
    }
}
