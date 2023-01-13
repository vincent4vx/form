<?php

namespace Quatrevieux\Form\View\Provider;

use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FieldView;
use Quatrevieux\Form\View\FormView;

/**
 * Generate the field view object, according to the given configuration
 *
 * @template T of FieldViewProviderConfigurationInterface
 */
interface FieldViewProviderInterface
{
    /**
     * Get the view object for the field
     *
     * @param T $configuration Configuration of the field view
     * @param string $name HTTP field name
     * @param mixed $value submitted HTTP value
     * @param FieldError|mixed[]|null $error Field error, or sub-field errors. If null, no error
     * @param array<string, scalar> $attributes Input HTML attributes
     *
     * @return FieldView|FormView The view object. If the field is an aggregation of inputs (i.e. embedded form, or array), a FormView is returned.
     */
    public function view(FieldViewProviderConfigurationInterface $configuration, string $name, mixed $value, FieldError|array|null $error, array $attributes): FieldView|FormView;
}
