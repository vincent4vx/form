<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\Embedded\Embedded;

class WithEmbeddedConstructor
{
    public function __construct(
        public string $foo,
        public string $bar,
        #[Embedded(EmbeddedFormConstructor::class)]
        public EmbeddedFormConstructor $embedded,
    ) {}
}

class EmbeddedFormConstructor
{
    public function __construct(
        public ?string $baz,
        public ?string $rab,
    ) {}
}
