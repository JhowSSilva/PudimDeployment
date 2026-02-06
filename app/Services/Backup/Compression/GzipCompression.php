<?php

namespace App\Services\Backup\Compression;

use Illuminate\Support\Facades\Process;

class GzipCompression implements CompressionInterface
{
    public function compress(string $filePath, ?string $password = null): string
    {
        $outputPath = $filePath . '.tar.gz';

        // Create tar.gz
        $command = sprintf(
            'tar -czf %s -C %s %s',
            escapeshellarg($outputPath),
            escapeshellarg(dirname($filePath)),
            escapeshellarg(basename($filePath))
        );

        $result = Process::run($command);

        if (!$result->successful()) {
            throw new \RuntimeException('Failed to create tar.gz: ' . $result->errorOutput());
        }

        return $outputPath;
    }

    public function decompress(string $filePath, ?string $password = null): string
    {
        $extractPath = dirname($filePath);

        $command = sprintf(
            'tar -xzf %s -C %s',
            escapeshellarg($filePath),
            escapeshellarg($extractPath)
        );

        $result = Process::run($command);

        if (!$result->successful()) {
            throw new \RuntimeException('Failed to extract tar.gz: ' . $result->errorOutput());
        }

        return $extractPath;
    }
}
