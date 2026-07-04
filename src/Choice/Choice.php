<?php

namespace Quatrevieux\Form\Choice;

use Attribute;
use Quatrevieux\Form\Choice\View\ChoiceView;
use Quatrevieux\Form\Choice\View\FieldChoiceViewProviderInterface;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;

use function is_int;
use function is_string;

/**
 * Check if the value is in the given choices.
 *
 * The value is checked with strict comparison, so ensure that the value is correctly cast.
 * This constraint supports multiple choices (i.e. input value is an array).
 *
 * You can define labels for the choices by using a string key in the choices array.
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     #[Choice(['foo', 'bar'])]
 *     public string $foo;
 *
 *     // Define labels for the choices
 *     #[Choice([
 *         'My first label' => 'foo',
 *         'My other label' => 'bar',
 *     ])]
 *     public string $bar;
 *
 *     // Load choices from a service
 *     #[Choice(BazChoiceProvider::class)]
 *     public string $baz;
 * }
 * </code>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Choice implements ConstraintInterface, FieldChoiceViewProviderInterface
{
    public const CODE = '41ac8b62-e143-5644-a3eb-0fbfff5a2064';

    public function __construct(
        /**
         * List of available choices.
         * Use a string key to define a label for the choice.
         *
         * If a class is given, it will be retrieved from the registry and used as choice providers.
         * This allows loading choices lazylly or using external dependencies.
         *
         * @var mixed[]|class-string<ChoicesProviderInterface>
         */
        public readonly array|string $choices,

        /**
         * Error message.
         * Use {{ value }} as placeholder for the invalid value.
         */
        public readonly string $message = 'The value is not a valid choice.',
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getValidator(RegistryInterface $registry): ConstraintValidatorInterface
    {
        return new ChoiceValidator(is_string($this->choices) ? $registry->getService($this->choices) : null);
    }

    /**
     * {@inheritdoc}
     */
    public function choiceViews(mixed $currentValue, FieldTransformerInterface $transformer, RegistryInterface $registry): array
    {
        if (is_string($this->choices)) {
            $provider = $registry->getService($this->choices);

            if ($provider instanceof FieldChoiceViewProviderInterface) {
                return $provider->choiceViews($currentValue, $transformer, $registry);
            }

            $loadedChoices = $provider->choices();
        } else {
            $loadedChoices = $this->choices;
        }

        $choices = [];

        foreach ($loadedChoices as $label => $choice) {
            /** @var scalar $current */
            $current = $transformer->transformToHttp($choice);
            $choices[] = new ChoiceView($current, !is_int($label) ? $label : null, $current == $currentValue);
        }

        return $choices;
    }
}
