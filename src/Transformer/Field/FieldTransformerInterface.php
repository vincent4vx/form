<?php

namespace Quatrevieux\Form\Transformer\Field;

/**
 * The FieldTransformerInterface is responsible for transform the value
 * from HTTP field to DTO property and vice versa
 */
interface FieldTransformerInterface
{
    /**
     * Transform HTTP field value to PHP / domain value
     *
     * @param mixed $value Base HTTP value
     *
     * @return mixed PHP value
     */
    public function transformFromHttp(mixed $value): mixed;

    /**
     * Normalize PHP / domain value to HTTP field value
     * Should be reverse operation of {@see FieldTransformerInterface::transformFromHttp()}
     *
     * @param mixed $value PHP value
     *
     * @return mixed Normalized HTTP value
     */
    public function transformToHttp(mixed $value): mixed;

    /**
     * Does this transformer can throw an error during "from HTTP" transformation ?
     * If true, calls to {@see FieldTransformerInterface::transformFromHttp()} should be wrapped in a try/catch block
     */
    public function canThrowError(): bool;
}
