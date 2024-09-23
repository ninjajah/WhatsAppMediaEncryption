# WhatsApp Media Encryption for PSR-7 Streams

This package provides a robust and industrial-quality implementation for encrypting and decrypting PSR-7 streams using the algorithms used by WhatsApp. It includes decorators for encryption, decryption, and hashing, and is designed to handle both full-file and streaming encryption/decryption.

## Features

- **Encryption**: Encrypt PSR-7 streams using AES-CBC and HMAC SHA-256.
- **Decryption**: Decrypt PSR-7 streams using AES-CBC and validate with HMAC SHA-256.
- **Sidecar Generation**: Generate sidecar files for streamable media (e.g., video and audio).
- **Type-Specific Application Info**: Supports different media types (IMAGE, VIDEO, AUDIO, DOCUMENT) with type-specific application info for HKDF.
