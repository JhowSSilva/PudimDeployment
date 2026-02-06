<?php

namespace App\Services\Backup\Compression;

use ZipArchive;

class ZipCompression implements CompressionInterface
{
    public function compress(string $filePath, ?string $password = null): string
    {
        $outputPath = $filePath . '.zip';
        
        $zip = new ZipArchive();
        
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Failed to create ZIP archive');
        }

        $zip->addFile($filePath, basename($filePath));

        if ($password) {
            $zip->setPassword($password);
            $zip->setEncryptionName(basename($filePath), ZipArchive::EM_AES_256);
        }

        $zip->close();

        return $outputPath;
    }

    public function decompress(string $filePath, ?string $password = null): string
    {
        $zip = new ZipArchive();
        
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Failed to open ZIP archive');
        }

        if ($password) {
            $zip->setPassword($password);
        }

        $extractPath = dirname($filePath);
        $zip->extractTo($extractPath);
        $zip->close();

        // Return path to first extracted file
        $extractedFile = $extractPath . '/' . pathinfo($filePath, PATHINFO_FILENAME);
        
        return $extractedFile;
    }
}
