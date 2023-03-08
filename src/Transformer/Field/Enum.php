<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use BackedEnum;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Transformer\TransformerException;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;
use Quatrevieux\Form\Validator\FieldError;
use ReflectionEnum;
use Stringable;
use UnitEnum;

use function is_scalar;
use function is_subclass_of;
use function print_r;

/**
 * Transform a value to its corresponding enum instance
 * It supports both UnitEnum and BackedEnum, and resolve the enum instance using the value or the name.
 *
 * Note: This transformer is case-sensitive
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     // If MyEnum is a UnitEnum, the name will be used to get the enum instance
 *     // Else, the value will be used
 *     // If the value is not found, the field will be considered as invalid
 *     #[Enum(MyEnum::class)]
 *     public ?MyEnum $myEnum;
 *
 *     // Use the name instead of the value on BackedEnum
 *     #[Enum(MyEnum::class, useName: true)]
 *     public ?MyEnum $byName;
 *
 *     // If the value is not found, the field will be set to null without error
 *     #[Enum(MyEnum::class, errorOnInvalid: false)]
 *     public ?MyEnum $noError;
 * }
 * </code>
 *
 * @implements FieldTransformerGeneratorInterface<Enum>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Enum implements FieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public const CODE = '052417e1-3a0d-5cd0-afdf-486cfe606edf';

    public function __construct(
        /**
         * The enum class to use
         *
         * Can be a UnitEnum (i.e. enum without value) or a BackedEnum (i.e. enum with value)
         * In case of UnitEnum, the name will be used to get the enum instance
         * In case of BackedEnum, the value will be used, unless the $useName option is set to true
         *
         * @var class-string<UnitEnum|BackedEnum>
         */
        private readonly string $class,

        /**
         * Always use the name to get the enum instance
         * This option is only used for BackedEnum
         */
        private readonly bool $useName = false,

        /**
         * If true, the field will be considered invalid if the value is not a valid choice.
         * If false, the field will be set to null without error.
         */
        private readonly bool $errorOnInvalid = true,

        /**
         * The error message to use if the value is not a valid choice
         * Use {{ value }} as placeholder for the input value
         */
        private readonly string $errorMessage = 'The value {{ value }} is not a valid choice.',
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(mixed $value): ?UnitEnum
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof $this->class) {
            return $value;
        }

        $enum = $this->fromValue($value);

        if ($enum === null && $this->errorOnInvalid) {
            $stringableValue = is_scalar($value) || $value instanceof Stringable ? $value : print_r($value, true);
            throw new TransformerException('Invalid enum value', new FieldError($this->errorMessage, ['value' => $stringableValue], self::CODE));
        }

        return $enum;
    }

    /**
     * {@inheritdoc}
     */
    public function transformToHttp(mixed $value): string|int|null
    {
        if (!$value instanceof $this->class) {
            return null;
        }

        return $this->useName || !$value instanceof BackedEnum ? $value->name : $value->value;
    }

    /**
     * {@inheritdoc}
     */
    public function canThrowError(): bool
    {
        return $this->errorOnInvalid;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $class = $transformer->class;
        $varName = Expr::varName($previousExpression, 'enum');
        $onError = $transformer->errorOnInvalid
            ? 'throw ' . Code::new(TransformerException::class, [
                'Invalid enum value',
                Expr::new(FieldError::class, [
                    $transformer->errorMessage,
                    ['value' => $varName->format('is_scalar({}) || {} instanceof \Stringable ? {} : print_r({}, true)')],
                    self::CODE
                ])
            ])
            : 'null'
        ;

        if ($transformer->useName || !is_subclass_of($class, BackedEnum::class)) {
            /** @var class-string<UnitEnum> $class */
            $caseByName = [];

            foreach ($class::cases() as $case) {
                $caseByName[$case->name] = $case;
            }

            $parse = Code::value($caseByName) . "[{$varName}] ?? {$onError}";
        } else {
            /** @var \ReflectionNamedType $type */
            $type = (new ReflectionEnum($class))->getBackingType();
            $typeName = $type->getName();

            $parse = "\\{$class}::tryFrom(({$typeName}) {$varName}) ?? {$onError}";
        }

        return "(({$varName} = {$previousExpression}) === null ? null : ({$varName} instanceof \\{$class} ? {$varName} : (is_scalar({$varName}) ? {$parse} : {$onError})))";
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $class = $transformer->class;
        $varName = Expr::varName($previousExpression, 'enum');

        $toHttp = $transformer->useName || !is_subclass_of($class, BackedEnum::class)
            ? $varName->name
            : $varName->value
        ;

        return "(!({$varName} = {$previousExpression}) instanceof \\{$class} ? null : {$toHttp})";
    }

    /**
     * Convert input value to enum instance
     *
     * @param mixed $value The value must be a scalar to be converted
     *
     * @return UnitEnum|null The enum item or null if not found
     */
    private function fromValue(mixed $value): ?UnitEnum
    {
        if (!is_scalar($value)) {
            return null;
        }

        $class = $this->class;

        if ($this->useName || !is_subclass_of($class, BackedEnum::class)) {
            foreach ($class::cases() as $case) {
                if ($case->name === $value) {
                    return $case;
                }
            }

            return null;
        }

        /** @var \ReflectionNamedType $type */
        $type = (new ReflectionEnum($class))->getBackingType();

        if ($type->getName() === 'string') {
            $value = (string) $value;
        } else {
            $value = (int) $value;
        }

        return $class::tryFrom($value);
    }
}
