<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\Instantiator\InstantiatorInterface;

/**
 * Base type for transform raw HTTP fields to array of data class properties values
 *
 * This transformer is called juste before {@see InstantiatorInterface::instantiate()} on submit,
 * or juste after {@see InstantiatorInterface::export()} on httpValue normalisation.
 *
 * The transformer implementation must filter all extra HTTP fields to ensure that undesired properties will not be filled on data object.
 */
interface FormTransformerInterface
{
    /**
     * Transform raw HTTP value to array of data object properties values
     *
     * @param array $value Raw HTTP value
     *
     * @return array PHP properties values
     */
    public function transformFromHttp(array $value): array;

    /**
     * Transform data object properties values to normalized HTTP fields
     *
     * @param array $value Array of properties values
     *
     * @return array Normalized HTTP fields value
     */
    public function transformToHttp(array $value): array;
}
