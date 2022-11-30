<?php

namespace Bench\Fixtures;

use Quatrevieux\Form\Transformer\Field\HttpField;
use Quatrevieux\Form\Validator\Constraint\Length;

class SimpleForm
{
    #[Length(min: 2)]
    #[HttpField('first_name')]
    public string $firstName;

    #[Length(min: 2)]
    #[HttpField('last_name')]
    public string $lastName;

    public ?int $age;
}
