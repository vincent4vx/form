<?php

namespace Quatrevieux\Form\DataMapper;

use Quatrevieux\Form\Validator\FieldError;

/**
 * The result structure of {@see DataMapperInterface::toDataObject()} method
 *
 * @template T as object
 */
final class DataMapperResult
{
    public function __construct(
        /**
         * The instantiated and hydrated DTO
         *
         * @var T
         */
        public readonly object $dto,

        /**
         * Transformation errors, indexed by field name
         *
         * @var array<string, FieldError|mixed[]>
         */
        public readonly array $errors = [],
    ) {}
}
