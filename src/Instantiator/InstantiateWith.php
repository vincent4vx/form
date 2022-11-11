<?php

namespace Quatrevieux\Form\Instantiator;

use Attribute;

/**
 * Define instantiator class to use for instantiate data object
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class InstantiateWith
{
    public function __construct(
        /**
         * @var class-string<InstantiatorInterface>
         */
        public readonly string $instantiatorClassName,
    ) {
    }
}
