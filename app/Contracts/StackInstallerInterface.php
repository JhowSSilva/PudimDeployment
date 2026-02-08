<?php

namespace App\Contracts;

use App\Models\Server;

interface StackInstallerInterface
{
    /**
     * Install the stack on the server
     */
    public function install(Server $server, array $config): bool;
    
    /**
     * Validate if installation was successful
     */
    public function validate(Server $server): bool;
    
    /**
     * Get required packages for installation
     */
    public function getRequiredPackages(): array;
    
    /**
     * Get the installer name
     */
    public function getName(): string;
    
    /**
     * Get supported versions
     */
    public function getSupportedVersions(): array;
    
    /**
     * Generate installation script
     */
    public function generateInstallScript(Server $server, array $config): string;
    
    /**
     * Get default configuration
     */
    public function getDefaultConfig(): array;
    
    /**
     * Post-install configuration
     */
    public function configure(Server $server, array $config): bool;
}