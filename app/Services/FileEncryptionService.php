<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class FileEncryptionService
{
    /**
     * Encrypt and store file
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $path Directory path to store
     * @return array ['encrypted_path' => string, 'original_name' => string]
     */
    /**
     * Encrypt and store file
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $path Directory path to store
     * @param string|null $key Base64 encoded key (optional, for reuse)
     * @param string|null $iv Base64 encoded IV (optional, for reuse)
     * @return array ['encrypted_path' => string, 'original_name' => string, 'key' => string, 'iv' => string]
     */
    public function encryptAndStore($file, $path = 'documents', $key = null, $iv = null)
    {
        // Read file contents
        $contents = file_get_contents($file->getRealPath());
        
        // Generate Key and IV if not provided
        if (!$key) {
            $key = base64_encode(random_bytes(32)); // AES-256 requires 32 bytes
        }
        if (!$iv) {
            $iv = base64_encode(random_bytes(16)); // AES-128-CBC block size is 16 bytes
        }
        
        // Encrypt using AES-256-CBC
        $encrypted = openssl_encrypt(
            $contents, 
            'AES-256-CBC', 
            base64_decode($key), 
            0, 
            base64_decode($iv)
        );
        
        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }
        
        // Generate unique filename
        $filename = time() . '_' . md5($file->getClientOriginalName()) . '.enc';
        $fullPath = $path . '/' . $filename;
        
        // Store encrypted file
        Storage::put($fullPath, $encrypted);
        
        return [
            'encrypted_path' => $fullPath,
            'original_name' => $file->getClientOriginalName(),
            'original_extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'encryption_key' => $key,
            'encryption_iv' => $iv,
        ];
    }
    
    /**
     * Decrypt and retrieve file
     * 
     * @param string $encryptedPath Path to encrypted file
     * @param string $key Base64 encoded key
     * @param string $iv Base64 encoded IV
     * @return string|bool Decrypted file contents
     */
    public function decryptFile($encryptedPath, $key, $iv)
    {
        if (!Storage::exists($encryptedPath)) {
            return false;
        }

        // Get encrypted contents from storage
        $encrypted = Storage::get($encryptedPath);
        
        // Decrypt using AES-256-CBC
        return openssl_decrypt(
            $encrypted, 
            'AES-256-CBC', 
            base64_decode($key), 
            0, 
            base64_decode($iv)
        );
    }
    
    /**
     * Download decrypted file
     * 
     * @param string $encryptedPath
     * @param string $originalName
     * @param string $key
     * @param string $iv
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadDecrypted($encryptedPath, $originalName, $key, $iv)
    {
        $decrypted = $this->decryptFile($encryptedPath, $key, $iv);
        
        if ($decrypted === false) {
            abort(404, 'File not found or decryption failed');
        }
        
        return response()->streamDownload(function() use ($decrypted) {
            echo $decrypted;
        }, $originalName);
    }
    
    /**
     * Delete encrypted file
     * 
     * @param string $encryptedPath
     * @return bool
     */
    public function delete($encryptedPath)
    {
        return Storage::delete($encryptedPath);
    }
    
    /**
     * Check if file exists
     * 
     * @param string $encryptedPath
     * @return bool
     */
    public function exists($encryptedPath)
    {
        return Storage::exists($encryptedPath);
    }
}
