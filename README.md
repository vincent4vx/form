# Form Validation and Hydration System

[![GitHub Actions status](https://github.com/vincent4vx/form/workflows/CI/badge.svg)](https://github.com/vincent4vx/form/actions)
[![Codecov status](https://codecov.io/gh/vincent4vx/form/branch/master/graph/badge.svg)](https://app.codecov.io/gh/vincent4vx/form)
[![Packagist version](https://img.shields.io/packagist/v/vincent4vx/form.svg)](https://packagist.org/packages/vincent4vx/form)

A simple form system with fast validation and object hydration. 
This library provides a way to validate form input data and populate an object with the validated data in a simple and efficient way.
It uses code generation to improve performance, and provides a runtime fallback for development.

The philosophy of the library is :
- Use PHP structures to define your forms, instead of building them using a DSL, like a builder or a YAML file.
- The declared structure is the only actually used structure for validation and hydration. No obscure internal structure will be used, nor duplication of the data.
- Components are mostly immutable, allowing caching form instances.
- Heavily use of code generation to improve performance.

## Installation

You can install this library using [Composer](https://getcomposer.org/):

```bash
composer require vincent4vx/form
```

## Usage

### Bootstrap

To use the form validation library, you first need to initialize the registry.
An adapter to PSR-11-compatible dependency injection container can be used, such as PHP-DI or Symfony DI.
If you don't use a dependency injection container, you can use the `DefaultRegistry` class.

Once you've initialized your container, you can use it to instantiate the library's form factory. 
Here's an example that shows how to instantiate the factories using the `ContainerRegistry` class provided with the library:

```php
use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\DefaultFormFactory;
use Quatrevieux\Form\Util\Functions;

// Initialize the container registry with your PSR-11 container.
$registry = new ContainerRegistry($container);

// Instantiate the runtime form factory (for development)
$factory = DefaultFormFactory::runtime($registry);

// Instantiate the generated form factory (for production)
$factory = DefaultFormFactory::generated(
    registry: $registry,
    savePathResolver: Functions::savePathResolver(self::GENERATED_DIR),
);
```

### Simple usage

Once you have instantiated the form factory, you can use it to create your forms.
First, you need to declare your form by declaring the fields it contains as public properties with attributes for validation and transformation rules.

Here's an example of a declaration for a user registration form:

```php

use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\Regex;
use Quatrevieux\Form\Validator\Constraint\PasswordStrength;
use Quatrevieux\Form\Validator\Constraint\EqualsWith;
use Quatrevieux\Form\Validator\Constraint\ValidateVar;
use Quatrevieux\Form\Component\Csrf\Csrf;

class RegistrationRequest
{
    // Non-nullable field will be considered as required
    #[Length(min: 3, max: 256), Regex('/^[a-zA-Z0-9_]+$/')]
    public string $username; 

    #[PasswordStrength]
    public string $password;

    // You can define custom messages for each constraint
    #[EqualsWith('password', message: 'Password confirmation must match the password'))]
    public string $passwordConfirmation;

    #[ValidateVar(ValidateVar::EMAIL)]
    public string $email;
    
    // Optional fields can be defined using the "?" operator, making them nullable
    #[Length(min: 3, max: 256)]
    public ?string $name;
    
    // You can use the Csrf component to add a CSRF token to your form (requires symfony/security-csrf)
    #[Csrf]
    public string $csrf;
}
```

To use this form, you can create an instance of the form using the form factory, and then use it to validate and hydrate your data:

```php
$form = $factory->create(RegistrationRequest::class);

// submit() will validate the data and return a SubmittedForm object
// The value must be stored in a variable, because the $form object is immutable
$submitted = $form->submit($_POST);

if ($submitted->valid()) {
    // If the submitted data is valid, you can access the form data, which is an instance of the RegistrationRequest class:
    $value = $submitted->value();
    
    $value->username;
    $value->password;
    // ...
} else {
    // If the submitted data is invalid, you can access the errors like this:
    $errors = $submitted->errors();
    
    /** @var \Quatrevieux\Form\Validator\FieldError $error */
    foreach ($errors as $field => $error) {
        $error->localizedMessage(); // The error message, translated using the translator
        $error->code; // UUID of the constraint that failed
        $error->parameters; // Failed constraint parameters. For example, the "min" and "max" parameters for the Length constraint
    }
}
```

### Custom validator

The library provides a set of built-in validators, as you can see [here](#validation), but you in a real-world application, 
you will probably need to create your own validators.

There is multiple ways to create a custom validator, depending on your needs (and time constraints).

> Note: don't forget to handle optional fields in your custom validators, as they may be null.

#### The quick and dirty way

The easiest way to create a custom validator is to create a validation method in your form class, and annotate the property with the [`ValidationMethod`](#validationmethod) constraint:

```php
use Quatrevieux\Form\Validator\Constraint\ValidationMethod;

class MyForm
{
    #[ValidationMethod('validateFoo')]
    public string $foo;
    
    public function validateFoo(string $value): ?string
    {
        // Do your validation here
        if (...) {
            // Return an error message if the value is invalid (it will be translated using the translator)
            // Note: the return value may also be a boolean (use message defined in the constraint) or a FieldError object
            // See the documentation for more information
            return 'Foo is invalid'; 
        }

        return null;
    }
}
```

But this method has some drawbacks:
- Polluting your form class with validation methods
- Type safety of parameters is not enforced, nor the method name is checked at compile time
- Reusability is possible, but need to declare a class with static methods for each validation method
- Dependency injection is not possible

So, only use this method for proof of concept or disposable code.

#### Dirty, but with dependency injection

If you need to inject dependencies in your validation method, you can use the [`ValidateBy`](#validateby) constraint with the validator class name,
and implements the [`ConstraintValidatorInterface`](src/Validator/Constraint/ConstraintValidatorInterface.php):

```php
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ValidateBy;

class MyForm
{
    #[ValidateBy(MyValidator::class)]
    public string $foo;
}

// Declare your validator class
class MyValidator implements ConstraintValidatorInterface
{
    public function __construct(
        // Inject your dependencies here
        private readonly MyFooService $service,
    ) {
    }

    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        // $constraint is the ValidateBy constraint, which can be used to access the parameters
        // $value is the value of the field
        // $data is the form object (MyForm in this case)

        if (!$this->service->isValid($value)) {
            // No sugar here, you have to create the FieldError object yourself
            // Note: it will be automatically translated using the translator
            return new FieldError('Foo is invalid');
        }

        return null;
    }
}
```

This method fixes most of the drawbacks of the previous method, but it's still cannot enforce type safety of parameters.
So, it's reasonable to use this method if the validation does not require any parameter.

#### The clean way

Depending on your needs, there is two ways to create a clean validator:
- When dependency injection is not required, you can create a class that extends the [`SelfValidatedConstraint`](src/Validator/Constraint/SelfValidatedConstraint.php) class.
  It will implement both the [`ConstraintInterface`](src/Validator/Constraint/ConstraintInterface.php) and the [`ConstraintValidatorInterface`](src/Validator/Constraint/ConstraintValidatorInterface.php).
  So all you have to do is to declare parameters, and implement the `validate()` method.
- When dependency injection is required, you need to create two classes :
  - One that implements the [`ConstraintInterface`](src/Validator/Constraint/ConstraintInterface.php), which will be used to declare parameters 
  - And another one the  [`ConstraintValidatorInterface`](src/Validator/Constraint/ConstraintValidatorInterface.php), which will be used to validate the data

##### SelfValidatedConstraint

```php
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\SelfValidatedConstraint;
use Quatrevieux\Form\Validator\FieldError;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MyConstraint extends SelfValidatedConstraint
{
    // It's recommended to declare an unique code for your constraint
    public const CODE = 'c6241cc4-c7f5-4951-96b5-bf3e69f9ed15';

    public function __construct(
        // Declare your parameters here
        public readonly string $foo,
        // It's recommended to declare a message parameter, which will be used as the default error message
        // Placeholders can be used to display parameters values
        public readonly string $message = 'Foo is invalid : {{ foo }}',
    ) {
    }

    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        // $constraint is same as $this
        // $value is the value of the field
        // $data is the form object (MyForm in this case)

        if (...) {
            return new FieldError($constraint->message, ['foo' => $constraint->foo], self::CODE);
        }

        return null;
    }
}
```

##### ConstraintInterface and ConstraintValidatorInterface

```php
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\RegistryInterface;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MyConstraint implements ConstraintInterface
{
    // It's recommended to declare an unique code for your constraint
    public const CODE = 'c6241cc4-c7f5-4951-96b5-bf3e69f9ed15';

    public function __construct(
        // Declare your parameters here
        public readonly string $foo,
        // It's recommended to declare a message parameter, which will be used as the default error message
        // Placeholders can be used to display parameters values
        public readonly string $message = 'Foo is invalid : {{ foo }}',
    ) {
    }
    
    public function getValidator(RegistryInterface $registry): ConstraintValidatorInterface
    {
        // Resolve the validator from the registry
        return $registry->getConstraintValidator(MyConstraintValidator::class);
    }
}

class MyConstraintValidator implements ConstraintValidatorInterface
{
    public function __construct(
        // Inject your dependencies here
        private readonly MyFooService $service,
    ) {
    }

    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        // $constraint is the MyConstraint constraint, which can be used to access the parameters
        // $value is the value of the field
        // $data is the form object (MyForm in this case)

        if (!$this->service->isValid($value, $constraint->foo)) {
            return new FieldError($constraint->message, ['foo' => $constraint->foo], MyConstraint::CODE);
        }

        return null;
    }
}
```

#### Code generation

By default, code generation is also performed on custom constraints by inlining the instantiation of the constraint class.
For example, the property:

```php
#[MyConstraint('bar')]
public ?string $foo;
```

Will be compiled in code like this:

```php
if (($error = ($fooConstraint = new MyConstraint('bar'))->getValidator($this->registry)->validate($fooConstraint, $data->foo ?? null, $data)) !== null) {
    $errors['foo'] = $error;
}
```

Which provides decent performance, but it's not optimal. 
So, you may want to generate the validation code yourself on simple constraints heavily used in your forms.

To do so, you can implement the [`ConstraintValidatorGeneratorInterface`](src/Validator/Generator/ConstraintValidatorGeneratorInterface.php) 
on the validator class (or constraint class in case of a self validated constraint).

See library [source code](src/Validator/Constraint) for examples.

### Custom transformers

Unlike validators, no quick and dirty way is provided to create custom transformers.
So, two ways are available to create a custom transformer:
- When dependency injection is not required, you can create a class that implements the [`FieldTransformerInterface`](src/Transformer/Field/FieldTransformerInterface.php) interface.
- When dependency injection is required, you need to create two classes :
  - One that implements the [`DelegatedFieldTransformerInterface`](src/Transformer/Field/DelegatedFieldTransformerInterface.php) interface, providing transformer parameters
  - And another one the [`ConfigurableFieldTransformerInterface`](src/Transformer/Field/ConfigurableFieldTransformerInterface.php) interface, providing the transformer logic

#### FieldTransformerInterface

This is the simplest way to create a custom transformer, but it doesn't allow dependency injection.
You simply have to:
- Create a class that implements the [`FieldTransformerInterface`](src/Transformer/Field/FieldTransformerInterface.php) interface,
- Implements the `transformFromHttp()` for HTTP to form object transformation
- Implements the `transformToHttp()` for form object to HTTP transformation (if needed)
- Declare it as an attribute on the form property.

```php
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MyTransformer implements FieldTransformerInterface
{
    public function __construct(
        // Declare your parameters here
        private readonly string $foo,
    ) {
    }

    public function transformFromHttp(mixed $value): mixed
    {
        // $value is the HTTP value
        // The value will be null if the field is not present in the HTTP request

        // Transform the value here
        return $value;
    }

    public function transformToHttp(mixed $value): mixed
    {
        // $value is the form object value

        // Transform the value here
        return $value;
    }

    public function canThrowError(): bool
    {
        // Return true if the transformer can throw an error
        // If true, the transformer will be wrapped in a try/catch block to mark the field as invalid
        return false;
    }
}

class MyForm
{
    #[MyTransformer('bar')]
    public ?string $foo;
}
```

#### DelegatedFieldTransformerInterface

When you need some dependencies on your transformation logic, you should use the [`DelegatedFieldTransformerInterface`](src/Transformer/Field/DelegatedFieldTransformerInterface.php) interface.
The transformation logic (and dependencies) will be provided by a class implementing the [`ConfigurableFieldTransformerInterface`](src/Transformer/Field/ConfigurableFieldTransformerInterface.php) interface.

```php
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\RegistryInterface;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MyTransformer implements DelegatedFieldTransformerInterface
{
    public function __construct(
        // Declare your parameters here
        public readonly string $foo,
    ) {
    }

    public function getTransformer(RegistryInterface $registry): ConfigurableFieldTransformerInterface
    {
        // Resolve the transformer from the registry
        return $registry->getFieldTransformer(MyTransformerImpl::class);
    }
}

class MyTransformerImpl implements ConfigurableFieldTransformerInterface
{
    public function __construct(
        // Declare your dependencies here
        private readonly MyFooService $service,
    ) {
    }

    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
    {
        // $configuration is the MyTransformer attribute instance
        // $value is the HTTP value
        // The value will be null if the field is not present in the HTTP request

        // Transform the value here
        return $value;
    }

    public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
    {
        // $configuration is the MyTransformer attribute instance
        // $value is the form object value

        // Transform the value here
        return $value;
    }
}
```

#### Code generation

The code generation is mostly the same as for validators, so you can refer to the [validators section](#code-generation) for more information.

To do so, you can implement the [`FieldTransformerGeneratorInterface`](src/Transformer/Generator/FieldTransformerGeneratorInterface.php)
on the transformer implementation class (i.e. class which implements `FieldTransformerInterface` or `ConfigurableFieldTransformerInterface`, but not `DelegatedFieldTransformerInterface`).

See library [source code](src/Transformer/Field) for examples.

## API

### Validation

#### ArrayShape

Source: [src/Validator/Constraint/ArrayShape.php](src/Validator/Constraint/ArrayShape.php)

The `ArrayShape` class is a PHP attribute that can be used to validate if the current field is an array and if it has the expected keys and values types.

**Example:**

```php
class MyForm
{
    #[ArrayShape([
        'firstName' => 'string',
        'lastName' => 'string',
        // Use ? to mark the field as optional
        'age?' => 'int',
        // You can declare a sub array shape
        'address' => [
            'street' => 'string',
            'city' => 'string',
            'zipCode' => 'string|int', // You can use multiple types
        ],
    ])]
    public array $person;

    // You can define array as dynamic list
    #[ArrayShape(key: 'int', value: 'int|float')]
    public array $listOfNumbers;

    // You can disable extra keys
    #[ArrayShape(['foo' => 'string', 'bar' => 'int'], allowExtraKeys: false)]
    public array $fixed;
}
```

**Constructor:**

The `ArrayShape` class constructor takes the following parameters:

| Parameter        | Description                                                                                                                                                                                                                                                                                                                        |
|------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `shape`          | Define array fields and their types. The key is the field name. If the field is optional, add a question mark `?` at the end of the name. The value is the type of the field. The type can be a string, following PHP's disjunctive normal form, a `TypeInterface` instance, or an array which will be converted to an array type. |
| `key`            | The key type for extra keys. The type can be a string, following PHP's disjunctive normal form, a `TypeInterface` instance. Note: this option is ignored for all keys that are defined in the shape.                                                                                                                               |
| `value`          | The value type. The type can be a string, following PHP's disjunctive normal form, a `TypeInterface` instance, or an array which will be converted to an array type. Note: this option is ignored for all values that are defined in the shape.                                                                                    |
| `allowExtraKeys` | Allow extra keys which are not defined in the shape. All these keys will be validated with the key type and the value type.                                                                                                                                                                                                        |
| `message`        | The error message to display.                                                                                                                                                                                                                                                                                                      |

#### Choice

Source: [src/Validator/Constraint/Choice.php](src/Validator/Constraint/Choice.php)

Check if the value is in the given choices.

The value is checked with strict comparison, so ensure that the value is correctly cast. This constraint supports multiple choices (i.e. input value is an array). You can define labels for the choices by using a string key in the choices array.

**Example:**

```php
class MyForm
{
    #[Choice(['foo', 'bar'])]
    public string $foo;

    // Define labels for the choices
    #[Choice([
        'My first label' => 'foo',
        'My other label' => 'bar',
    ])]
    public string $bar;
}
```

**Constructor:**

| Parameter | Description                                                                   |
|-----------|-------------------------------------------------------------------------------|
| `choices` | List of available choices. Use a string key to define a label for the choice. |
| `message` | Error message. Use {{ value }} as placeholder for the invalid value.          |

#### EqualsWith

Source: [src/Validator/Constraint/EqualsWith.php](src/Validator/Constraint/EqualsWith.php)

Check if the current field value is equals to other field value.

**Example:**
```php
class MyForm
{
    #[EqualsWith('password', message: 'Passwords must be equals')]
    public string $passwordConfirm;
    public string $password;
}
```

**Constructor:**

| Parameter | Description                                                                                                     |
|-----------|-----------------------------------------------------------------------------------------------------------------|
| `field`   | The other field name. Must be defined on the same data object.                                                  |
| `message` | Error message displayed if values are different. Use `{{ field }}` placeholder to display the other field name. |
| `strict`  | If true, use a strict comparison (i.e. ===), so type and value will be compared.                                |

#### EqualTo

Source: [src/Validator/Constraint/EqualTo.php](src/Validator/Constraint/EqualTo.php)

Check that the field value is equal to the given value. This comparison use the simple comparison operator (==) and not the strict one (===).

Numeric and string values are supported. To ensure that the comparison is done in the same type, add a typehint to the field and use the same type on the constraint's value.

**Example:**
```php
class MyForm
{
    #[EqualTo(10)]
    public int $foo;
}
```

**Constructor:**

| Parameter | Description                                                                                                                                  |
|-----------|----------------------------------------------------------------------------------------------------------------------------------------------|
| `value`   | The value to compare against.                                                                                                                |
| `message` | The error message displayed if the field value is not equal to the given value. Use `{{ value }}` placeholder to display the compared value. | 

**See also:**
- [IdenticalTo](#identicalto) for a strict comparison
- [NotEqualTo](#notequalto) for the opposite constraint

#### GreaterThan

Source: [src/Validator/Constraint/GreaterThan.php](src/Validator/Constraint/GreaterThan.php)

Check that the field value is greater than the given value.

Numeric and string values are supported. To ensure that the comparison is done in the same type, add a typehint to the field and use the same type on the constraint's value.

**Example:**
```php
class MyForm
{
    #[GreaterThan(10)]
    public int $foo;
}
```

**Constructor:**

| Parameter | Description                                                                                                                                      |
|-----------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| `value`   | The value to compare against.                                                                                                                    |
| `message` | The error message displayed if the field value is not greater than the given value. Use `{{ value }}` placeholder to display the compared value. | 

**See also:**
- [GreaterThanOrEqual](#greaterthanorequal) for a greater or equal comparison
- [LessThanOrEqual](#lessthanorequal) for the opposite constraint

#### GreaterThanOrEqual

Source: [src/Validator/Constraint/GreaterThanOrEqual.php](src/Validator/Constraint/GreaterThanOrEqual.php)

Check that the field value is greater than or equal to the given value. Numeric and string values are supported. To ensure that the comparison is done in the same type, add a typehint to the field and use the same type on the constraint's value.

**Example:**

```php
class MyForm
{
    #[GreaterThanOrEqual(10)]
    public int $foo;
}
```

**Constructor:**

| Parameter | Description                   |
|-----------|-------------------------------|
| `value`   | The value to compare against. |
| `message` | The error message to display. |

**See also:**
- [GreaterThan](#greaterthan) for a greater without equal comparison.
- [LessThan](#lessthan) for the opposite constraint.

#### IdenticalTo

Source: [src/Validator/Constraint/IdenticalTo.php](src/Validator/Constraint/IdenticalTo.php)

Check that the field value is the same as the given value. This comparison uses the strict comparison operator (===).

Numeric and string values are supported. The value type must be the same as the field type, and the field value must be cast using type hint or [Cast](src/Validator/Transformer/Cast.php) transformer.

**Example:**

```php
class MyForm
{
    #[IdenticalTo(10)]
    public int $foo;
}
```

**Constructor:**

| Parameter | Description                                   |
|-----------|-----------------------------------------------|
| `value`   | The value to compare against.                 |
| `message` | The error message to use if validation fails. |

**See also:**

- [EqualTo](#equalto) for a simple comparison
- [NotIdenticalTo](#notidenticalto) for the opposite constraint

#### Length

Source: [src/Validator/Constraint/Length.php](src/Validator/Constraint/Length.php)

Validate the length of a string field. If the field is not a string, this validator will be ignored.

This constraint allows you to check the length of a string field. You can define the minimum and maximum length, and customize the error message.

**Example:**

```php
class MyForm
{
    // Only check the minimum length
    #[Length(min: 2)]
    public string $foo;

    // Only check the maximum length
    #[Length(max: 32)]
    public string $bar;

    // For a fixed length
    #[Length(min: 12, max: 12)]
    public string $baz;

    // Check the length is between 2 and 32 (included)
    #[Length(min: 2, max: 32)]
    public string $qux;
}
```

**Constructor:**

| Parameter | Description                                                                                                                                                                                                                                                                                                                                                                                                       |
|-----------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `min`     | Minimum length (included). If null, no minimum length will be checked.                                                                                                                                                                                                                                                                                                                                            |
| `max`     | Maximum length (included). If null, no maximum length will be checked.                                                                                                                                                                                                                                                                                                                                            |
| `message` | Error message displayed if the length is not between `min` and `max`. Use `{{ min }}` and `{{ max }}` placeholders to display the min and max parameters (if defined). If null, the default message will be used depending on defined parameters: `Length::MIN_MESSAGE` if only `min` is defined, `Length::MAX_MESSAGE` if only `max` is defined, `Length::INTERVAL_MESSAGE` if both `min` and `max` are defined. |

#### LessThan

Source: [src/Validator/Constraint/LessThan.php](src/Validator/Constraint/LessThan.php)

The `LessThan` constraint checks if the field value is less than the given value. The comparison uses the strict comparison operator `<` to ensure that the comparison is done in the same type.

**Example:**
```php
class MyForm
{
    #[LessThan(10)]
    public int $foo;
}
```

**Constructor:**

| Parameter | Description                                                                                                   |
|-----------|---------------------------------------------------------------------------------------------------------------|
| `value`   | The value to compare to the field value.                                                                      |
| `message` | The error message to display if the comparison fails. Default: `'The value should be less than {{ value }}.'` |

**See also:**
- [LessThanOrEqual](#lessthanorequal) for a less than or equal comparison.
- [GreaterThanOrEqual](#greaterthanorequal) for the opposite constraint.

#### LessThanOrEqual

Source: [src/Validator/Constraint/LessThanOrEqual.php](src/Validator/Constraint/LessThanOrEqual.php)

Check that the field value is less than or equal to the given value

Numeric and string values are supported. To ensure that the comparison is done in the same type, add a typehint to the field and use the same type on the constraint's value.

**Example:**

```php
class MyForm
{
    #[LessThanOrEqual(10)]
    public int $foo;
}
```

**Constructor:**

| Parameter | Description                                                |
|-----------|------------------------------------------------------------|
| `value`   | The maximum value allowed for the field                    |
| `message` | The error message if the value is greater than the maximum |

**See also:**

- [LessThan](#lessthan) for a less without equal comparison
- [GreaterThan](#greaterthan) for the opposite constraint

#### NotEqualTo

Source: [src/Validator/Constraint/NotEqualTo.php](src/Validator/Constraint/NotEqualTo.php)

Check that the field value is equal to the given value. This comparison uses the simple comparison operator `!=` and not the strict one `!==`.

Numeric and string values are supported. To ensure that the comparison is done in the same type, add a typehint to the field and use the same type on the constraint's value.

**Example:**

```php
class MyForm
{
    #[NotEqualTo(10)]
    public int $foo;
}
```

**Constructor:**

| Parameter | Description                   |
|-----------|-------------------------------|
| `value`   | The value to compare against. |
| `message` | The validation message.       |

**See also:**

- [NotIdenticalTo](#notidenticalto) for a strict comparison.
- [EqualTo](#equalto) for the opposite constraint.

#### NotIdenticalTo

Source: [src/Validator/Constraint/NotIdenticalTo.php](src/Validator/Constraint/NotIdenticalTo.php)

Check that the field value is equal to the given value. This comparison uses the strict comparison operator `!==`. Numeric and string values are supported. The value type must be the same as the field type, and the field value must be cast using typehint or `Cast` transformer.

**Example:**
```php
class MyForm
{
    #[NotIdenticalTo(10)]
    public int $foo;
}
```

**Constructor:**

| Parameter | Description                   |
|-----------|-------------------------------|
| `value`   | The value to compare against. |
| `message` | The error message to display. |

**See also:**
- [NotEqualTo](#notequalto) for a simple comparison
- [IdenticalTo](#identicalto) for the opposite constraint

#### PasswordStrength

Source: [src/Validator/Constraint/PasswordStrength.php](src/Validator/Constraint/PasswordStrength.php)

Check the strength of a password field. The strength is a logarithmic value representing an approximation of the number of possible combinations.

This algorithm takes into account:
- The presence of lowercase letters
- The presence of uppercase letters
- The presence of digits
- The presence of special characters
- The length of the password

This check does not force the user to use a specific set of characters while still providing a good level of security. 
The strength should be chosen according to the password hashing algorithm used: slower algorithms make brute force attacks slower so a lower strength is acceptable. 
The recommended strength is 51, which takes around one month to brute force at one billion attempts per second.

**Example:**

```php
class User
{
    #[PasswordStrength(min:51, message:"Your password is too weak")]
    private $password;
}
```

**Constructor:**

| Parameter | Description                                                                                                                                                            |
|-----------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `min`     | The minimum strength of the password. If the strength is lower than this value, the validation will fail.                                                              |
| `message` | The error message to display if the password is too weak. Use placeholders `{{ strength }}` and `{{ min_strength }}` to display the strength and the minimum strength. |

#### Regex

Source: [src/Validator/Constraint/Regex.php](src/Validator/Constraint/Regex.php)

Check if the field value matches the given regular expression using PCRE syntax. 
Generates an HTML5 "pattern" attribute if possible.

**Example:**

```php
class MyForm
{
    #[Regex('^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$')]
    public ?string $uuid;

    #[Regex('^[a-z]+$', flags: 'i')]
    public string $foo;
}
```

**Constructor:**

| Parameter | Description                                                                                                                                                                                    |
|-----------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `pattern` | Regular expression pattern, following the PCRE syntax. Unlike the PHP `preg_match()` function, the pattern must not be enclosed by delimiters and flags must be passed as a separate argument. |
| `flags`   | Regular expression flags/pattern modifiers. Usage of flags other than `i` (case-insensitive) will disable HTML5 "pattern" attribute generation.                                                |
| `message` | Error message to display if the value does not match the pattern.                                                                                                                              |

#### Required

Source: [src/Validator/Constraint/Required.php](src/Validator/Constraint/Required.php)

This constraint marks a field as required. The field will be considered valid if it is not null and not an empty string or array. 
Any other value will be considered valid, like `0`, `false`, etc.

> Note: this constraint is not required if the field is typed as non-nullable.

**Example:**

```php
class MyForm
{
    // Explicitly mark the field as required because it is nullable
    #[Required]
    public $foo;

    // The field is required because it is not nullable
    public string $bar;

    // You can define a custom error message to override the default one
    #[Required('This field is required')]
    public string $baz;
}
```

**Constructor:**

| Parameter | Description                                                                                      |
|-----------|--------------------------------------------------------------------------------------------------|
| `message` | The error message to be shown if the value is not valid. Defaults to `'This value is required'`. |

#### UploadedFile

Source: [src/Validator/Constraint/UploadedFile.php](src/Validator/Constraint/UploadedFile.php)

Check if the uploaded file is valid.
Use PSR-7 `UploadedFileInterface`, so [`psr/http-message`](https://packagist.org/packages/psr/http-message) package is required to use this constraint.

**Example:**

```php
class MyForm
{
    // Constraint for simply check that the file is successfully uploaded
    #[UploadedFile]
    public UploadedFileInterface $file;
     // You can also define a file size limit
    #[UploadedFile(maxSize: 1024 * 1024)]
    public UploadedFileInterface $withLimit;
     // You can also define the file type using mime type filter, or file extension filter
    // Note: this is not a security feature, you should always check the actual file type on the server side
    #[UploadedFile(
        allowedMimeTypes: ['image/*', 'application/pdf'], // wildcard is allowed for subtype
        allowedExtensions: ['jpg', 'png', 'pdf'] // you can specify the file extension (without the dot)
    )]
    public UploadedFileInterface $withMimeTypes;
}

// Create and submit the form
// Here, $request is a PSR-7 ServerRequestInterface
$form = $factory->create(MyForm::class);
$submitted = $form->submit($request->getParsedBody() + $request->getUploadedFiles());
```

**Constructor:**

| Parameter           | Description                                                                      |
|---------------------|----------------------------------------------------------------------------------|
| `maxSize`           | Maximum file size in bytes. If defined, files with unknown size will be rejected |
| `allowedMimeTypes`  | List of allowed mime types. You can use wildcards on subtype, like "image/*"     |
| `allowedExtensions` | List of allowed file extensions. The dot must not be included                    |

#### ValidateArray

Source: [`src/Validator/Constraint/ValidateArray.php`](src/Validator/Constraint/ValidateArray.php)

Constraint for array validation. Will apply validation to each element of the array.

**Example:**

```php
class MyRequest
{
    #[ValidateArray(constraints: [
        new Length(min: 3, max: 10),
        new Regex(pattern: '/^[a-z]+$/'),
    ])]
    public ?array $values;
}
```

**Constructor:**

| Parameter         | Description                                                                                                                                                                                                                                               |
|-------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `constraints`     | List of constraints to apply to each element of the array.                                                                                                                                                                                                |
| `message`         | Global error message to show if at least one element of the array is invalid. This message will be used only if `ValidateArray::$aggregateErrors` is true. Use `{{ item_errors }}` as a placeholder for the list of item errors.                          |
| `itemMessage`     | Error message format for each item error. Use as placeholders: `{{ key }}` for the item array key. If the array is not associative, the key will be the item index. Be aware that the key value is not escaped. `{{ error }}` for the item error message. |
| `aggregateErrors` | Aggregate all item errors in a single `FieldError`. The error message will be the `ValidateArray::$message` with the item errors concatenated. If this option is false, the validation will return a `FieldError` for each invalid item.                  |

#### ValidateBy

Source: [src/Validator/Constraint/ValidateBy.php](src/Validator/Constraint/ValidateBy.php)

This is a generic constraint used to validate a field value using a custom validator instance. 
This class allows you to specify the validator class to use and an array of options to pass to the validator. 
It is important to note that it is preferable to use a custom constraint class instead of using the options array.

**Example:**

```php
class MyForm
{
   #[ValidateBy(MyValidator::class, ['checksum' => 15])]
   public string $foo;
}

class MyValidator implements ConstraintValidatorInterface
{
    public function __construct(private ChecksumAlgorithmInterface $algo) {}

    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        if ($this->algo->checksum($value) !== $constraint->options['checksum']) {
            return new FieldError('Invalid checksum');
        }

        return null;
    }
}
```

**Constructor:**

| Parameter        | Description                                                                    |
|------------------|--------------------------------------------------------------------------------|
| `validatorClass` | Validator class to use. Must be registered in the ConstraintValidatorRegistry. |
| `options`        | Array of options to pass to the validator.                                     |

**See also:**
- [Custom validator](#dirty-but-with-dependency-injection) describes how to create a custom validator
  Here's an example of how you could convert the docblock for the `ValidateVar` class to a readme section:

#### ValidateVar

Source: [src/Validator/Constraint/ValidateVar.php](src/Validator/Constraint/ValidateVar.php)

Validate field value using `filter_var()` with `FILTER_VALIDATE_*` constant.

**Example:**

```php
class MyRequest
{
    #[ValidateVar(ValidateVar::EMAIL)]
    public ?string $email;

    #[ValidateVar(ValidateVar::DOMAIN, options: FILTER_FLAG_HOSTNAME)] // You can add flags as an int
    public ?string $domain;

    #[ValidateVar(ValidateVar::INT, options: ['options' => ['min_range' => 0, 'max_range' => 100]])] // You can add options as an array
    public ?float $int;
}
```

**Constructor:**

| Parameter | Description                                                                                                                    |
|-----------|--------------------------------------------------------------------------------------------------------------------------------|
| `filter`  | The id of the validation filter to apply. Should be one of the constants of this class or `FILTER_VALIDATE_*`.                 |
| `options` | Filter options or flags. To use flags with options, use an array with the key `"flags"`, and options with the key `"options"`. |
| `message` | The error message.                                                                                                             |

#### ValidationMethod

Source: [src/Validator/Constraint/ValidationMethod.php](src/Validator/Constraint/ValidationMethod.php)

Validate a field value using a method call.

This method can be a static method on a given class or an instance method declared on the data object. The method will be called with the following arguments:
- the field value
- the data object (on the instance method, this parameter is same as $this)
- extra parameters, as variadic arguments

The method can return one of the following:
- `null`: the field is valid
- `true`: the field is valid
- a string: the field is invalid, the string is the error message
- a `FieldError` instance: the field is invalid
- `false`: the field is invalid, the error message is the default one

**Example:**

```php
class MyForm
{
    // Calling validateFoo() on this instance
    #[ValidateMethod(method: 'validateFoo', parameters: [15], message: 'Invalid checksum')]
    public string $foo;

    // Calling Functions::validateFoo()
    #[ValidateMethod(class: Functions::class, method: 'validateFoo', parameters: [15], message: 'Invalid checksum')]
    public string $foo;

    // Return a boolean, so the default error message is used
    public function validateFoo(string $value, object $data, int $checksum)
    {
        return crc32($value) % 32 === $checksum;
    }

    // Return a string, so the string is used as error message
    public function validateFoo(string $value, object $data, int $checksum)
    {
        if (crc32($value) % 32 !== $checksum) {
            return 'Invalid checksum';
        }

        return null;
    }

    // Return a FieldError instance
    public function validateFoo(string $value, object $data, int $checksum)
    {
        if (crc32($value) % 32 !== $checksum) {
            return new FieldError('Invalid checksum');
        }

        return null;
    }
}

class Functions
{
    // You can also use a static method
    public static function validateFoo(string $value, object $data, int $checksum): bool
    {
        return crc32($value) % 32 === $checksum;
    }
}
```

**Constructor:**

| Parameter    | Description                                                          |
|--------------|----------------------------------------------------------------------|
| `method`     | Method name to call                                                  |
| `class`      | Class name to call the method on                                     |
| `parameters` | Extra parameters to pass to the method                               |
| `message`    | Default error message to use if the method returns false             |
| `code`       | Error code to use if the method returns false or a message as string |

**See also:**
- [Custom validators](#the-quick-and-dirty-way) describes how to create a custom validator

## Internal working

The process of validating and hydrating a form is divided into 3 steps, plus a fourth optional step:
- The transformation step, which transforms the raw data into a normalized format that can be handled by PHP
- The hydration step, which populates the form object with the normalized data
- The validation step, which validates the form object
- And optionally, generation of view objects, which can be used to generate HTML forms

Each step can be compiled into a PHP class using code generation, which improves performance.
Steps will be executed in the reverse order when importing data from the form object.

### Transformation

The transformation is the first step of the form process when calling the `FormInterface::submit()` method.
It's also the last step of the process when calling the `FormInterface::import()` method.

The transformation step is responsible for transforming the raw data into an array of properties 
that can be used to populate the form object on the following step.

So it will perform :
- data normalization, such as converting a string to an integer, or converting a date string to a DateTime object
- filtering, such as removing non-declared fields
- aliasing, such as renaming a field to match the form object property name

For example, the following HTTP form data:
```
name=john&roles=5,42&created_at=2020-01-01T00:00:00Z
```

Can be transformed into the following array:
```php
[
    'name' => 'john',
    'roles' => [5, 42],
    'createdAt' => new \DateTime('2020-01-01T00:00:00Z'),
]
```

During the `import()` step, the transformation will perform the reverse operation, so the previous array will be transformed into the following array:
```php
[
    'name' => 'john',
    'roles' => '5,42',
    'created_at' => '2020-01-01T00:00:00Z',
]
```

> To summarize, the transformation step will transform an HTTP form data array into a normalized and safe array 
> that can be used to populate the form object, and vice versa.

See:
- [FormTransformerInterface](src/Transformer/FormTransformerInterface.php) - The interface which perform the transformation step
- [FieldTransformerInterface](src/Transformer/Field/FieldTransformerInterface.php) - The interface which perform the data transformation for each field

### Hydration

The hydration step is the second step of the form process when calling the `FormInterface::submit()` method, 
and the first step when calling the `FormInterface::import()` method.

On the submit process, the hydration step is responsible for instantiate and populate the form object with the array from the transformation step.
On the import process, the hydration step is responsible for extracting properties array from the form object.

See:
- [DataMapperInterface](src/DataMapper/DataMapperInterface.php) - The interface which perform the hydration step
- [PublicPropertyDataMapper](src/DataMapper/PublicPropertyDataMapper.php) - The default implementation

### Validation

The validation step is the last step of the submit process. The validation is not performed when importing data from the form object.

This step will simply validate each property of the form object using the constraints declared on the form fields.
It will return an array of [`FieldError`](src/Validator/FieldError.php) objects, indexed by the property name.

> Note: some errors can be raised by the transformation step. In this case, the validation of the field will be skipped, 
> and the error of the transformation step will be returned instead.

See:
- [ValidatorInterface](src/Validator/ValidatorInterface.php) - The interface which perform the validation of the whole form object
- [ConstraintInterface](src/Validator/Constraint/ConstraintInterface.php) - The interface for a field constraint
- [ConstraintValidatorInterface](src/Validator/Constraint/ConstraintValidatorInterface.php) - The interface for validate a single constraint

### View generation

The view generation is an optional step that can be used to generate view objects from the form object.
This step is triggered by calling the `FormInterface::view()` method.

If the form is a submitted or an imported one, the view will be generated from `FilledFormInterface::httpValue()`.

See:
- [FormViewInstantiatorInterface](src/View/FormViewInstantiatorInterface.php) - The interface which perform the view generation
- [FormView](src/View/FormView.php) - The view object for a form
- [FieldView](src/View/FieldView.php) - The view object for a field

### Immutability

Most of the form components are immutable, which means that they will return a new instance when you call a method that modify the object.

So, each form process will return a new instance:
- The default form factory will return a new `FormInterface` instance
- `FormInterface::submit()` will return a new `SubmittedFormInterface` instance
- `FormInterface::import()` will return a new `ImportedFormInterface` instance

Each types only provide the methods that are actually available for this type, so you can't call `FormInterface::value()` for example.

The immutability allows to reuse or cache form instances (the default factory will always cache instances).
But it came with a drawback: a new variable is required to store results of each form process.
```php
$form = $factory->create(MyForm::class);

// The following code will not work:
$form->submit($_POST);

if (!$form->valid()) {
    return $this->showErrors($form->errors());
}

return $this->process($form->value());

// The following code will work:
$submitted = $form->submit($_POST);

if (!$submitted->valid()) {
    return $this->showErrors($submitted->errors());
}

return $this->process($submitted->value());
```

## License

This library is licensed under the [MIT license](LICENSE).
