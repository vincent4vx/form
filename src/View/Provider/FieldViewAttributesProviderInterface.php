<?php

namespace Quatrevieux\Form\View\Provider;

use Quatrevieux\Form\View\FieldView;

/**
 * Provide HTML attributes to a {@see FieldView} object
 * This type should be used as attribute on the corresponding property on the DTO.
 */
interface FieldViewAttributesProviderInterface
{
    /**
     * Get the attributes for the field view
     *
     * The key is the attribute name, the value is the attribute value.
     * In case of boolean value, it will be used as a flag (e.g. "required" => true will be rendered as "required").
     *
     * @return array<string, scalar>
     *
     * @see FieldView::$attributes
     * @see FieldViewProviderInterface::view() Will be passed to the $attributes argument
     */
    public function getAttributes(): array;
}
