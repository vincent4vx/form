<?php

namespace Quatrevieux\Form\Embedded;

use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FormView;
use Quatrevieux\Form\View\FormViewInstantiatorFactoryInterface;
use Quatrevieux\Form\View\FormViewInstantiatorInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderInterface;

use function is_array;

/**
 * @implements FieldViewProviderInterface<ArrayOf>
 * @internal Used and instantiated by {@see ArrayOf::getViewProvider()}
 */
final class ArrayOfViewProvider implements FieldViewProviderInterface
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
}
