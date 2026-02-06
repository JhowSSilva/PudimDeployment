<?php

namespace App\Services\Backup\Compression;

class Bzip2Compression implements CompressionInterface
{
    public function compress(string $filePath, ?string $password = null): string
    {
        $outputPath = $filePath . '.tar.bz2';

        $command = sprintf(
            'tar -cjf %s -C %s %s',
            escapeshellarg($outputPath),
            escapeshellarg(dirname($filePath)),
            escapeshellarg(basename($filePath))
        );

        $result = \Illuminate\Support\Facades\Process::run($command);

        if (!$result->successful()) {
            throw new \RuntimeException('Failed to create tar.bz2: ' . $result->errorOutput());
        }

        return $outputPath;
    }

    public function decompress(string $filePath, ?string $password = null): string
    {
        $extractPath = dirname($filePath);

        $command = sprintf(
            'tar -xjf %s -C %s',
            escapeshellarg($filePath),
            escapeshellarg($extractPath)
        );

        $result = \Illuminate\Support\Facades\Process::run($command);

        if (!$result->successful()) {
            throw new \RuntimeException('Failed to extract tar.bz2: ' . $result->errorOutput());
        }

        return $extractPath;
    }
}
