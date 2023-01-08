<?php

namespace Bench\Fixtures;

use Quatrevieux\Form\Embedded\ArrayOf;
use Quatrevieux\Form\Embedded\Embedded;
use Quatrevieux\Form\Validator\Constraint\Length;

class ComplexeForm
{
    #[Length(min: 3, max: 12)]
    public string $pseudo;
    #[Embedded(Credentials::class)]
    public Credentials $credentials;
    #[ArrayOf(Address::class)]
    public array $addresses;
}

class Credentials
{
    #[Length(min: 3, max: 256)]
    public string $username;
    #[Length(min: 6)]
    public string $password;
}

class Address
{
    public string $street;
    public string $city;
    public string $zipCode;
    public string $country;
}
