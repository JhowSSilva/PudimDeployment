<?php

namespace App\Services\Backup\Compression;

interface CompressionInterface
{
    /**
     * Compress file and return path to compressed file
     */
    public function compress(string $filePath, ?string $password = null): string;

    /**
     * Decompress file and return path to decompressed file
     */
    public function decompress(string $filePath, ?string $password = null): string;
}
