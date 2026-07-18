<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\DataMapper\ConstructorDataMapper;
use Quatrevieux\Form\DataMapper\InstantiateWith;

#[InstantiateWith(ConstructorDataMapper::class)]
class RequestWithDefaultValueConstructor
{
    public function __construct(
        public readonly ?int $foo = 42,
        public readonly string $bar = '???',
    ) {}
}
