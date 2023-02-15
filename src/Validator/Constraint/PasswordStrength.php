<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;

use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;

use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

use function is_scalar;
use function log;
use function preg_match;

/**
 * Check the strength of a password field
 *
 * The strength is a logarithmic value representing an approximation of the number of possible combinations.
 * So an increment of 1 means a 2x increase in the number of possible combinations.
 *
 * This algorithm takes into account :
 * - the presence of lowercase letters
 * - the presence of uppercase letters
 * - the presence of digits
 * - the presence of special characters
 * - the length of the password
 *
 * This check does not force the user to use a specific set of characters while still providing a good level of security.
 * The strength should be chosen according to the password hashing algorithm used : slower algorithms makes brute force attacks slower
 * so a lower strength is acceptable.
 *
 * The recommended strength is 51, which takes around one month to brute force at one billion attempts per second.
 *
 * @implements ConstraintValidatorGeneratorInterface<PasswordStrength>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class PasswordStrength extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public const CODE = 'adf637fd-31b6-558c-aa97-8c9522d310ca';

    private const BASE_BY_PATTERN = [
        '/[a-z]/' => 26,
        '/[A-Z]/' => 26,
        '/[0-9]/' => 10,
        '/[^a-zA-Z0-9]/' => 32,
    ];

    public function __construct(
        /**
         * The minimum strength of the password
         * If the strength is lower than this value, the validation will fail
         */
        public readonly int $min = 51,

        /**
         * The error message to display if the password is too weak
         *
         * Use placeholders `{{ strength }}` and `{{ min_strength }}` to display the strength and the minimum strength
         */
        public readonly string $message = 'The password is too weak',
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        if (!is_scalar($value)) {
            return null;
        }

        $min = $constraint->min;
        $strength = self::computeStrength((string) $value);

        if ($strength >= $min) {
            return null;
        }

        return new FieldError($constraint->message, [
            'strength' => $strength,
            'min_strength' => $min,
        ], self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        return FieldErrorExpression::single(function (string $accessor) use ($constraint) {
            $passwordStrength = Call::static(self::class)->computeStrength(Code::raw('(string) ' . $accessor));
            $passwordStrengthVar = Code::varName($passwordStrength);
            $fieldError = Code::new(FieldError::class, [
                $constraint->message,
                [
                    'strength' => Code::raw($passwordStrengthVar),
                    'min_strength' => $constraint->min,
                ],
                self::CODE,
            ]);

            return "is_scalar({$accessor}) && ({$passwordStrengthVar} = {$passwordStrength}) < {$constraint->min} ? {$fieldError} : null";
        });
    }

    /**
     * Compute the strength of a password
     *
     * @param string $password The password to compute
     *
     * @return int
     */
    public static function computeStrength(string $password): int
    {
        if (empty($password)) {
            return 0;
        }

        $base = 0;

        foreach (self::BASE_BY_PATTERN as $pattern => $baseValue) {
            if (preg_match($pattern, $password)) {
                $base += $baseValue;
            }
        }

        return (int) (strlen($password) * log($base, 2));
    }
}
