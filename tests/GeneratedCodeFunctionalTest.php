<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Fixtures\RequiredParametersRequest;

class GeneratedCodeFunctionalTest extends FunctionalTest
{
    public function form(string $dataClass): FormInterface
    {
        return $this->generatedForm($dataClass);
    }

    public function test_should_generate_classes_and_use_internally()
    {
        $form = $this->generatedForm(FormGenerationTestRequest::class);

        $this->assertFormIsGenerated(FormGenerationTestRequest::class);
        $this->assertTrue(class_exists('Quatrevieux_Form_FormGenerationTestRequestDataMapper'));
        $this->assertTrue(class_exists('Quatrevieux_Form_FormGenerationTestRequestValidator'));

        $this->assertInstanceOf('Quatrevieux_Form_FormGenerationTestRequestDataMapper', (new \ReflectionProperty($form, 'dataMapper'))->getValue($form));
        $this->assertInstanceOf('Quatrevieux_Form_FormGenerationTestRequestValidator', (new \ReflectionProperty($form, 'validator'))->getValue($form));
    }
}

// Use a dedicated class to avoid conflicts with the generated class
class FormGenerationTestRequest extends RequiredParametersRequest
{
}
