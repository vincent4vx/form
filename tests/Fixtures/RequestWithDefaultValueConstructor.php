<?php

namespace Quatrevieux\Form\Fixtures;

class RequestWithDefaultValueConstructor
{
    public function __construct(
        public readonly ?int $foo = 42,
        public readonly string $bar = '???',
    ) {}
}
