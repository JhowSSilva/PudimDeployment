<?php

namespace App\Services\Backup\Compression;

class NoCompression implements CompressionInterface
{
    public function compress(string $filePath, ?string $password = null): string
    {
        // No compression, return original file
        return $filePath;
    }

    public function decompress(string $filePath, ?string $password = null): string
    {
        // No decompression needed
        return $filePath;
    }
}
