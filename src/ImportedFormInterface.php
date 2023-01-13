<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\View\FormView;

/**
 * @template T as object
 */
interface ImportedFormInterface
{
    /**
     * Get imported value
     *
     * @return T
     */
    public function value(): object;

    /**
     * Get imported value normalized as HTTP value
     *
     * @return array<string, mixed>
     */
    public function httpValue(): array;

    public function view(): FormView; // @todo extends FormInterface
}
