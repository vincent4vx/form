<?php

namespace Quatrevieux\Form;

/**
 * Base type for form with data already filled
 * This is the base type for submitted or imported form
 *
 * Like parent FormInterface, this type is immutable, so all operations return a new instance of the form
 *
 * @template T as object
 * @extends FormInterface<T>
 */
interface FilledFormInterface extends FormInterface
{
    /**
     * The form value
     *
     * In case of submitted form, this is the DTO object filled with submitted data
     * In case of imported form, this is the imported value
     *
     * @return T
     */
    public function value(): object;

    /**
     * Get the raw HTTP value
     *
     * In case of submitted form, this value is not transformed or filtered
     * In case of imported form, this value is the transformed value
     *
     * @return array<string, mixed>
     */
    public function httpValue(): array;
}
