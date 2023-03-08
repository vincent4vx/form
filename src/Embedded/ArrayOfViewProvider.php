<?php

namespace Quatrevieux\Form\Embedded;

use Closure;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FormView;
use Quatrevieux\Form\View\FormViewInstantiatorFactoryInterface;
use Quatrevieux\Form\View\FormViewInstantiatorInterface;
use Quatrevieux\Form\View\Generator\FieldViewProviderGeneratorInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderInterface;

use function is_array;

/**
 * @implements FieldViewProviderInterface<ArrayOf>
 * @implements FieldViewProviderGeneratorInterface<ArrayOf>
 * @internal Used and instantiated by {@see ArrayOf::getViewProvider()}
 */
final class ArrayOfViewProvider implements FieldViewProviderInterface, FieldViewProviderGeneratorInterface
{
    public function __construct(
        private readonly FormViewInstantiatorFactoryInterface $viewInstantiatorFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function view(FieldViewProviderConfigurationInterface $configuration, string $name, mixed $value, array|FieldError|null $error, array $attributes): FormView
    {
        $value = (array) $value;
        $instantiator = $this->viewInstantiatorFactory->create($configuration->class);

        if (!$error) {
            $fieldsErrors = [];
            $globalError = null;
        } elseif (is_array($error)) {
            $fieldsErrors = $error;
            $globalError = null;
        } else {
            $fieldsErrors = [];
            $globalError = $error;
        }

        $fields = [];

        foreach ($value as $index => $item) {
            /** @var array<FieldError|mixed[]>|FieldError $fieldError */
            $fieldError = $fieldsErrors[$index] ?? [];
            $fields[$index] = $this->itemFormView($instantiator, (array) $item, $fieldError, $name . '[' . $index . ']');
        }

        return new FormView(
            $fields,
            $value,
            $instantiator->default($name . '[]'),
            $globalError
        );
    }

    /**
     * Create a form view for a single array element
     *
     * @param FormViewInstantiatorInterface $instantiator
     * @param mixed[] $value Raw HTTP value of the item
     * @param array<FieldError|mixed[]>|FieldError $error
     * @param string $name Base HTTP field name
     *
     * @return FormView
     */
    private function itemFormView(FormViewInstantiatorInterface $instantiator, array $value, array|FieldError $error, string $name): FormView
    {
        if (is_array($error)) {
            $errors = $error;
            $error = null;
        } else {
            $errors = [];
        }

        $formView = $instantiator->submitted($value, $errors, $name);

        if ($error) {
            $formView->error = $error;
        }

        return $formView;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFieldViewExpression(FieldViewProviderConfigurationInterface $configuration, string $name, array $attributes): Closure
    {
        $instantiatorFactory = Expr::this()->registry->getFormViewInstantiatorFactory()->create($configuration->class);

        return static function (string $valueAccessor, string $errorAccessor, ?string $rootFieldNameAccessor) use ($instantiatorFactory, $name): string {
            $fieldNameExpression = $rootFieldNameAccessor
                ? Code::raw('"{' . $rootFieldNameAccessor . '}[' . $name . '][{$index}]"')
                : Code::raw('"'.$name.'[{$index}]"')
            ;
            $use = $rootFieldNameAccessor ? " use($rootFieldNameAccessor)" : '';

            $closure = Code::expr(
                'function ($values, $errors)' . $use . ' {' .
                    '$instantiator = ' . $instantiatorFactory . ';' .
                    '$fieldsErrors = is_array($errors) ? $errors : [];' .
                    '$fields = [];' .
                    'foreach ($values as $index => $item) {' .
                        '$fieldError = $fieldsErrors[$index] ?? [];' .
                        '$fields[$index] = $field = ' . Code::expr('$instantiator')->submitted(
                            Code::raw('(array) $item'),
                            Code::raw('is_array($fieldError) ? $fieldError : []'),
                            $fieldNameExpression
                        ) . ';' .
                        '$field->error = $fieldError instanceof \\' . FieldError::class . ' ? $fieldError : null;' .
                    '}' .
                    'return ' . Code::new(FormView::class, [
                        Code::raw('$fields'),
                        Code::raw('$values'),
                        Code::expr('$instantiator')->default(
                            $rootFieldNameAccessor
                                ? Code::raw('"{' . $rootFieldNameAccessor . '}[' . $name . '][]"')
                                : $name . '[]'
                        ),
                        Code::expr('$errors')->isInstanceOfOr(FieldError::class, null),
                    ]) . ';' .
                '}'
            );

            return $closure(
                Code::raw("(array) ($valueAccessor)"),
                Code::raw($errorAccessor)
            );
        };
    }
}
