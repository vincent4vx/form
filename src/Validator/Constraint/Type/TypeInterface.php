<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

/**
 * Handle type verification and generation of validation code
 */
interface TypeInterface
{
    /**
     * Get the type name
     * The name may lose some information, for example, in array type, subtypes and shape are not included.
     *
     * The name should be able to be parsed by {@see TypeParser::parse()}
     */
    public function name(): string;

    /**
     * Check if the value matches the type
     *
     * @param mixed $value
     *
     * @return bool true if the value matches
     */
    public function check(mixed $value): bool;

    /**
     * Generate PHP code to check if the value matches the type
     *
     * @param string $value The value accessor as PHP code
     *
     * @return string The PHP expression which performs the check of the value, and returns a boolean
     */
    public function generateCheck(string $value): string;
}
