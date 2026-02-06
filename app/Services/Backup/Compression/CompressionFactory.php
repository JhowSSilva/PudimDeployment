<?php

namespace App\Services\Backup\Compression;

class CompressionFactory
{
    public static function make(string $type): CompressionInterface
    {
        return match($type) {
            'zip' => new ZipCompression(),
            'tar' => new TarCompression(),
            'tar_gz' => new GzipCompression(),
            'tar_bz2' => new Bzip2Compression(),
            'none' => new NoCompression(),
            default => throw new \InvalidArgumentException("Unsupported compression type: {$type}"),
        };
    }
}
