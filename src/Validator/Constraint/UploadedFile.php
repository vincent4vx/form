<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Psr\Http\Message\UploadedFileInterface;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\FileSize;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Quatrevieux\Form\View\Provider\FieldViewAttributesProviderInterface;

use function array_map;
use function implode;
use function pathinfo;
use function str_ends_with;
use function str_starts_with;
use function substr;

/**
 * Check if the uploaded file is valid
 * Use PSR-7 UploadedFileInterface, so "psr/http-message" package is required to use this constraint
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     // Constraint for simply check that the file is successfully uploaded
 *     #[UploadedFile]
 *     public UploadedFileInterface $file;
 *
 *     // You can also define a file size limit
 *     #[UploadedFile(maxSize: 1024 * 1024)]
 *     public UploadedFileInterface $withLimit;
 *
 *     // You can also define the file type using mime type filter, or file extension filter
 *     // Note: this is not a security feature, you should always check the actual file type on the server side
 *     #[UploadedFile(
 *         allowedMimeTypes: ['image/*', 'application/pdf'], // wildcard is allowed for subtype
 *         allowedExtensions: ['jpg', 'png', 'pdf'] // you can specify the file extension (without the dot)
 *     )]
 *     public UploadedFileInterface $withMimeTypes;
 * }
 *
 * // Create and submit the form
 * // Here, $request is a PSR-7 ServerRequestInterface
 * $form = $factory->create(MyForm::class);
 * $submitted = $form->submit($request->getParsedBody() + $request->getUploadedFiles());
 * </code>
 *
 * @implements ConstraintValidatorGeneratorInterface<UploadedFile>
 */
#[\Attribute(Attribute::TARGET_PROPERTY)]
final class UploadedFile extends SelfValidatedConstraint implements FieldViewAttributesProviderInterface, ConstraintValidatorGeneratorInterface
{
    public const CODE = '7999bac5-3960-5af1-8d08-65153904c14a';

    public function __construct(
        /**
         * Maximum file size in bytes
         * If defined, files with unknown size will be rejected
         *
         * Note: ensure that upload_max_filesize and post_max_size are greater than this value
         *
         * @see UploadedFileInterface::getSize()
         * @see UploadedFile::$messageFileTooBig for error message
         * @see UploadedFile::$messageUploadedFailed when the file size is unknown
         */
        public readonly ?int $maxSize = null,

        /**
         * List of allowed mime types
         * You can use wildcards on subtype, like "image/*"
         *
         * Note: this is not a security feature, you should always check the actual file type on the server side
         *
         * @var list<string>|null
         *
         * @see UploadedFileInterface::getClientMediaType()
         * @see UploadedFile::$messageInvalidMimeType for error message
         * @see UploadedFile::$messageUploadedFailed when the mime type is unknown
         */
        public readonly ?array $allowedMimeTypes = null,

        /**
         * List of allowed file extensions
         * The dot must not be included
         *
         * The extension is extracted from uploaded file name
         *
         * @var list<string>|null
         *
         * @see UploadedFileInterface::getClientFilename()
         * @see UploadedFile::$messageInvalidExtension for error message
         * @see UploadedFile::$messageUploadedFailed when the extension is unknown
         */
        public readonly ?array $allowedExtensions = null,

        /**
         * Generic error message when the upload has failed
         * The parameter "error" is used to determine the error type, see UPLOAD_ERR_XXX constants
         */
        public readonly string $messageUploadedFailed = 'The upload has failed',

        /**
         * Error message when the file size is too big
         * This error can occur when file size is higher than {@see UploadedFile::$maxSize} or PHP limits
         *
         * Available parameters:
         * - current_size: the current file size formatted with unit (e.g. 1.5 MB)
         * - max_size: the maximum allowed file size formatted with unit (e.g. 1.5 MB)
         * - current_size_bytes: the current file size in bytes
         * - max_size_bytes: the maximum allowed file size in bytes
         *
         * @see UploadedFile::$maxSize
         * @see FileSize::format() for formatting
         */
        public readonly string $messageFileTooBig = 'The file is too big ({{ current_size }}), maximum allowed size is {{ max_size }}',

        /**
         * Error message when the file extension is not allowed
         *
         * Available parameters:
         * - current_extension: the current file extension
         * - allowed_extensions: the list of allowed extensions, separated by comma
         */
        public readonly string $messageInvalidExtension = 'The file extension "{{ current_extension }}" is not allowed, allowed extensions are {{ allowed_extensions }}',

        /**
         * Error message when the mime type is not allowed
         *
         * Available parameters:
         * - current_mime_type: the current file mime type
         * - allowed_mime_types: the list of allowed mime types, separated by comma
         */
        public readonly string $messageInvalidMimeType = 'The file mime type "{{ current_mime_type }}" is not allowed, allowed mime types are {{ allowed_mime_types }}',
    ) {}

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof UploadedFileInterface) {
            return new FieldError($this->messageUploadedFailed, code: self::CODE);
        }

        if ($value->getError() !== UPLOAD_ERR_OK) {
            return $this->uploadError($value);
        }

        if (!$this->checkFilesize($value)) {
            return $this->fileSizeError($value->getSize());
        }

        if (!$this->checkExtension($value)) {
            return $this->invalidExtensionError($value->getClientFilename());
        }

        if (!$this->checkMimeType($value)) {
            return $this->invalidMimeTypeError($value->getClientMediaType());
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        $attributes = ['type' => 'file'];

        $accept = [];

        if ($this->allowedMimeTypes) {
            $accept[] = implode(',', $this->allowedMimeTypes);
        }

        if ($this->allowedExtensions) {
            $extensions = array_map(
                fn($ext) => '.' . $ext,
                $this->allowedExtensions,
            );
            $accept[] = implode(',', $extensions);
        }

        if ($accept) {
            $attributes['accept'] = implode(',', $accept);
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        return FieldErrorExpression::single(function (string $accessor) use ($constraint) {
            $accessor = Code::expr($accessor);
            $maxSize = $constraint->maxSize ?? FileSize::maxUploadFileSize();
            $genericError = Code::new(FieldError::class, [$constraint->messageUploadedFailed, [], self::CODE]);
            $fileSizeError = Code::new(FieldError::class, [
                $constraint->messageFileTooBig,
                [
                    'current_size_bytes' => $accessor->getSize(),
                    'max_size_bytes' => $maxSize,
                    'current_size' => Code::raw(Call::static(FileSize::class)->format($accessor->getSize())),
                    'max_size' => FileSize::format($maxSize),
                ],
                self::CODE,
            ]);

            $checks = [];

            $checks[] = '(' . $accessor . ' instanceof \\' . UploadedFileInterface::class . ' ? null : ' . $genericError . ')';
            $checks[] = 'match($__tmpUploadError = ' . $accessor . '->getError()) {'
                . 'UPLOAD_ERR_OK => null,'
                . "UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => ({$accessor}->getSize() === null ? {$genericError} : {$fileSizeError}),"
                . 'default => ' . Code::new(FieldError::class, [$constraint->messageUploadedFailed, ['error' => Code::raw('$__tmpUploadError')], self::CODE])
            . '}';

            if ($constraint->maxSize !== null) {
                $checks[] = "({$accessor}->getSize() !== null ? null : {$genericError})";
                $checks[] = "({$accessor}->getSize() <= {$maxSize} ? null : {$fileSizeError})";
            }

            if ($constraint->allowedExtensions !== null) {
                $extensionError = Code::new(FieldError::class, [
                    $constraint->messageInvalidExtension,
                    [
                        'current_extension' => Code::raw(Call::pathinfo($accessor->getClientFilename(), PATHINFO_EXTENSION)),
                        'allowed_extensions' => implode(', ', $constraint->allowedExtensions),
                    ],
                    self::CODE,
                ]);
                $checkExtensionExpression  = array_map(
                    fn($ext) => Call::str_ends_with(Code::raw('$__tmpFileName'), '.' . $ext),
                    $constraint->allowedExtensions,
                );
                $checkExtensionExpression = implode(' || ', $checkExtensionExpression);

                $checks[] = "((\$__tmpFileName = {$accessor}->getClientFilename()) !== null ? null : {$genericError})";
                $checks[] = "(({$checkExtensionExpression}) ? null : {$extensionError})";
            }

            if ($constraint->allowedMimeTypes !== null) {
                $mimeTypeError = Code::new(FieldError::class, [
                    $constraint->messageInvalidMimeType,
                    [
                        'current_mime_type' => $accessor->getClientMediaType(),
                        'allowed_mime_types' => implode(', ', $constraint->allowedMimeTypes),
                    ],
                    self::CODE,
                ]);
                $checkMimeTypeExpression = array_map(
                    function ($mime) {
                        if (str_ends_with($mime, '/*')) {
                            return Call::str_starts_with(Code::raw('$__tmpFileMimeType'), substr($mime, 0, -1));
                        }

                        return '$__tmpFileMimeType === ' . Code::value($mime);
                    },
                    $constraint->allowedMimeTypes,
                );
                $checkMimeTypeExpression = implode(' || ', $checkMimeTypeExpression);

                $checks[] = "((\$__tmpFileMimeType = {$accessor}->getClientMediaType()) !== null ? null : {$genericError})";
                $checks[] = "(({$checkMimeTypeExpression}) ? null : {$mimeTypeError})";
            }

            return $accessor . ' === null ? null : ' . implode(' ?? ', $checks);
        });
    }

    private function checkExtension(UploadedFileInterface $file): bool
    {
        if ($this->allowedExtensions === null) {
            return true;
        }

        $filename = $file->getClientFilename();

        if ($filename === null) {
            return false;
        }

        foreach ($this->allowedExtensions as $ext) {
            if (str_ends_with($filename, '.' . $ext)) {
                return true;
            }
        }

        return false;
    }

    private function checkMimeType(UploadedFileInterface $file): bool
    {
        if ($this->allowedMimeTypes === null) {
            return true;
        }

        $mime = $file->getClientMediaType();

        if ($mime === null) {
            return false;
        }

        foreach ($this->allowedMimeTypes as $allowed) {
            if ($allowed === $mime) {
                return true;
            }

            if (str_ends_with($allowed, '/*')) {
                $allowed = substr($allowed, 0, -2);
                if (str_starts_with($mime, $allowed)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function checkFilesize(UploadedFileInterface $file): bool
    {
        if ($this->maxSize === null) {
            return true;
        }

        if ($file->getSize() === null) {
            return false;
        }

        return $file->getSize() <= $this->maxSize;
    }

    private function uploadError(UploadedFileInterface $file): FieldError
    {
        $error = $file->getError();

        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            return $this->fileSizeError($file->getSize());
        }

        return new FieldError($this->messageUploadedFailed, ['error' => $error], code: self::CODE);
    }

    private function fileSizeError(?int $currentSize): FieldError
    {
        if ($currentSize === null) {
            return new FieldError($this->messageUploadedFailed, code: self::CODE);
        }

        $maxSize = $this->maxSize ?? FileSize::maxUploadFileSize();

        return new FieldError(
            $this->messageFileTooBig,
            [
                'current_size_bytes' => $currentSize,
                'max_size_bytes' => $maxSize,
                'current_size' => FileSize::format($currentSize),
                'max_size' => FileSize::format($maxSize),
            ],
            self::CODE,
        );
    }

    private function invalidExtensionError(?string $filename): FieldError
    {
        if ($filename === null) {
            return new FieldError($this->messageUploadedFailed, code: self::CODE);
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return new FieldError(
            $this->messageInvalidExtension,
            [
                'current_extension' => $extension,
                'allowed_extensions' => implode(', ', $this->allowedExtensions),
            ],
            self::CODE,
        );
    }

    private function invalidMimeTypeError(?string $mime): FieldError
    {
        if ($mime === null) {
            return new FieldError($this->messageUploadedFailed, code: self::CODE);
        }

        return new FieldError(
            $this->messageInvalidMimeType,
            [
                'current_mime_type' => $mime,
                'allowed_mime_types' => implode(', ', $this->allowedMimeTypes),
            ],
            self::CODE,
        );
    }
}
