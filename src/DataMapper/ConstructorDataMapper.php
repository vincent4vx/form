<?php

namespace Quatrevieux\Form\DataMapper;

use LogicException;
use Quatrevieux\Form\DataMapper\Generator\DataMapperTypeGeneratorInterface;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;
use Quatrevieux\Form\Validator\Constraint\Required;
use Quatrevieux\Form\Validator\FieldError;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;

use function array_filter;
use function array_map;
use function get_object_vars;
use function sprintf;

/**
 * Instantiate the form DTO using its constructor with promoted properties
 *
 * @template T as object
 * @implements DataMapperInterface<T>
 * @implements DataMapperTypeGeneratorInterface<ConstructorDataMapper<T>>
 */
final class ConstructorDataMapper implements DataMapperInterface, DataMapperTypeGeneratorInterface
{
    /**
     * @var array<string, ConstructorParameterMetadata>|null
     */
    private ?array $parameters = null;

    public function __construct(
        /**
         * Data transfer object class name
         *
         * @var class-string<T> $className
         */
        private readonly string $className,
        private readonly RegistryInterface $registry,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function toDataObject(array $fields): DataMapperResult
    {
        $errors = [];
        $parameterValues = [];

        foreach ($this->parameters() as $field => $parameter) {
            $fieldValue = $fields[$field] ?? null;

            if ($fieldValue === null) {
                $fieldValue = $parameter->fallback;

                if ($parameter->required) {
                    $errors[$field] = new FieldError($parameter->requiredMessage, code: Required::CODE, translator: $this->registry->getTranslator());
                }
            }

            $parameterValues[$field] = $fieldValue;
        }

        return new DataMapperResult(new ($this->className)(...$parameterValues), $errors);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(object $data): array
    {
        return get_object_vars($data);
    }

    /**
     * {@inheritdoc}
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function generateToDataObject(DataMapperInterface $dataMapper): string
    {
        $parameters = $this->parameters();
        $parametersCode = [];

        foreach ($parameters as $parameter) {
            $parametersCode[$parameter->name] = new Expr(sprintf('$fields[%s] ?? %s', Code::value($parameter->name), Code::value($parameter->fallback)));
        }

        $newDtoCode = Code::new($this->className, $parametersCode);

        // Handle required parameters errors
        $requiredParameters = array_filter($parameters, static fn(ConstructorParameterMetadata $param) => $param->required);
        $requiredParametersErrors = array_map(static fn(ConstructorParameterMetadata $param) => $param->requiredMessage, $requiredParameters);
        $requiredParametersErrorsCode = Code::value($requiredParametersErrors);

        $newFieldErrorCode = Code::new(FieldError::class, [new Expr('$message'), [], Required::CODE, Expr::this()->registry->getTranslator()]);
        $newResultCode = Code::new(DataMapperResult::class, [new Expr('$dto'), new Expr('$errors')]);

        return <<<PHP
            \$errors = [];
            \$dto = {$newDtoCode};

            foreach ({$requiredParametersErrorsCode} as \$field => \$message) {
                if (!isset(\$fields[\$field])) {
                    \$errors[\$field] = {$newFieldErrorCode};
                }
            }

            return {$newResultCode};
        PHP;
    }

    /**
     * {@inheritdoc}
     */
    public function generateToArray(DataMapperInterface $dataMapper): string
    {
        return 'return get_object_vars($data);';
    }

    /**
     * Get the fallback values for the constructor parameters if there are missing from the form input.
     *
     * @return array<string, ConstructorParameterMetadata>
     */
    private function parameters(): array
    {
        if ($this->parameters !== null) {
            return $this->parameters;
        }

        $reflectionClass = new ReflectionClass($this->className);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return [];
        }

        $parameters = [];
        $baseRequired = new Required();

        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $parameters[$parameter->getName()] = new ConstructorParameterMetadata(
                    name: $parameter->name,
                    fallback: $parameter->getDefaultValue(),
                    required: false,
                    requiredMessage: $baseRequired->message,
                );
            } else {
                $fallback = $this->resolveFallbackValueFromType($parameter->getType());
                $required = $fallback !== null; // Null fallback means that the parameter is nullable, so it's not required
                $requiredMessage = $baseRequired->message;

                if ($required && $parameter->isPromoted()) {
                    // Get the actual required error message
                    foreach ((new ReflectionProperty($this->className, $parameter->name))->getAttributes(Required::class) as $attribute) {
                        $requiredMessage = $attribute->newInstance()->message;
                    }
                }

                $parameters[$parameter->getName()] = new ConstructorParameterMetadata(
                    name: $parameter->name,
                    fallback: $fallback,
                    required: $required,
                    requiredMessage: $requiredMessage,
                );
            }
        }

        return $this->parameters = $parameters;
    }

    private function resolveFallbackValueFromType(?ReflectionType $type): mixed
    {
        if (!$type || $type->allowsNull()) {
            return null;
        }

        if (!$type instanceof ReflectionNamedType) {
            throw new LogicException(sprintf('Cannot use complex type with %s on %s', self::class, $this->className));
        }

        return match ($type->getName()) {
            'int' => 0,
            'float' => 0.0,
            'string' => '',
            'bool' => false,
            'array' => [],
            default => throw new LogicException(sprintf('Cannot resolve fallback value for type %s on %s', $type->getName(), $this->className)),
        };
    }
}
