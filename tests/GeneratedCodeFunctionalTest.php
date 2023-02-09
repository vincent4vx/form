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
        $form = $this->generatedForm(RequiredParametersRequest::class);

        $this->assertFormIsGenerated(RequiredParametersRequest::class);
        $this->assertTrue(class_exists('Quatrevieux_Form_Fixtures_RequiredParametersRequestDataMapper'));
        $this->assertTrue(class_exists('Quatrevieux_Form_Fixtures_RequiredParametersRequestValidator'));

        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_RequiredParametersRequestDataMapper', (new \ReflectionProperty($form, 'dataMapper'))->getValue($form));
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_RequiredParametersRequestValidator', (new \ReflectionProperty($form, 'validator'))->getValue($form));
    }
}
