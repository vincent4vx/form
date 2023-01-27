<?php

namespace Quatrevieux\Form\View\Generator;

use Closure;
use Quatrevieux\Form\View\FieldView;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;

/**
 * Generate the PHP code for instantiate field view object, according to the given configuration
 *
 * @template C as FieldViewProviderConfigurationInterface
 */
interface FieldViewProviderGeneratorInterface
{
    /**
     * Get the expression generator for instantiating the field view
     *
     * The returned closure takes as arguments:
     * - the value accessor (e.g. "$httpValue['field'] ?? null")
     * - the error accessor (e.g. "$errors['field'] ?? null")
     * - the root field name accessor (e.g. '$rootFieldName'). This can be null, and is only used for embedded forms.
     *
     * The closure must return a string which is the PHP code for instantiating the field view.
     *
     * @param C $configuration Configuration of the field view
     * @param string $name The HTTP field name {@see FieldView::$name}
     * @param array<string, scalar> $attributes Input HTML attributes. {@see FieldView::$attributes}
     *
     * @return Closure(string, string, ?string):string
     */
    public function generateFieldViewExpression(FieldViewProviderConfigurationInterface $configuration, string $name, array $attributes): Closure;
}
