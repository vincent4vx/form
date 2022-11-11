<?php

namespace Quatrevieux\Form\Transformer\Field;

interface FieldTransformerInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function transformFromHttp(mixed $value): mixed;

    /**
     * @param mixed $value
     * @return mixed
     */
    public function transformToHttp(mixed $value): mixed;
}
