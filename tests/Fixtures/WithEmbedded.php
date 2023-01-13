<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\Embedded\Embedded;

class WithEmbedded
{
    public ?string $foo;
    public ?string $bar;
    #[Embedded(EmbeddedForm::class)]
    public ?EmbeddedForm $embedded;
}

class EmbeddedForm
{
    public ?string $baz;
    public ?string $rab;
}
