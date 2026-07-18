<?php

namespace Quatrevieux\Form\Fixtures;

class SimpleRequestConstructor
{
    public function __construct(
        public readonly ?string $foo = null,
        public readonly ?string $bar = null,
    ) {}
}
