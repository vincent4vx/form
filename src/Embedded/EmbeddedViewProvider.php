<?php

namespace Quatrevieux\Form\Embedded;

use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FormView;
use Quatrevieux\Form\View\FormViewInstantiatorFactoryInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderInterface;

use function is_array;

/**
 * @implements FieldViewProviderInterface<Embedded>
 * @internal Used and instantiated by {@see Embedded::getViewProvider()}
 */
final class EmbeddedViewProvider implements FieldViewProviderInterface
{
    public function __construct(
        private readonly FormViewInstantiatorFactoryInterface $instantiatorFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function view(FieldViewProviderConfigurationInterface $configuration, string $name, mixed $value, array|FieldError|null $error, array $attributes): FormView
    {
        $viewInstantiator = $this->instantiatorFactory->create($configuration->class);

        $value = is_array($value) ? $value : [];

        if (!$error) {
            $fieldsErrors = [];
            $globalError = null;
        } elseif (is_array($error)) {
            /** @var array<FieldError|mixed[]> $fieldsErrors */
            $fieldsErrors = $error;
            $globalError = null;
        } else {
            $fieldsErrors = [];
            $globalError = $error;
        }

        $formView = $viewInstantiator->submitted($value, $fieldsErrors, $name);

        if ($globalError) {
            $formView->error = $globalError;
        }

        return $formView;
    }
}
