<?php

namespace Quatrevieux\Form\View;

use function htmlspecialchars;
use function strtr;

/**
 * Templates for rendering a field with choices
 * Values are callable, so they can be passed to {@see FieldView::render()}.
 */
enum SelectTemplate
{
    /**
     * Render using a <select> element
     */
    case Select;

    /**
     * Render using <input type="radio"> elements, wrapped in a <div>
     */
    case Radio;

    /**
     * Render using <input type="checkbox"> elements, wrapped in a <div>
     */
    case Checkbox;

    /**
     * Perform rendering of the field view
     *
     * @param FieldView $view
     * @return string
     */
    public function __invoke(FieldView $view): string
    {
        return $this->render($view);
    }

    /**
     * Perform rendering of the field view
     *
     * @param FieldView $view
     * @return string
     */
    public function render(FieldView $view): string
    {
        $choices = $view->choices ?? [];
        $template = $this->choiceTemplate();
        $selected = $this->selectedFlag();

        $html = '';

        foreach ($choices as $choice) {
            $html .= self::renderChoice($template, $selected, $view, $choice);
        }

        return self::renderInput($this->inputTemplate(), $view, $html);
    }

    private function inputTemplate(): string
    {
        return match ($this) {
            self::Select => '<select name="{{ name }}" {{ attributes }}>{{ choices }}</select>',
            default => '<div {{ attributes }}>{{ choices }}<div>',
        };
    }

    private function choiceTemplate(): string
    {
        return match ($this) {
            self::Select => '<option value="{{ value }}" {{ attributes }}>{{ label }}</option>',
            self::Radio => '<label><input type="radio" name="{{ name }}" value="{{ value }}" {{ attributes }}>{{ label }}</label>',
            self::Checkbox => '<label><input type="checkbox" name="{{ name }}" value="{{ value }}" {{ attributes }}>{{ label }}</label>',
        };
    }

    private function selectedFlag(): string
    {
        return match ($this) {
            self::Select => 'selected',
            default => 'checked',
        };
    }

    private static function renderChoice(string $template, string $selected, FieldView $view, ChoiceView $choice): string
    {
        return strtr($template, [
            '{{ value }}' => htmlspecialchars((string) $choice->value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5),
            '{{ label }}' => htmlspecialchars($choice->localizedLabel() ?: (string) $choice->value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5),
            '{{ name }}' => htmlspecialchars($view->name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5),
            '{{ attributes }}' => $choice->selected ? $selected : '',
        ]);
    }

    private static function renderInput(string $inputTemplate, FieldView $view, string $choices): string
    {
        return strtr($inputTemplate, [
            '{{ name }}' => htmlspecialchars($view->name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5),
            '{{ attributes }}' => FieldTemplate::renderAttributes($view->attributes),
            '{{ choices }}' => $choices,
        ]);
    }
}
