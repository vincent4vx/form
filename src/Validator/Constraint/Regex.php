<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use InvalidArgumentException;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Quatrevieux\Form\View\Provider\FieldViewAttributesProviderInterface;

use function addcslashes;
use function ctype_alpha;
use function is_scalar;
use function preg_last_error_msg;
use function preg_match;
use function str_contains;
use function strlen;
use function strtolower;
use function strtoupper;
use function substr;

/**
 * Check if the field value match the given regular expression
 * Use the PCRE syntax (https://www.php.net/manual/en/pcre.pattern.php)
 *
 * Note: HTML5 "pattern" attribute will be generated if possible (only supports case-insensitive flag)
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     #[Regex('^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$')]
 *     public ?string $uuid;
 *
 *     // Use flags parameter to pass the flags (here, case insensitive)
 *     #[Regex('^[a-z]+$', flags: 'i')]
 *     public string $foo;
 * }
 * </code>
 *
 * @implements ConstraintValidatorGeneratorInterface<Regex>
 *
 * @see preg_match() Used to validate the value
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class Regex extends SelfValidatedConstraint implements FieldViewAttributesProviderInterface, ConstraintValidatorGeneratorInterface
{
    public const CODE = '4ba73c60-bba8-58cc-a92b-7f572ecaaf1f';

    public function __construct(
        /**
         * Regular expression pattern, following the PCRE syntax
         *
         * Unlike the PHP preg_match() function, the pattern must not be enclosed by delimiters
         * and flags must be passed as a separate argument
         */
        public readonly string $pattern,

        /**
         * Regular expression flags / pattern modifiers
         *
         * Note: Usage of flags other than i (case-insensitive) will disable HTML5 "pattern" attribute generation
         *
         * @see https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
         */
        public readonly string $flags = '',

        /**
         * Error message to display if the value does not match the pattern
         */
        public readonly string $message = 'This value is not valid.',
    ) {
        if (@preg_match($this->getGrepPattern(), '') === false) {
            throw new InvalidArgumentException(sprintf('The regular expression "%s" is not valid : %s', $this->pattern, preg_last_error_msg()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        if (!is_scalar($value)) {
            return null;
        }

        $pattern = $constraint->getGrepPattern();

        if (!preg_match($pattern, (string) $value)) {
            return new FieldError($constraint->message, code: self::CODE);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        return FieldErrorExpression::single(fn (string $accessor) => (string) Code::expr($accessor)->format(
            'is_scalar({}) && !preg_match({pattern}, (string) {}) ? {error} : null',
            pattern: $constraint->getGrepPattern(),
            error: new FieldError($constraint->message, code: self::CODE),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        // HTML5 does not support regex flags
        if ($this->flags !== '' && $this->flags !== 'i') {
            return [];
        }

        // Convert the pattern to a valid HTML5 regex
        // Inspired by Symfony\Component\Validator\Constraints\Regex::getHtmlPattern()
        $pattern = $this->pattern;

        // Trim leading ^, otherwise prepend .*
        $pattern = $pattern[0] === '^' ? substr($pattern, 1) : '.*' . $pattern;

        // Trim trailing $, otherwise append .*
        $pattern = $pattern[-1] === '$' ? substr($pattern, 0, -1) : $pattern . '.*';

        // Handle case-insensitive flag
        if (str_contains($this->flags, 'i')) {
            $pattern = self::toCaseInsensitive($pattern);
        }

        return [
            'pattern' => $pattern,
        ];
    }

    /**
     * Compile the regex pattern.
     */
    private function getGrepPattern(): string
    {
        return '#' . addcslashes($this->pattern, '#') . '#' . $this->flags;
    }

    /**
     * Explicitly convert a regex pattern to a case-insensitive one.
     *
     * @param string $pattern The pattern to convert
     * @return string
     */
    private static function toCaseInsensitive(string $pattern): string
    {
        $converted = '';
        $inRange = false;
        $rangeContent = '';
        $escapeNext = false;

        for ($i = 0, $length = strlen($pattern); $i < $length; ++$i) {
            $char = $pattern[$i];

            if ($escapeNext) {
                $converted .= $char;
                $escapeNext = false;
                continue;
            }

            switch ($char) {
                case '[':
                    $converted .= '[';
                    $inRange = true;
                    $rangeContent = '';
                    break;

                case ']':
                    $converted .= strtoupper($rangeContent) . ']';
                    $rangeContent = '';
                    $inRange = false;
                    break;

                case '\\':
                    $converted .= '\\';
                    $escapeNext = true;
                    break;

                default:
                    if ($inRange) {
                        $char = strtolower($char);
                        $converted .= $char;
                        $rangeContent .= $char;
                    } elseif (!ctype_alpha($char)) {
                        $converted .= $char;
                    } else {
                        $converted .= '[' . strtolower($char) . strtoupper($char) . ']';
                    }
                    break;
            }
        }

        return $converted;
    }
}
