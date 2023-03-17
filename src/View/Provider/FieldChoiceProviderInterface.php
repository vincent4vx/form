<?php

namespace Quatrevieux\Form\View\Provider;

use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\View\ChoiceView;

interface FieldChoiceProviderInterface
{
    /**
     * Get the choices for the field
     *
     * @return ChoiceView[]
     */
    public function choices(mixed $currentValue, FieldTransformerInterface $transformer): array;
}
