<?php

namespace Quatrevieux\Form\Component\Csrf;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class CsrfTest extends FormTestCase
{
    private TokenStorageInterface $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = new class implements TokenStorageInterface {
            private array $tokens = [];

            public function getToken(string $tokenId): string
            {
                return $this->tokens[$tokenId] ?? '';
            }

            public function setToken(string $tokenId, #[\SensitiveParameter] string $token)
            {
                $this->tokens[$tokenId] = $token;
            }

            public function removeToken(string $tokenId): ?string
            {
                $token = $this->tokens[$tokenId] ?? null;
                unset($this->tokens[$tokenId]);

                return $token;
            }

            public function hasToken(string $tokenId): bool
            {
                return isset($this->tokens[$tokenId]);
            }
        };
        $this->container->set(CsrfTokenManager::class, new CsrfTokenManager(storage: $this->storage));
        $this->container->set(CsrfManager::class, new CsrfManager($this->container->get(CsrfTokenManager::class)));
    }

    public function test_code()
    {
        $this->assertSame(Csrf::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'Csrf')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(CsrfTestRequest::class) : $this->runtimeForm(CsrfTestRequest::class);

        $this->assertFalse($form->submit([])->valid());
        $this->assertErrors(['csrf' => new FieldError('Invalid CSRF token', [], Csrf::CODE)], $form->submit([])->errors());
        $this->assertErrors(['csrf' => new FieldError('Invalid CSRF token', [], Csrf::CODE)], $form->submit(['csrf' => 'invalid'])->errors());

        $view = $form->view();
        $tokenValue = $view['csrf']->value;
        $this->assertEquals('<input name="csrf" value="' . $tokenValue . '" required type="hidden" />', (string) $view['csrf']);

        $this->assertTrue($form->submit(['csrf' => $tokenValue])->valid());
        $this->assertTrue($this->storage->hasToken('foo'));
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_refresh(bool $generated)
    {
        $form = $generated ? $this->generatedForm(CsrfTestRequestRefresh::class) : $this->runtimeForm(CsrfTestRequestRefresh::class);

        $this->assertFalse($form->submit([])->valid());
        $this->assertErrors(['csrf' => new FieldError('Custom message', [], Csrf::CODE)], $form->submit([])->errors());
        $this->assertErrors(['csrf' => new FieldError('Custom message', [], Csrf::CODE)], $form->submit(['csrf' => 'invalid'])->errors());

        $view = $form->view();
        $tokenValue = $view['csrf']->value;
        $this->assertEquals('<input name="csrf" value="' . $tokenValue . '" required type="hidden" />', (string) $view['csrf']);

        $this->assertTrue($form->submit(['csrf' => $tokenValue])->valid());
        $this->assertFalse($this->storage->hasToken('foo'));

        $this->assertNotEquals($form->view()['csrf']->value, $form->view()['csrf']->value);
    }

    public function test_generate_validator()
    {
        $constraint = new Csrf(id: 'foo');
        $this->assertGeneratedValidator("\$this->registry->getConstraintValidator('Quatrevieux\\\Form\\\Component\\\Csrf\\\CsrfManager')->validateToken('foo', (\$data->foo ?? null)) ? null : new \Quatrevieux\Form\Validator\FieldError('Invalid CSRF token', [], '642ecf60-c56e-547b-9064-dd30d553f5dd')", $constraint);

        $constraint = new Csrf(id: 'foo', refresh: true);
        $this->assertGeneratedValidator("\$this->registry->getConstraintValidator('Quatrevieux\\\Form\\\Component\\\Csrf\\\CsrfManager')->validateTokenAndRemove('foo', (\$data->foo ?? null)) ? null : new \Quatrevieux\Form\Validator\FieldError('Invalid CSRF token', [], '642ecf60-c56e-547b-9064-dd30d553f5dd')", $constraint);

        $constraint = new Csrf(id: 'foo', message: 'Custom message');
        $this->assertGeneratedValidator("\$this->registry->getConstraintValidator('Quatrevieux\\\Form\\\Component\\\Csrf\\\CsrfManager')->validateToken('foo', (\$data->foo ?? null)) ? null : new \Quatrevieux\Form\Validator\FieldError('Custom message', [], '642ecf60-c56e-547b-9064-dd30d553f5dd')", $constraint);
    }

    public function test_generate_transformers()
    {
        $manager = new CsrfManager($this->container->get(CsrfTokenManager::class));
        $generator = new FormTransformerGenerator($this->registry);

        $this->assertSame('empty(($__tmp_44e18f0f3b2a419fae74cbbaef66f40e = $data->foo ?? null)) || !is_scalar($__tmp_44e18f0f3b2a419fae74cbbaef66f40e) ? \'__empty__\' : (string) $__tmp_44e18f0f3b2a419fae74cbbaef66f40e', $manager->generateTransformFromHttp(new Csrf(id: 'foo'), '$data->foo ?? null', $generator));
        $this->assertSame('$this->registry->getFieldTransformer(\'Quatrevieux\\\Form\\\Component\\\Csrf\\\CsrfManager\')->getToken(\'foo\')', $manager->generateTransformToHttp(new Csrf(id: 'foo'), '$data->foo ?? null', $generator));
        $this->assertSame('$this->registry->getFieldTransformer(\'Quatrevieux\\\Form\\\Component\\\Csrf\\\CsrfManager\')->getRefreshedToken(\'foo\')', $manager->generateTransformToHttp(new Csrf(id: 'foo', refresh: true), '$data->foo ?? null', $generator));
    }
}

class CsrfTestRequest
{
    #[Csrf(id: 'foo')]
    public string $csrf;
}

class CsrfTestRequestRefresh
{
    #[Csrf(id: 'foo', refresh: true, message: 'Custom message')]
    public string $csrf;
}
