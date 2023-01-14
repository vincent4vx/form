<?php

namespace Quatrevieux\Form\View;

use Stringable;

use function htmlspecialchars;
use function is_scalar;

/**
 * FieldView renderer templates
 * Values are callable, so they can be passed to {@see FieldView::render()}.
 */
enum FieldTemplate: string
{
    case Input = '<input name="{{ name }}" value="{{ value }}" {{ attributes }}/>';
    case Textarea = '<textarea name="{{ name }}" {{ attributes }}>{{ value }}</textarea>';

    /**
     * Perform rendering of the field view
     *
     * @param FieldView $view
     * @return string
     */
    public function __invoke(FieldView $view): string
    {
        return self::renderTemplate($this->value, $view);
    }

    /**
     * Render a template string with the given view
     *
     * Available placeholders are:
     * - {{ name }}: the field name
     * - {{ value }}: the field value
     * - {{ attributes }}: the field attributes
     *
     * @param string $template
     * @param FieldView $view
     *
     * @return string
     */
    public static function renderTemplate(string $template, FieldView $view): string
    {
        $value = $view->value;
        $value = is_scalar($value) || $value instanceof Stringable ? (string) $value : '';

        return strtr($template, [
            '{{ name }}' => htmlspecialchars($view->name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5),
            '{{ value }}' => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5),
            '{{ attributes }}' => self::renderAttributes($view->attributes),
        ]);
    }

    /**
     * Render the field attributes as HTML string
     * This string ends with a space when attributes are present.
     *
     * @param array<string, scalar> $attributes
     * @return string
     */
    private static function renderAttributes(array $attributes): string
    {
        $attributesString = '';

        foreach ($attributes as $name => $value) {
            if ($value === false) {
                continue;
            }

            $attributesString .= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);

            if ($value !== true) {
                $attributesString .= '="' . htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5) . '"';
            }

            $attributesString .= ' ';
        }

        return $attributesString;
    }
}
