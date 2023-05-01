<?php

namespace Quatrevieux\Form\Util;

use InvalidArgumentException;

use function count;
use function is_numeric;
use function round;
use function strtolower;
use function substr;

/**
 * Utility class for parse file size
 */
final class FileSize
{
    /**
     * Try to parse a file size in the php ini notation to bytes
     *
     * The value can be :
     * - a number (in bytes)
     * - an integer followed by a unit (K, M, G). The unit is case-insensitive
     *
     * @param non-empty-string $value ini value to parse
     *
     * @return int file size in bytes
     *
     * @see https://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
     */
    public static function parseIniNotation(string $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        $unit = strtolower($value[-1]);
        $value = (int) substr($value, 0, -1);

        return match ($unit) {
            'k' => $value * 1024,
            'm' => $value * 1024 * 1024,
            'g' => $value * 1024 * 1024 * 1024,
            default => throw new InvalidArgumentException('Invalid file size unit'),
        };
    }

    /**
     * Get the upload file size limit from ini configuration
     * The returned value is the minimum between post_max_size and upload_max_filesize
     *
     * @return int max upload file size in bytes
     */
    public static function maxUploadFileSize(): int
    {
        $maxSize = PHP_INT_MAX;

        if ($post = ini_get('post_max_size')) {
            $maxSize = self::parseIniNotation($post);
        }

        if ($upload = ini_get('upload_max_filesize')) {
            $upload = self::parseIniNotation($upload);

            if ($upload < $maxSize) {
                $maxSize = $upload;
            }
        }

        return $maxSize;
    }

    /**
     * Format a file size in bytes to a human-readable string
     * The returned size is rounded to 1 decimal
     *
     * @param int $size file size in bytes
     *
     * @return string human-readable file size, with unit (B, kB, MB, GB, TB)
     */
    public static function format(int $size): string
    {
        $units = ['B', 'kB', 'MB', 'GB', 'TB'];
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 1) . ' ' . $units[$unit];
    }
}
