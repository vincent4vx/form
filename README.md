# Form Validation and Hydration System

[![GitHub Actions status](https://github.com/vincent4vx/form/workflows/CI/badge.svg)](https://github.com/vincent4vx/form/actions)
[![Codecov status](https://codecov.io/gh/vincent4vx/form/branch/master/graph/badge.svg)](https://app.codecov.io/gh/vincent4vx/form)
[![Packagist version](https://img.shields.io/packagist/v/vincent4vx/form.svg)](https://packagist.org/packages/vincent4vx/form)

A simple form system with fast validation and object hydration. 
This library provides a way to validate form input data and populate an object with the validated data in a simple and efficient way.
It uses code generation to improve performance, and provides a runtime fallback for development.

The philosophy of the library is :
- Use PHP structures to define your forms, instead of building them using a DSL like a builder or a YAML file.
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
