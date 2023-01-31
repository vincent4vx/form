<?php

namespace Quatrevieux\Form\Embedded;

use Closure;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FieldView;
use Quatrevieux\Form\View\FormView;
use Quatrevieux\Form\View\FormViewInstantiatorFactoryInterface;
use Quatrevieux\Form\View\Generator\FieldViewProviderGeneratorInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderInterface;

use function is_array;

/**
 * @implements FieldViewProviderInterface<Embedded>
 * @implements FieldViewProviderGeneratorInterface<Embedded>
 * @internal Used and instantiated by {@see Embedded::getViewProvider()}
 */
final class EmbeddedViewProvider implements FieldViewProviderInterface, FieldViewProviderGeneratorInterface
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

    /**
     * {@inheritdoc}
     */
    public function generateFieldViewExpression(FieldViewProviderConfigurationInterface $configuration, string $name, array $attributes): Closure
    {
        $instantiatorFactory = Expr::this()->registry->getFormViewInstantiatorFactory()->create($configuration->class);

        return static function (string $valueAccessor, string $errorAccessor, ?string $rootFieldNameAccessor) use ($instantiatorFactory, $name): string {
            if ($rootFieldNameAccessor) {
                $name = Code::raw('"{' . $rootFieldNameAccessor . '}[' . $name . ']"');
                $use = ' use ('.$rootFieldNameAccessor.')';
            } else {
                $use = '';
            }

            $errorAccessor = Code::expr($errorAccessor);
            $valueAccessor = Code::expr($valueAccessor);
            $formView = $instantiatorFactory->submitted(
                Code::raw('$value'),
                Code::raw('$fieldsErrors'),
                $name
            );

            $closure = Code::expr(
                'function ($value, $fieldsErrors, $globalError)'.$use.' {' .
                    '$formView = ' . $formView . ';' .
                    '$formView->error = $globalError;' .
                    'return $formView;' .
                '}'
            );

            return $closure(
                $valueAccessor->isArrayOr([]),
                $errorAccessor->isArrayOr([]),
                $errorAccessor->isInstanceOfOr(FieldError::class, null),
            );
        };
    }
}
