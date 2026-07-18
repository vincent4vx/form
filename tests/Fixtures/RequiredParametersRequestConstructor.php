<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\DataMapper\ConstructorDataMapper;
use Quatrevieux\Form\DataMapper\InstantiateWith;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\Required;

#[InstantiateWith(ConstructorDataMapper::class)]
class RequiredParametersRequestConstructor
{
    public function __construct(
        public readonly int $foo,

        #[
            Required('bar must be set'),
            Length(min: 3)
        ]
        public readonly string $bar,
    ) {}
}
