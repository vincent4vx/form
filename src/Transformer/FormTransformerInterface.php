<?php

namespace Quatrevieux\Form\Transformer;

interface FormTransformerInterface
{
    /**
     * @param array $value
     * @return array
     */
    public function transformFromHttp(array $value): array;

    /**
     * @param array $value
     * @return array
     */
    public function transformToHttp(array $value): array;
}
