<?php

namespace Quatrevieux\Form\Fixtures;

class ConstructorWithSimpleEnum
{
    public function __construct(
        public readonly MySimpleEnum $enum,
    ) {}
}

enum MySimpleEnum
{
    case Foo;
    case Bar;
}
