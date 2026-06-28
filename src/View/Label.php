<?php

namespace Quatrevieux\Form\View;

use Override;

/**
 * Simple implementation of {@see LabelInterface} that store a label string
 */
final class Label implements LabelInterface
{
    use LabelTrait;

    public function __construct(
        /**
         * The label. Will be translated if a translator is available.
         */
        private readonly string $label,
    ) {}

    #[Override]
    public function label(): string
    {
        return $this->label;
    }
}
