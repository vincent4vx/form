<?php

namespace Quatrevieux\Form\View\Generator;

use Closure;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderInterface;

/**
 * Fallback implementation of {@see FieldViewProviderGeneratorInterface} that forward instantiation to {@see FieldViewProviderInterface::view()}.
 *
 * This generator will simply inline view provider instantiation, and call `view()` method.
 * To instantiate provider, promoted property will be used.
 *
 * Generated code example:
 * `($__view_ff45de = new MyFieldViewProvider(foo: "bar"))->getViewProvider($this->registry)->view($__view_ff45de, 'foo', $data['foo'] ?? null, $errors['foo'] ?? null, ['required' => true])`
 *
 * @implements FieldViewProviderGeneratorInterface<FieldViewProviderConfigurationInterface>
 */
final class GenericFieldViewProviderGenerator implements FieldViewProviderGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateFieldViewExpression(FieldViewProviderConfigurationInterface $configuration, string $name, array $attributes): Closure
    {
        $newConfiguration = Code::instantiate($configuration);
        $varName = Expr::varName($newConfiguration, 'view');
        $configurationExpr = "({$varName} = {$newConfiguration})";

        if (!$configuration instanceof FieldViewProviderInterface) {
            $viewProvider = "{$configurationExpr}->getViewProvider(\$this->registry)";
        } else {
            $viewProvider = $configurationExpr;
        }

        return static fn (string $valueAccessor, string $errorAccessor, ?string $rootFieldNameAccessor = null): string => Call::object($viewProvider)->view(
            $varName,
            $rootFieldNameAccessor ? Code::raw('"{' . $rootFieldNameAccessor. '}[' . $name . ']"') : $name,
            Code::raw($valueAccessor),
            Code::raw($errorAccessor),
            $attributes
        );
    }
}
