<?php

namespace Ninjajah\Encryption;

use GuzzleHttp\Psr7\Stream;
use Jsq\EncryptionStreams\Cbc;
use Psr\Http\Message\StreamInterface;
use Jsq\EncryptionStreams\AesEncryptingStream;
use Jsq\EncryptionStreams\AesDecryptingStream;
use Jsq\EncryptionStreams\HashingStream;
use Jsq\EncryptionStreams\HmacStream;
use GuzzleHttp\Psr7\Utils;
use Jsq\EncryptionStreams\CipherMethod;

class WhatsAppMediaEncryption
{
    private const HKDF_INFO = [
        'IMAGE' => 'WhatsApp Image Keys',
        'VIDEO' => 'WhatsApp Video Keys',
        'AUDIO' => 'WhatsApp Audio Keys',
        'DOCUMENT' => 'WhatsApp Document Keys',
    ];

    private const BLOCK_SIZE = 16;
    private const MAC_LENGTH = 10;
    private const CHUNK_SIZE = 64 * 1024; // 64KB

    public function encrypt(Stream $stream, string $mediaKey, string $mediaType): StreamInterface
    {
        // Step 1: Expand the mediaKey
        $mediaKeyExpanded = $this->expandMediaKey($mediaKey, $mediaType);

        // Step 2: Split the expanded key
        $iv = substr($mediaKeyExpanded, 0, 16);
        $cipherKey = substr($mediaKeyExpanded, 16, 32);
        $macKey = substr($mediaKeyExpanded, 48, 32);
        $cipherMethod = new Cbc($iv);

        // Step 3: Encrypt the stream
        $encryptedStream = new AesEncryptingStream(
            $stream,
            $cipherKey,
            $cipherMethod
        );

        // Step 4: Read the encrypted content
        $encryptedContent = $encryptedStream->getContents();

        // Step 5: Sign `iv + enc` with `macKey` using HMAC SHA-256
        $mac = substr(hash_hmac('sha256', $iv . $encryptedContent, $macKey, true), 0, self::MAC_LENGTH);

        // Step 6: Append `mac` to the `enc`
        $finalStream = new Stream(fopen('php://memory', 'r+'));
        $finalStream->write($encryptedContent . $mac);
        $finalStream->rewind();

        return $finalStream;
    }


    public function decrypt(StreamInterface $stream, string $mediaKey, string $mediaType): StreamInterface
    {
        // Step 1: Expand the mediaKey
        $mediaKeyExpanded = $this->expandMediaKey($mediaKey, $mediaType);

        // Step 2: Split the expanded key
        $iv = substr($mediaKeyExpanded, 0, 16);
        $cipherKey = substr($mediaKeyExpanded, 16, 32);
        $macKey = substr($mediaKeyExpanded, 48, 32);
        $cipherMethod = new Cbc($iv);

        // Step 3: Read the encrypted content and mac
        $encryptedContent = $stream->getContents();
        $encLength = strlen($encryptedContent) - self::MAC_LENGTH;
        $enc = substr($encryptedContent, 0, $encLength);
        $mac = substr($encryptedContent, $encLength);

        // Step 4: Validate media data with HMAC
        $calculatedMac = substr(hash_hmac('sha256', $iv . $enc, $macKey, true), 0, self::MAC_LENGTH);
        if (!hash_equals($calculatedMac, $mac)) {
            throw new \Exception('MAC validation failed');
        }

        // Step 5: Decrypt the stream
        $decryptedStream = new AesDecryptingStream(
            new Stream(fopen('php://memory', 'r+')),
            $cipherKey,
            $cipherMethod
        );
        $decryptedStream->write($enc);
        $decryptedStream->rewind();

        return $decryptedStream;
    }

    private function expandMediaKey(string $mediaKey, string $mediaType): string
    {
        $info = self::HKDF_INFO[$mediaType] ?? '';
        return hash_hkdf('sha256', $mediaKey, 112, $info, '');
    }

    public function generateSidecar(StreamInterface $stream, string $mediaKey, string $mediaType): string
    {
        $mediaKeyExpanded = $this->expandMediaKey($mediaKey, $mediaType);
        $macKey = substr($mediaKeyExpanded, 48, 32);

        $sidecar = '';
        $stream->rewind();

        $n = 0;
        while (!$stream->eof()) {
            $chunk = $stream->read(self::CHUNK_SIZE + self::BLOCK_SIZE);
            $sidecar .= substr(hash_hmac('sha256', $chunk, $macKey, true), 0, self::MAC_LENGTH);
            $n++;
        }

        return $sidecar;
    }
}
