<?php

namespace Quatrevieux\Form\Transformer\Field;

/**
 * Transformer implementation of {@link DelegatedFieldTransformerInterface}
 *
 * Works like {@see FieldTransformerInterface} but configuration is passed as parameter.
 *
 * @template T as DelegatedFieldTransformerInterface
 */
interface ConfigurableFieldTransformerInterface
{
    /**
     * Transform HTTP field value to PHP / domain value
     *
     * @param T $configuration Transformer configuration
     * @param mixed $value Base HTTP value
     *
     * @return mixed PHP value
     *
     * @see FieldTransformerInterface::transformFromHttp()
     */
    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed;

    /**
     * Normalize PHP / domain value to HTTP field value
     *
     * @param T $configuration Transformer configuration
     * @param mixed $value PHP value
     *
     * @return mixed Normalized HTTP value
     *
     * @see FieldTransformerInterface::transformToHttp()
     */
    public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed;
}
