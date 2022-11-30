<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\Transformer\Field\Cast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;
use Quatrevieux\Form\Transformer\Field\HttpField;
use ReflectionClass;
use ReflectionProperty;

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
        private readonly FieldTransformerRegistryInterface $registry,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @todo handle field name mapping
     */
    public function create(string $dataClassName): FormTransformerInterface
    {
        $reflectionClass = new ReflectionClass($dataClassName);
        $fieldsTransformers = [];
        $fieldsNameMapping = [];

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            /** @var list<FieldTransformerInterface|DelegatedFieldTransformerInterface> $transformers */
            $transformers = [];
            $needCast = $property->hasType();

            foreach ($property->getAttributes(HttpField::class) as $attribute) {
                $fieldsNameMapping[$property->name] = $attribute->newInstance()->name;
            }

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
            }

            if ($needCast) {
                /** @phpstan-ignore-next-line $property->getType() is not null */
                $transformers[] = new Cast(CastType::fromReflectionType($property->getType()));
            }

            $fieldsTransformers[$property->name] = $transformers;
        }

        return new RuntimeFormTransformer($this->registry, $fieldsTransformers, $fieldsNameMapping);
    }
}
