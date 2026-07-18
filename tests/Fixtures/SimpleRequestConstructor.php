<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\DataMapper\ConstructorDataMapper;
use Quatrevieux\Form\DataMapper\InstantiateWith;

#[InstantiateWith(ConstructorDataMapper::class)]
class SimpleRequestConstructor
{
    public function __construct(
        public readonly ?string $foo,
        public readonly ?string $bar,
    ) {}
}
