<?php

namespace Quatrevieux\Form\Transformer;

use OutOfBoundsException;
use Quatrevieux\Form\DataMapper\DataMapperInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;

/**
 * Base type for transform raw HTTP fields to array of data class properties values
 *
 * This transformer is called juste before {@see DataMapperInterface::toDataObject()} on submit,
 * or juste after {@see DataMapperInterface::toArray()} on httpValue normalisation.
 *
 * The transformer implementation must filter all extra HTTP fields to ensure that undesired properties will not be filled on data object.
 */
interface FormTransformerInterface
{
    /**
     * Transform raw HTTP value to array of data object properties values
     *
     * @param mixed[] $value Raw HTTP value
     *
     * @return TransformationResult Result of transformation process. Contains properties values and transformation errors
     */
    public function transformFromHttp(array $value): TransformationResult;

    /**
     * Transform data object properties values to normalized HTTP fields
     *
     * @param mixed[] $value Array of properties values
     *
     * @return mixed[] Normalized HTTP fields value
     */
    public function transformToHttp(array $value): array;

    /**
     * Get the transformer for the given field
     *
     * @param string $fieldName Field name
     *
     * @return FieldTransformerInterface
     */
    public function fieldTransformer(string $fieldName): FieldTransformerInterface;
}
