<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\DataMapper\ConstructorDataMapper;
use Quatrevieux\Form\DataMapper\InstantiateWith;
use Quatrevieux\Form\Fixtures\RequiredParametersRequestConstructor;

use function class_exists;

class GeneratedCodeWithConstructorFunctionalTest extends FunctionalWithConstructorTest
{
    public function form(string $dataClass): FormInterface
    {
        return $this->generatedForm($dataClass);
    }

    public function test_should_generate_classes_and_use_internally()
    {
        $form = $this->generatedForm(FormGenerationTestRequestConstructor::class);

        $this->assertFormIsGenerated(FormGenerationTestRequestConstructor::class);
        $this->assertTrue(class_exists('Quatrevieux_Form_FormGenerationTestRequestConstructorDataMapper'));
        $this->assertTrue(class_exists('Quatrevieux_Form_FormGenerationTestRequestConstructorValidator'));

        $this->assertInstanceOf('Quatrevieux_Form_FormGenerationTestRequestConstructorDataMapper', (new \ReflectionProperty($form, 'dataMapper'))->getValue($form));
        $this->assertInstanceOf('Quatrevieux_Form_FormGenerationTestRequestConstructorValidator', (new \ReflectionProperty($form, 'validator'))->getValue($form));
    }
}

// Use a dedicated class to avoid conflicts with the generated class
#[InstantiateWith(ConstructorDataMapper::class)]
class FormGenerationTestRequestConstructor extends RequiredParametersRequestConstructor
{
}
