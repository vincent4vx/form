<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\Transformer\Field\Cast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

final class RuntimeFormTransformerFactory implements FormTransformerFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @todo handle field name mapping
     */
    public function create(string $dataClassName): FormTransformerInterface
    {
        $reflectionClass = new ReflectionClass($dataClassName);
        $fieldsTransformers = [];

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $transformers = [];
            $needCast = $property->hasType();

            foreach ($property->getAttributes(FieldTransformerInterface::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $transformers[] = $attribute->newInstance();

                if ($needCast && $attribute->getName() === Cast::class) {
                    $needCast = false;
                }
            }

            if ($needCast) {
                $transformers[] = new Cast(CastType::fromReflectionType($property->getType()));
            }

            $fieldsTransformers[$property->name] = $transformers;
        }

        return new RuntimeFormTransformer($fieldsTransformers);
    }
}
