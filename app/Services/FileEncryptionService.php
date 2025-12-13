<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileEncryptionService
{
    /**
     * Encrypt and save a file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @return array [file_path, encryption_key, encryption_iv]
     */
    public function encryptAndStore($file, $directory = 'documents')
    {
        // Read file content
        $fileContent = file_get_contents($file->getRealPath());
        
        // Generate encryption key and IV
        $encryptionKey = random_bytes(32); // 256 bits
        $iv = random_bytes(16); // 128 bits
        
        // Encrypt the file content
        $encryptedContent = openssl_encrypt(
            $fileContent,
            'AES-256-CBC',
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        // Generate unique filename
        $filename = uniqid('doc_') . '_' . time() . '.enc';
        $path = $directory . '/' . $filename;
        
        // Store encrypted file
        Storage::disk('local')->put($path, $encryptedContent);
        
        // Return file path and encryption credentials (encoded for storage)
        return [
            'file_path' => $path,
            'encryption_key' => base64_encode($encryptionKey),
            'encryption_iv' => base64_encode($iv),
        ];
    }

    /**
     * Decrypt and return file content
     *
     * @param string $filePath
     * @param string $encryptionKey (base64 encoded)
     * @param string $iv (base64 encoded)
     * @return string|false
     */
    public function decryptFile($filePath, $encryptionKey, $iv)
    {
        // Get encrypted content
        if (!Storage::disk('local')->exists($filePath)) {
            return false;
        }
        
        $encryptedContent = Storage::disk('local')->get($filePath);
        
        // Decode encryption credentials
        $key = base64_decode($encryptionKey);
        $ivDecoded = base64_decode($iv);
        
        // Decrypt content
        $decryptedContent = openssl_decrypt(
            $encryptedContent,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $ivDecoded
        );
        
        return $decryptedContent;
    }

    /**
     * Delete encrypted file
     *
     * @param string $filePath
     * @return bool
     */
    public function deleteFile($filePath)
    {
        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->delete($filePath);
        }
        
        return true;
    }

    /**
     * Get file size
     *
     * @param string $filePath
     * @return int|false
     */
    public function getFileSize($filePath)
    {
        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->size($filePath);
        }
        
        return false;
    }
}
