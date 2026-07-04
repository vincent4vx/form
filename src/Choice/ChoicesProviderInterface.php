<?php

namespace Quatrevieux\Form\Choice;

use Quatrevieux\Form\Choice\Label\LabelInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

interface ChoicesProviderInterface
{
    /**
     * List of available choices.
     *
     * Use the key to define a label for the choice.
     * If the default key is used (i.e. an int), the value will be used as a label.
     *
     * @return iterable<string|int|LabelInterface|TranslatableInterface, mixed>
     */
    public function choices(): iterable;

    /**
     * Check if the given value is an available choice.
     * If the type is invalid, false must be returned.
     *
     * @param mixed $value
     * @return bool
     */
    public function contains(mixed $value): bool;
}
