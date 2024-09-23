<?php
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Psr7\Stream;
use Ninjajah\Encryption\WhatsAppMediaEncryption;

$encryption = new WhatsAppMediaEncryption();
$mediaKey = file_get_contents(__DIR__ . '/samples/IMAGE.key');

/**
 * IMAGE TEST #1
 **/

$originalContent = file_get_contents(__DIR__ . '/samples/IMAGE.original', 'r+');
$originalStream = new Stream(fopen('php://memory', 'r+'));
$originalStream->write($originalContent);
$originalStream->rewind();

$encryptedStream = $encryption->encrypt($originalStream, $mediaKey, 'IMAGE');
$decryptedStream = $encryption->decrypt($encryptedStream, $mediaKey, 'IMAGE');
$decryptedContent = $decryptedStream->getContents();

var_dump($originalContent == $decryptedContent);


/**
 * IMAGE TEST #2
 **/

$originalContent = file_get_contents(__DIR__ . '/samples/IMAGE.original', 'r+');
$originalStream = new Stream(fopen('php://memory', 'r+'));
$originalStream->write($originalContent);
$originalStream->rewind();

$encryptedContent = file_get_contents(__DIR__ . '/samples/IMAGE.encrypted', 'r+');
$encryptedStream = new Stream(fopen('php://memory', 'r+'));
$encryptedStream->write($encryptedContent);
$encryptedStream->rewind();

$decryptedStream = $encryption->decrypt($encryptedStream, $mediaKey, 'IMAGE');

$decryptedContent = $decryptedStream->getContents();

var_dump($originalContent == $decryptedContent);


$mediaKey = file_get_contents(__DIR__ . '/samples/VIDEO.key');

/**
 * VIDEO TEST #1
 **/

$originalContent = file_get_contents(__DIR__ . '/samples/VIDEO.original', 'r+');
$originalStream = new Stream(fopen('php://memory', 'r+'));
$originalStream->write($originalContent);
$originalStream->rewind();

$encryptedStream = $encryption->encrypt($originalStream, $mediaKey, 'VIDEO');
$decryptedStream = $encryption->decrypt($encryptedStream, $mediaKey, 'VIDEO');
$decryptedContent = $decryptedStream->getContents();

var_dump($originalContent == $decryptedContent);

/**
 * VIDEO TEST #2
 **/

$originalContent = file_get_contents(__DIR__ . '/samples/VIDEO.original', 'r+');
$originalStream = new Stream(fopen('php://memory', 'r+'));
$originalStream->write($originalContent);
$originalStream->rewind();

$encryptedContent = file_get_contents(__DIR__ . '/samples/VIDEO.encrypted', 'r+');
$encryptedStream = new Stream(fopen('php://memory', 'r+'));
$encryptedStream->write($encryptedContent);
$encryptedStream->rewind();

$decryptedStream = $encryption->decrypt($encryptedStream, $mediaKey, 'VIDEO');

$decryptedContent = $decryptedStream->getContents();
var_dump($originalContent == $decryptedContent);
