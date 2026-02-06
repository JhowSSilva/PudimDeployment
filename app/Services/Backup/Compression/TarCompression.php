<?php

namespace App\Services\Backup\Compression;

class TarCompression implements CompressionInterface
{
    public function compress(string $filePath, ?string $password = null): string
    {
        // Similar to GzipCompression but without compression
        $outputPath = $filePath . '.tar';

        $command = sprintf(
            'tar -cf %s -C %s %s',
            escapeshellarg($outputPath),
            escapeshellarg(dirname($filePath)),
            escapeshellarg(basename($filePath))
        );

        $result = \Illuminate\Support\Facades\Process::run($command);

        if (!$result->successful()) {
            throw new \RuntimeException('Failed to create tar: ' . $result->errorOutput());
        }

        return $outputPath;
    }

    public function decompress(string $filePath, ?string $password = null): string
    {
        $extractPath = dirname($filePath);

        $command = sprintf(
            'tar -xf %s -C %s',
            escapeshellarg($filePath),
            escapeshellarg($extractPath)
        );

        $result = \Illuminate\Support\Facades\Process::run($command);

        if (!$result->successful()) {
            throw new \RuntimeException('Failed to extract tar: ' . $result->errorOutput());
        }

        return $extractPath;
    }
}
