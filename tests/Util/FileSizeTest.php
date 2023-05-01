<?php

namespace Quatrevieux\Form\Util;

use PHPUnit\Framework\TestCase;

class FileSizeTest extends TestCase
{
    public function test_parseIniNotation()
    {
        $this->assertEquals(1423, FileSize::parseIniNotation('1423'));
        $this->assertEquals(2048, FileSize::parseIniNotation('2K'));
        $this->assertEquals(1024, FileSize::parseIniNotation('1k'));
        $this->assertEquals(3145728, FileSize::parseIniNotation('3M'));
        $this->assertEquals(1073741824, FileSize::parseIniNotation('1G'));
    }

    public function test_parseIniNotion_invalid_should_throw_invalid_argument()
    {
        $this->expectException(\InvalidArgumentException::class);
        FileSize::parseIniNotation('invalid');
    }

    public function test_parseIniNotion_unsupported_unit_should_throw_invalid_argument()
    {
        $this->expectException(\InvalidArgumentException::class);
        FileSize::parseIniNotation('1T');
    }

    public function test_format()
    {
        $this->assertSame('123 B', FileSize::format(123));
        $this->assertSame('1 kB', FileSize::format(1024));
        $this->assertSame('2.5 kB', FileSize::format(2548));
        $this->assertSame('12 MB', FileSize::format(12547896));
        $this->assertSame('1.2 GB', FileSize::format(1254789654));
        $this->assertSame('1.1 TB', FileSize::format(1254789654123));
        $this->assertSame('11412.2 TB', FileSize::format(12547896541234587));
    }

    public function test_maxUploadFileSize()
    {
        $this->assertSame(PHP_INT_MAX, $this->execMaxUploadFileSize(['upload_max_filesize' => '', 'post_max_size' => '']));
        $this->assertSame(2097152, $this->execMaxUploadFileSize(['upload_max_filesize' => '', 'post_max_size' => '2M']));
        $this->assertSame(2097152, $this->execMaxUploadFileSize(['upload_max_filesize' => '2M', 'post_max_size' => '']));
        $this->assertSame(512000, $this->execMaxUploadFileSize(['upload_max_filesize' => '500k', 'post_max_size' => '2M']));
        $this->assertSame(204800, $this->execMaxUploadFileSize(['upload_max_filesize' => '4M', 'post_max_size' => '200k']));
    }

    private function execMaxUploadFileSize(array $config = []): int
    {
        $autoload = __DIR__ . '/../../vendor/autoload.php';
        $options = '';

        foreach ($config as $key => $value) {
            $options .= ' -d ' . escapeshellarg($key . '=' . $value);
        }

        $options .= ' -r ' . escapeshellarg('require ' . var_export($autoload, true) . '; echo  ' . FileSize::class . '::maxUploadFileSize();');

        return (int) shell_exec(PHP_BINARY . $options);
    }
}
