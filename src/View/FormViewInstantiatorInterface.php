<?php

namespace Quatrevieux\Form\View;

use Quatrevieux\Form\Validator\FieldError;

/**
 * Type for instantiate the form view object
 */
interface FormViewInstantiatorInterface
{
    /**
     * Create the default form view object
     * This method is used when the form is not submitted nor imported
     *
     * @param string|null $rootField In case of embedded form, define a root HTTP field which will be used as prefix on sub-fields (e.g. "user[address]"). Use null for a root form.
     *
     * @return FormView
     */
    public function default(?string $rootField = null): FormView;

    /**
     * Create the form view object from the submitted data and errors
     *
     * @param mixed[] $value Submitted HTTP data
     * @param array<array-key, FieldError|mixed[]> $errors Fields errors
     * @param string|null $rootField In case of embedded form, define a root HTTP field which will be used as prefix on sub-fields (e.g. "user[address]"). Use null for a root form.
     *
     * @return FormView
     */
    public function submitted(array $value, array $errors, ?string $rootField = null): FormView;
}
