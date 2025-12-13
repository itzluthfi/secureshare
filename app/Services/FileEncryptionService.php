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
    public function encryptAndStore($file, $path = 'documents')
    {
        // Read file contents
        $contents = file_get_contents($file->getRealPath());
        
        // Encrypt using AES-256-CBC (Laravel default)
        $encrypted = Crypt::encryptString($contents);
        
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
        ];
    }
    
    /**
     * Decrypt and retrieve file
     * 
     * @param string $encryptedPath Path to encrypted file
     * @return string Decrypted file contents
     */
    public function decryptFile($encryptedPath)
    {
        // Get encrypted contents from storage
        $encrypted = Storage::get($encryptedPath);
        
        // Decrypt using AES-256-CBC
        $decrypted = Crypt::decryptString($encrypted);
        
        return $decrypted;
    }
    
    /**
     * Download decrypted file
     * 
     * @param string $encryptedPath
     * @param string $originalName
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadDecrypted($encryptedPath, $originalName)
    {
        $decrypted = $this->decryptFile($encryptedPath);
        
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
