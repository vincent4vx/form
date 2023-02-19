<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;

use function array_diff_key;
use function array_fill_keys;
use function array_key_exists;
use function array_keys;
use function is_array;
use function is_string;
use function substr;

/**
 * Type for check an array shape.
 * Unlike {@see PrimitiveType::Array}, this type can check keys, values types, and also defined keys.
 */
final class ArrayType implements TypeInterface
{
    /**
     * List of fields and their types.
     *
     * @var array<string, TypeInterface>
     */
    private readonly array $shape;

    /**
     * List of keys marked as optional.
     * Keys names are stored as key and value of this array.
     *
     * @var array<string, string>
     */
    private readonly array $optionalKeys;

    /**
     * The key type for extra keys.
     * This type is not used for keys that are defined in the shape.
     */
    private readonly TypeInterface $keyType;

    /**
     * The value type for extra keys.
     * This type is not used for values that are defined in the shape.
     */
    private readonly TypeInterface $valueType;

    /**
     * Allow extra keys which are not defined in the shape.
     * All these keys will be validated with the key type and the value type.
     */
    private readonly bool $allowExtraKeys;

    /**
     * @param array<string, string|TypeInterface|mixed[]> $shape Define array fields and their types.
     * @param TypeInterface|string $keyType The key type for extra keys.
     * @param TypeInterface|string|array<string, string|TypeInterface|mixed[]> $valueType The value type.
     * @param bool $allowExtraKeys Allow extra keys which are not defined in the shape.
     */
    public function __construct(array $shape = [], TypeInterface|string $keyType = 'string|int', TypeInterface|string|array $valueType = PrimitiveType::Mixed, bool $allowExtraKeys = true)
    {
        [$this->shape, $this->optionalKeys] = self::parseShape($shape);
        $this->keyType = is_string($keyType) ? TypeParser::parse($keyType) : $keyType;
        $this->valueType = match (true) {
            is_string($valueType) => TypeParser::parse($valueType),
            is_array($valueType) => new ArrayType($valueType),
            default => $valueType,
        };
        $this->allowExtraKeys = $allowExtraKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return PrimitiveType::Array->name();
    }

    /**
     * {@inheritdoc}
     */
    public function check(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($this->shape as $key => $type) {
            if (!array_key_exists($key, $value)) {
                if (isset($this->optionalKeys[$key])) {
                    continue;
                }

                return false;
            }

            if (!$type->check($value[$key])) {
                return false;
            }
        }

        return $this->checkExtraKeys($value);
    }

    /**
     * {@inheritdoc}
     */
    public function generateCheck(string $value): string
    {
        $expressions = [];
        $expressions[] = "is_array({$value})";

        foreach ($this->shape as $key => $type) {
            $expressions[] = '(' . $this->generateCheckField($value, $key, $type, isset($this->optionalKeys[$key])) . ')';
        }

        if ($checkExtraKeys = $this->generateCheckExtraKeys($value)) {
            $expressions[] = $checkExtraKeys;
        }

        return implode(' && ', $expressions);
    }

    private function generateCheckField(string $accessor, string $key, TypeInterface $type, bool $optional): string
    {
        $strKey = Code::value($key);

        $keyExistsExpression = Call::array_key_exists($key, Code::raw($accessor));
        $checkTypeExpression = $type->generateCheck("{$accessor}[{$strKey}]");

        return $optional
            ? "(!{$keyExistsExpression} || {$checkTypeExpression})"
            : "({$keyExistsExpression} && {$checkTypeExpression})"
        ;
    }

    private function generateCheckExtraKeys(string $value): ?string
    {
        $keys = array_fill_keys(array_keys($this->shape), 1);

        if (!$this->allowExtraKeys) {
            return Call::array_diff_key(Code::raw($value), $keys) . ' === []';
        }

        if (self::isArrayKey($this->keyType)) {
            $keyCheck = '';
        } else {
            $keyCheck =
                'if (!(' . $this->keyType->generateCheck('$key') . ')) {' .
                    'return false;' .
                '}'
            ;
        }

        if (self::isMixed($this->valueType)) {
            $valueCheck = '';
        } else {
            $valueCheck =
                'if (!(' . $this->valueType->generateCheck('$value') . ')) {' .
                    'return false;' .
                '}'
            ;
        }

        if ($keyCheck || $valueCheck) {
            $extraValues = $this->shape ? Call::array_diff_key(Code::raw($value), $keys) : Code::raw($value);
            return '(function ($values) {' .
                    'foreach ($values as $key => $value) {' .
                        $keyCheck .
                        $valueCheck .
                    '}' .
                    'return true;' .
                '})(' . $extraValues . ')'
            ;
        }

        return null;
    }

    /**
     * @param mixed[] $value
     * @return bool
     */
    private function checkExtraKeys(array $value): bool
    {
        $extraValues = array_diff_key($value, $this->shape);

        if (!$this->allowExtraKeys && $extraValues) {
            return false;
        }

        $valueType = $this->valueType;
        $keyType = $this->keyType;

        foreach ($extraValues as $name => $extraValue) {
            if (!$keyType->check($name)) {
                return false;
            }

            if (!$valueType->check($extraValue)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse and normalize the shape parameter.
     *
     * @param array<string, string|TypeInterface|mixed[]> $shape
     * @return list{array<string, TypeInterface>, array<string, string>}
     */
    private static function parseShape(array $shape): array
    {
        $normalized = [];
        $optionalKeys = [];

        foreach ($shape as $key => $type) {
            if ($key[-1] === '?') {
                $key = substr($key, 0, -1);
                $optionalKeys[$key] = $key;
            }

            if (is_array($type)) {
                // @phpstan-ignore-next-line : Cannot manually specify type for $type
                $type = new ArrayType($type);
            } elseif (is_string($type)) {
                $type = TypeParser::parse($type);
            }

            $normalized[$key] = $type;
        }

        return [$normalized, $optionalKeys];
    }

    /**
     * Check if the given type is mixed (i.e. can be anything).
     */
    private static function isMixed(TypeInterface $type): bool
    {
        if ($type === PrimitiveType::Mixed) {
            return true;
        }

        if ($type instanceof UnionType) {
            foreach ($type->types as $subType) {
                if (self::isMixed($subType)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if the given type cover all possible array keys (i.e. int|string).
     */
    private static function isArrayKey(TypeInterface $type): bool
    {
        if (self::isMixed($type)) {
            return true;
        }

        if (!$type instanceof UnionType) {
            return false;
        }

        $hasInt = false;
        $hasString = false;

        foreach ($type->types as $subType) {
            if ($subType === PrimitiveType::Int) {
                $hasInt = true;
            } elseif ($subType === PrimitiveType::String) {
                $hasString = true;
            }
        }

        return $hasInt && $hasString;
    }
}
