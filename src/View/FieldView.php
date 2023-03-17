<?php

namespace Quatrevieux\Form\View;

use Quatrevieux\Form\Validator\FieldError;
use Stringable;

/**
 * Structure of a single field view
 * Note: Unlike most form components, this class is mutable. So it can be configured from the view.
 */
final class FieldView implements Stringable
{
    public function __construct(
        /**
         * HTTP field name
         * This name may be different from the form field name in case of embedded forms (e.g. "user[username]" for a "username" field in a "user" form).
         */
        public readonly string $name,

        /**
         * Raw HTTP value
         */
        public mixed $value,

        /**
         * Field error, if any
         */
        public ?FieldError $error,

        /**
         * Input HTML attributes
         *
         * May contains HTML 5 validation attributes like "required" or "minlength".
         * In case of boolean value, it will be used as a flag (e.g. "required" => true will be rendered as "required").
         *
         * @var array<string, scalar>
         */
        public array $attributes = [],

        public ?array $choices = null,
    ) {
    }

    /**
     * Define an attribute
     *
     * @param string $name Attribute name
     * @param scalar $value Attribute value
     *
     * @return $this
     */
    public function attr(string $name, mixed $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Add "flag" attributes
     * This is a shortcut for `$this->attr($name, true)`
     *
     * @param string ...$flags List of flags to enable
     *
     * @return $this
     */
    public function is(string... $flags): self
    {
        foreach ($flags as $flag) {
            $this->attributes[$flag] = true;
        }

        return $this;
    }

    /**
     * Add a CSS class on the field, by appending it to the "class" attribute
     *
     * @param string $class CSS class name
     *
     * @return $this
     */
    public function class(string $class): self
    {
        $previousClasses = $this->attributes['class'] ?? null;
        $this->attributes['class'] = $previousClasses ? $previousClasses . ' ' . $class : $class;

        return $this;
    }

    /**
     * Define the input type
     * This is a shortcut for `$this->attr('type', $type)`
     *
     * @param string $type
     *
     * @return $this
     */
    public function type(string $type): self
    {
        $this->attributes['type'] = $type;
        return $this;
    }

    /**
     * @param array|null $choices
     */
    public function choices(?array $choices): self
    {
        $this->choices = $choices;
        return $this;
    }

    /**
     * Render the field view as an HTML string
     *
     * @param callable(self):string $renderer Renderer function. It will receive the current field view as argument.
     *
     * @return string
     */
    public function render(callable $renderer): string
    {
        return $renderer($this);
    }

    /**
     * Render the field as <input/> HTML tag
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render(FieldTemplate::Input);
    }
}
