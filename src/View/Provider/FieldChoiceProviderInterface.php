<?php

namespace Quatrevieux\Form\View\Provider;

use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\View\ChoiceView;

/**
 * Provide choice views to a {@see FieldView} object
 * This type should be used as attribute on the corresponding property on the DTO.
 */
interface FieldChoiceProviderInterface
{
    /**
     * Get the choices for the field
     *
     * @param mixed $currentValue Current field value, in raw HTTP format. Will be used to determine the selected choice.
     * @param FieldTransformerInterface $transformer Current field transformer. Will be used to transform the choices values to HTTP format.
     *
     * @return list<ChoiceView>
     */
    public function choices(mixed $currentValue, FieldTransformerInterface $transformer): array;
}
