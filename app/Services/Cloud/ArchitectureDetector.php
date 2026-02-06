<?php

namespace App\Services\Cloud;

class ArchitectureDetector
{
    /**
     * Instance families that use ARM64 (Graviton)
     */
    private const ARM64_FAMILIES = [
        't4g',   // Graviton2
        'a1',    // Graviton1
        'm6g',   // Graviton2
        'm6gd',  // Graviton2 with NVMe
        'm7g',   // Graviton3
        'm7gd',  // Graviton3 with NVMe
        'c6g',   // Graviton2
        'c6gd',  // Graviton2 with NVMe
        'c6gn',  // Graviton2 network optimized
        'c7g',   // Graviton3
        'c7gd',  // Graviton3 with NVMe
        'c7gn',  // Graviton3 network optimized
        'r6g',   // Graviton2
        'r6gd',  // Graviton2 with NVMe
        'r7g',   // Graviton3
        'r7gd',  // Graviton3 with NVMe
        'x2gd',  // Graviton2
        'im4gn', // Graviton2
        'is4gen',// Graviton2
    ];

    /**
     * Detect architecture from instance type
     */
    public static function detect(string $instanceType): string
    {
        $family = self::extractFamily($instanceType);
        
        return self::isGravitonFamily($family) ? 'arm64' : 'x86_64';
    }

    /**
     * Check if instance type is Graviton (ARM64)
     */
    public static function isGraviton(string $instanceType): bool
    {
        return self::detect($instanceType) === 'arm64';
    }

    /**
     * Check if instance type is x86_64
     */
    public static function isX86(string $instanceType): bool
    {
        return self::detect($instanceType) === 'x86_64';
    }

    /**
     * Extract instance family from instance type
     * Example: t4g.micro -> t4g, m7gd.large -> m7gd
     */
    private static function extractFamily(string $instanceType): string
    {
        $parts = explode('.', $instanceType);
        return $parts[0] ?? '';
    }

    /**
     * Check if family is Graviton
     */
    private static function isGravitonFamily(string $family): bool
    {
        return in_array($family, self::ARM64_FAMILIES);
    }

    /**
     * Get compatible AMI architecture string for AWS API
     */
    public static function getAMIArchitecture(string $instanceType): string
    {
        return self::isGraviton($instanceType) ? 'arm64' : 'x86_64';
    }

    /**
     * Get human-readable architecture name
     */
    public static function getArchitectureName(string $instanceType): string
    {
        if (self::isGraviton($instanceType)) {
            $family = self::extractFamily($instanceType);
            
            // Determine Graviton generation
            if (str_contains($family, '7g')) {
                return 'ARM64 (Graviton3)';
            } elseif (str_contains($family, '6g') || $family === 't4g') {
                return 'ARM64 (Graviton2)';
            } elseif ($family === 'a1') {
                return 'ARM64 (Graviton1)';
            }
            
            return 'ARM64 (Graviton)';
        }
        
        return 'x86_64 (Intel/AMD)';
    }

    /**
     * Get recommended instance types for architecture
     */
    public static function getRecommendedInstances(string $architecture): array
    {
        if ($architecture === 'arm64') {
            return [
                't4g.micro', 't4g.small', 't4g.medium', 't4g.large',
                'm6g.medium', 'm6g.large', 'm6g.xlarge',
                'm7g.medium', 'm7g.large', 'm7g.xlarge',
                'c6g.medium', 'c6g.large',
                'r6g.medium', 'r6g.large',
            ];
        }
        
        return [
            't3.micro', 't3.small', 't3.medium', 't3.large',
            't3a.micro', 't3a.small', 't3a.medium',
            'm5.large', 'm5.xlarge', 'm5.2xlarge',
            'c5.large', 'c5.xlarge',
            'r5.large', 'r5.xlarge',
        ];
    }
}
