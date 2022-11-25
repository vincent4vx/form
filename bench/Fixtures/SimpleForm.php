<?php

namespace Bench\Fixtures;

use Quatrevieux\Form\Validator\Constraint\Length;

class SimpleForm
{
    #[Length(min: 2)]
    public string $firstName;

    #[Length(min: 2)]
    public string $lastName;

    public ?int $age;
}
