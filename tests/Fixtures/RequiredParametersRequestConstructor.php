<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\Required;

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
