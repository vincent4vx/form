<?php

namespace Quatrevieux\Form;

use BadMethodCallException;
use Quatrevieux\Form\View\FormView;

/**
 * Default implementation of ImportedFormInterface
 *
 * @template T as object
 * @extends AbstractFilledForm<T>
 * @implements ImportedFormInterface<T>
 */
final class ImportedForm extends AbstractFilledForm implements ImportedFormInterface
{
    /**
     * {@inheritdoc}
     */
    final public function view(): FormView
    {
        $viewInstantiator = $this->viewInstantiator ?? throw new BadMethodCallException('View system disabled for the form');

        return $viewInstantiator->submitted($this->httpValue, []);
    }
}
