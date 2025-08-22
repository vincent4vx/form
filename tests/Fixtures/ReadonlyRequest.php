<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\Transformer\Field\DefaultValue;

if (PHP_VERSION_ID > 80400) {
    final readonly class ReadonlyRequest
    {
        #[DefaultValue(42)]
        public(set) ?int $foo;

        #[DefaultValue('???')]
        public(set) string $bar;
    }
}
