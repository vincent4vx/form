<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\Validator\FieldError;
use RuntimeException;

/**
 * Wrap FieldError into a RuntimeException for handle sub-fields errors
 */
final class TransformerException extends RuntimeException
{
    /**
     * Sub errors. Translator should be already set on them.
     *
     * @var FieldError|array<string, FieldError|mixed[]>
     */
    public readonly FieldError|array $errors;

    /**
     * @param string $message Global error message
     * @param FieldError|array<string, FieldError|mixed[]> $errors Sub errors. Should be indexed by field name.
     */
    public function __construct(string $message, FieldError|array $errors)
    {
        parent::__construct($message);

        $this->errors = $errors;
    }
}
