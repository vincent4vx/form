<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\Required;

class RequiredParametersRequest
{
    public int $foo;

    #[
        Required('bar must be set'),
        Length(min: 3)
    ]
    public string $bar;
}
