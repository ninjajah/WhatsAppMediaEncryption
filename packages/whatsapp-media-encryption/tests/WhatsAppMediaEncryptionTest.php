<?php

namespace Ninjajah\Encryption\Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Stream;
use Ninjajah\Encryption\WhatsAppMediaEncryption;

class WhatsAppMediaEncryptionTest extends TestCase
{
    private $encryption;

    protected function setUp(): void
    {
        $this->encryption = new WhatsAppMediaEncryption();
    }

    public function testEncryptDecrypt()
    {
        $originalContent = file_get_contents(__DIR__ . '/../samples/IMAGE.original');
        $mediaKey = file_get_contents(__DIR__ . '/../samples/IMAGE.key');

        $originalStream = new Stream(fopen('php://memory', 'r+'));
        $originalStream->write($originalContent);
        $originalStream->rewind();

        $encryptedStream = $this->encryption->encrypt($originalStream, $mediaKey, 'IMAGE');
        $decryptedStream = $this->encryption->decrypt($encryptedStream, $mediaKey, 'IMAGE');

        $decryptedContent = $decryptedStream->getContents();
        $this->assertEquals($originalContent, $decryptedContent);
    }

    public function testEncryptImage()
    {
        $originalContent = file_get_contents(__DIR__ . '/../samples/IMAGE.original');
        $mediaKey = file_get_contents(__DIR__ . '/../samples/IMAGE.key');

        $originalStream = new Stream(fopen('php://memory', 'r+'));
        $originalStream->write($originalContent);
        $originalStream->rewind();

        $encryptedContent = file_get_contents(__DIR__ . '/../samples/IMAGE.encrypted', 'r+');
        $encryptedStream = new Stream(fopen('php://memory', 'r+'));
        $encryptedStream->write($encryptedContent);
        $encryptedStream->rewind();

        $decryptedStream = $this->encryption->decrypt($encryptedStream, $mediaKey, 'IMAGE');

        $decryptedContent = $decryptedStream->getContents();

        $this->assertEquals($originalContent, $decryptedContent);
    }

    public function testEncryptVideo()
    {

        $mediaKey = file_get_contents(__DIR__ . '/../samples/VIDEO.key');

        $originalContent = file_get_contents(__DIR__ . '/../samples/VIDEO.original', 'r+');
        $originalStream = new Stream(fopen('php://memory', 'r+'));
        $originalStream->write($originalContent);
        $originalStream->rewind();

        $encryptedContent = file_get_contents(__DIR__ . '/../samples/VIDEO.encrypted', 'r+');
        $encryptedStream = new Stream(fopen('php://memory', 'r+'));
        $encryptedStream->write($encryptedContent);
        $encryptedStream->rewind();

        $decryptedStream = $this->encryption->decrypt($encryptedStream, $mediaKey, 'VIDEO');

        $decryptedContent = $decryptedStream->getContents();

        $this->assertEquals($originalContent, $decryptedContent);
    }

    public function testGenerateSidecar()
    {
        $mediaKey = file_get_contents(__DIR__ . '/../samples/VIDEO.key');

        $originalContent = file_get_contents(__DIR__ . '/../samples/VIDEO.original');
        $originalStream = new Stream(fopen('php://memory', 'r+'));
        $originalStream->write($originalContent);
        $originalStream->rewind();

        $sidecar = $this->encryption->generateSidecar($originalStream, $mediaKey, 'VIDEO');
        $expectedSidecar = file_get_contents(__DIR__ . '/../samples/VIDEO.sidecar');

        $this->assertEquals($expectedSidecar, $sidecar);
    }
}
