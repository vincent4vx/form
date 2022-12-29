<?php

namespace Quatrevieux\Form;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;

/**
 * Used implementation of TranslatorInterface when no translator is provided
 * This class will simply perform placeholder replacement without any translation
 */
final class DummyTranslator implements TranslatorInterface
{
    use TranslatorTrait;

    private static DummyTranslator $instance;

    /**
     * Get or create the instance of the dummy translator
     *
     * @return static
     */
    public static function instance(): self
    {
        return self::$instance ??= new self();
    }
}
