<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Psr\Http\Message\UploadedFileInterface;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;

class UploadedFileTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(UploadedFile::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'UploadedFile')->toString());
    }

    public function test_html_attribute()
    {
        $this->assertSame(['type' => 'file'], (new UploadedFile())->getAttributes());
        $this->assertSame(['type' => 'file', 'accept' => '.png,.jpg'], (new UploadedFile(allowedExtensions: ['png', 'jpg']))->getAttributes());
        $this->assertSame(['type' => 'file', 'accept' => 'image/*,application/pdf'], (new UploadedFile(allowedMimeTypes: ['image/*', 'application/pdf']))->getAttributes());
        $this->assertSame(['type' => 'file', 'accept' => 'image/*,application/pdf,.jpg,.png'], (new UploadedFile(allowedMimeTypes: ['image/*', 'application/pdf'], allowedExtensions: ['jpg', 'png']))->getAttributes());
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(UploadFormTest::class) : $this->runtimeForm(UploadFormTest::class);

        $this->assertErrors(['file' => new FieldError('The upload has failed', code: UploadedFile::CODE)], $form->submit(['file' => 'invalid'])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_PARTIAL);
        $this->assertErrors(['file' => new FieldError('The upload has failed', ['error' => UPLOAD_ERR_PARTIAL], code: UploadedFile::CODE)], $form->submit(['file' => $file])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_FORM_SIZE);
        $file->method('getSize')->willReturn(3256984);
        $this->assertErrors(['file' => new FieldError('The file is too big ({{ current_size }}), maximum allowed size is {{ max_size }}', [
            'current_size_bytes' => 3256984,
            'max_size_bytes' => 2097152,
            'current_size' => '3.1 MB',
            'max_size' => '2 MB',
        ], code: UploadedFile::CODE)], $form->submit(['file' => $file])->errors());
        $file->method('getError')->willReturn(UPLOAD_ERR_INI_SIZE);
        $this->assertErrors(['file' => 'The file is too big (3.1 MB), maximum allowed size is 2 MB'], $form->submit(['file' => $file])->errors());


        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);

        $this->assertTrue($form->submit(['file' => $file])->valid());
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function test_functional_file_size_limit(bool $generated)
    {
        $form = $generated ? $this->generatedForm(UploadFormTest::class) : $this->runtimeForm(UploadFormTest::class);

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_PARTIAL);
        $this->assertErrors(['withSizeLimit' => 'The upload has failed'], $form->submit(['withSizeLimit' => $file])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->method('getSize')->willReturn(3256984);
        $this->assertErrors(['withSizeLimit' => 'The file is too big (3.1 MB), maximum allowed size is 5 kB'], $form->submit(['withSizeLimit' => $file])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $this->assertErrors(['withSizeLimit' => 'The upload has failed'], $form->submit(['withSizeLimit' => $file])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->method('getSize')->willReturn(1254);
        $this->assertTrue($form->submit(['withSizeLimit' => $file])->valid());
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function test_functional_file_extension(bool $generated)
    {
        $form = $generated ? $this->generatedForm(UploadFormTest::class) : $this->runtimeForm(UploadFormTest::class);

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_PARTIAL);
        $this->assertErrors(['withExtension' => 'The upload has failed'], $form->submit(['withExtension' => $file])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->method('getClientFilename')->willReturn('test.txt');
        $this->assertErrors(['withExtension' => 'The file extension "txt" is not allowed, allowed extensions are jpg, png'], $form->submit(['withExtension' => $file])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->method('getClientFilename')->willReturn('testjpg');
        $this->assertErrors(['withExtension' => 'The file extension "" is not allowed, allowed extensions are jpg, png'], $form->submit(['withExtension' => $file])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $this->assertErrors(['withExtension' => 'The upload has failed'], $form->submit(['withExtension' => $file])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->method('getClientFilename')->willReturn('test.jpg');
        $this->assertTrue($form->submit(['withExtension' => $file])->valid());
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function test_functional_mime_type(bool $generated)
    {
        $form = $generated ? $this->generatedForm(UploadFormTest::class) : $this->runtimeForm(UploadFormTest::class);

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_PARTIAL);
        $this->assertErrors(['withMimeType' => 'The upload has failed'], $form->submit(['withMimeType' => $file])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->method('getClientMediaType')->willReturn('text/plain');
        $this->assertErrors(['withMimeType' => 'The file mime type "text/plain" is not allowed, allowed mime types are image/*, application/pdf'], $form->submit(['withMimeType' => $file])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $this->assertErrors(['withMimeType' => 'The upload has failed'], $form->submit(['withMimeType' => $file])->errors());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->method('getClientMediaType')->willReturn('image/jpeg');
        $this->assertTrue($form->submit(['withMimeType' => $file])->valid());

        $file = $this->createMock(UploadedFileInterface::class);
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->method('getClientMediaType')->willReturn('application/pdf');
        $this->assertTrue($form->submit(['withMimeType' => $file])->valid());
    }

    public function test_generate()
    {
        $this->assertGeneratedValidator('($data->foo ?? null) === null ? null : (($data->foo ?? null) instanceof \Psr\Http\Message\UploadedFileInterface ? null : new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [], \'7999bac5-3960-5af1-8d08-65153904c14a\')) ?? match($__tmpUploadError = ($data->foo ?? null)->getError()) {UPLOAD_ERR_OK => null,UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => (($data->foo ?? null)->getSize() === null ? new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [], \'7999bac5-3960-5af1-8d08-65153904c14a\') : new \Quatrevieux\Form\Validator\FieldError(\'The file is too big ({{ current_size }}), maximum allowed size is {{ max_size }}\', [\'current_size_bytes\' => ($data->foo ?? null)->getSize(), \'max_size_bytes\' => 2097152, \'current_size\' => \Quatrevieux\Form\Util\FileSize::format(($data->foo ?? null)->getSize()), \'max_size\' => \'2 MB\'], \'7999bac5-3960-5af1-8d08-65153904c14a\')),default => new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [\'error\' => $__tmpUploadError], \'7999bac5-3960-5af1-8d08-65153904c14a\')}', new UploadedFile());
        $this->assertGeneratedValidator('($data->foo ?? null) === null ? null : (($data->foo ?? null) instanceof \Psr\Http\Message\UploadedFileInterface ? null : new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [], \'7999bac5-3960-5af1-8d08-65153904c14a\')) ?? match($__tmpUploadError = ($data->foo ?? null)->getError()) {UPLOAD_ERR_OK => null,UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => (($data->foo ?? null)->getSize() === null ? new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [], \'7999bac5-3960-5af1-8d08-65153904c14a\') : new \Quatrevieux\Form\Validator\FieldError(\'The file is too big ({{ current_size }}), maximum allowed size is {{ max_size }}\', [\'current_size_bytes\' => ($data->foo ?? null)->getSize(), \'max_size_bytes\' => 1234, \'current_size\' => \Quatrevieux\Form\Util\FileSize::format(($data->foo ?? null)->getSize()), \'max_size\' => \'1.2 kB\'], \'7999bac5-3960-5af1-8d08-65153904c14a\')),default => new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [\'error\' => $__tmpUploadError], \'7999bac5-3960-5af1-8d08-65153904c14a\')} ?? (($data->foo ?? null)->getSize() !== null ? null : new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [], \'7999bac5-3960-5af1-8d08-65153904c14a\')) ?? (($data->foo ?? null)->getSize() <= 1234 ? null : new \Quatrevieux\Form\Validator\FieldError(\'The file is too big ({{ current_size }}), maximum allowed size is {{ max_size }}\', [\'current_size_bytes\' => ($data->foo ?? null)->getSize(), \'max_size_bytes\' => 1234, \'current_size\' => \Quatrevieux\Form\Util\FileSize::format(($data->foo ?? null)->getSize()), \'max_size\' => \'1.2 kB\'], \'7999bac5-3960-5af1-8d08-65153904c14a\'))', new UploadedFile(maxSize: 1234));
        $this->assertGeneratedValidator('($data->foo ?? null) === null ? null : (($data->foo ?? null) instanceof \Psr\Http\Message\UploadedFileInterface ? null : new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [], \'7999bac5-3960-5af1-8d08-65153904c14a\')) ?? match($__tmpUploadError = ($data->foo ?? null)->getError()) {UPLOAD_ERR_OK => null,UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => (($data->foo ?? null)->getSize() === null ? new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [], \'7999bac5-3960-5af1-8d08-65153904c14a\') : new \Quatrevieux\Form\Validator\FieldError(\'The file is too big ({{ current_size }}), maximum allowed size is {{ max_size }}\', [\'current_size_bytes\' => ($data->foo ?? null)->getSize(), \'max_size_bytes\' => 2097152, \'current_size\' => \Quatrevieux\Form\Util\FileSize::format(($data->foo ?? null)->getSize()), \'max_size\' => \'2 MB\'], \'7999bac5-3960-5af1-8d08-65153904c14a\')),default => new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [\'error\' => $__tmpUploadError], \'7999bac5-3960-5af1-8d08-65153904c14a\')} ?? (($__tmpFileName = ($data->foo ?? null)->getClientFilename()) !== null ? null : new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [], \'7999bac5-3960-5af1-8d08-65153904c14a\')) ?? ((str_ends_with($__tmpFileName, \'.png\') || str_ends_with($__tmpFileName, \'.gif\')) ? null : new \Quatrevieux\Form\Validator\FieldError(\'The file extension "{{ current_extension }}" is not allowed, allowed extensions are {{ allowed_extensions }}\', [\'current_extension\' => pathinfo(($data->foo ?? null)->getClientFilename(), 4), \'allowed_extensions\' => \'png, gif\'], \'7999bac5-3960-5af1-8d08-65153904c14a\'))', new UploadedFile(allowedExtensions: ['png', 'gif']));
        $this->assertGeneratedValidator('($data->foo ?? null) === null ? null : (($data->foo ?? null) instanceof \Psr\Http\Message\UploadedFileInterface ? null : new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [], \'7999bac5-3960-5af1-8d08-65153904c14a\')) ?? match($__tmpUploadError = ($data->foo ?? null)->getError()) {UPLOAD_ERR_OK => null,UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => (($data->foo ?? null)->getSize() === null ? new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [], \'7999bac5-3960-5af1-8d08-65153904c14a\') : new \Quatrevieux\Form\Validator\FieldError(\'The file is too big ({{ current_size }}), maximum allowed size is {{ max_size }}\', [\'current_size_bytes\' => ($data->foo ?? null)->getSize(), \'max_size_bytes\' => 2097152, \'current_size\' => \Quatrevieux\Form\Util\FileSize::format(($data->foo ?? null)->getSize()), \'max_size\' => \'2 MB\'], \'7999bac5-3960-5af1-8d08-65153904c14a\')),default => new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [\'error\' => $__tmpUploadError], \'7999bac5-3960-5af1-8d08-65153904c14a\')} ?? (($__tmpFileMimeType = ($data->foo ?? null)->getClientMediaType()) !== null ? null : new \Quatrevieux\Form\Validator\FieldError(\'The upload has failed\', [], \'7999bac5-3960-5af1-8d08-65153904c14a\')) ?? ((str_starts_with($__tmpFileMimeType, \'image/\') || $__tmpFileMimeType === \'application/pdf\') ? null : new \Quatrevieux\Form\Validator\FieldError(\'The file mime type "{{ current_mime_type }}" is not allowed, allowed mime types are {{ allowed_mime_types }}\', [\'current_mime_type\' => ($data->foo ?? null)->getClientMediaType(), \'allowed_mime_types\' => \'image/*, application/pdf\'], \'7999bac5-3960-5af1-8d08-65153904c14a\'))', new UploadedFile(allowedMimeTypes: ['image/*', 'application/pdf']));
    }
}

class UploadFormTest
{
    #[UploadedFile]
    public $file;

    #[UploadedFile(maxSize: 5 * 1024)]
    public ?UploadedFileInterface $withSizeLimit;

    #[UploadedFile(allowedExtensions: ['jpg', 'png'])]
    public ?UploadedFileInterface $withExtension;

    #[UploadedFile(allowedMimeTypes: ['image/*', 'application/pdf'])]
    public ?UploadedFileInterface $withMimeType;
}
