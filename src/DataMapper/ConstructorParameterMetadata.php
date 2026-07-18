<?php

namespace Quatrevieux\Form\DataMapper;

final class ConstructorParameterMetadata
{
    public function __construct(
        public readonly string $name,
        public readonly mixed $fallback,
        public readonly bool $required,
        public readonly string $requiredMessage,
    ) {}
}
