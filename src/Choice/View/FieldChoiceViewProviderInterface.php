<?php

namespace Quatrevieux\Form\Choice\View;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;

/**
 * Provide choice views to a {@see FieldView} object
 * This type should be used as attribute on the corresponding property on the DTO.
 */
interface FieldChoiceViewProviderInterface
{
    /**
     * Get the choices for the field
     *
     * @param mixed $currentValue Current field value, in raw HTTP format. Will be used to determine the selected choice.
     * @param FieldTransformerInterface $transformer Current field transformer. Will be used to transform the choices values to HTTP format.
     * @param RegistryInterface $registry The registry. Can be used to load choices from a service.
     *
     * @return list<ChoiceView>
     */
    public function choiceViews(mixed $currentValue, FieldTransformerInterface $transformer, RegistryInterface $registry): array;
}
