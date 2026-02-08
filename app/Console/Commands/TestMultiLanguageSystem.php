<?php

namespace App\Console\Commands;

use App\Services\StackInstallationService;
use Illuminate\Console\Command;

class TestMultiLanguageSystem extends Command
{
    protected $signature = 'test:multi-language-system {--details}';
    protected $description = 'Test the multi-language server provisioning system';

    protected StackInstallationService $stackService;

    public function __construct(StackInstallationService $stackService)
    {
        parent::__construct();
        $this->stackService = $stackService;
    }

    public function handle()
    {
        $this->info("🧪 Testing Multi-Language Server Provisioning System");
        $this->line("═══════════════════════════════════════════════════════════════");
        
        $allTests = [
            'testServiceInstantiation' => 'Service Instantiation',
            'testInstallerRegistration' => 'Installer Registration', 
            'testSupportedLanguages' => 'Supported Languages',
            'testLanguageVersions' => 'Language Versions'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($allTests as $method => $description) {
            try {
                $this->info("🔍 Testing: {$description}");
                
                $result = $this->$method();
                
                if ($result['success']) {
                    $this->line("  ✅ PASSED: {$result['message']}");
                    $passed++;
                } else {
                    $this->error("  ❌ FAILED: {$result['message']}");
                    $failed++;
                }
                
                if ($this->option('details') && isset($result['details'])) {
                    $this->line("     " . $result['details']);
                }
                
            } catch (\Exception $e) {
                $this->error("  ❌ ERROR: {$e->getMessage()}");
                $failed++;
            }
            
            $this->line("");
        }
        
        $this->line("═══════════════════════════════════════════════════════════════");
        
        if ($failed === 0) {
            $this->info("🎉 ALL TESTS PASSED! ({$passed}/{$passed})");
            $this->info("✨ Multi-language system is ready for production!");
            return self::SUCCESS;
        } else {
            $this->error("💥 SOME TESTS FAILED! ({$passed} passed, {$failed} failed)");
            $this->warn("⚠️  Please fix the issues before deploying to production.");
            return self::FAILURE;
        }
    }

    protected function testServiceInstantiation(): array
    {
        try {
            $service = app(StackInstallationService::class);
            
            if (!$service instanceof StackInstallationService) {
                return [
                    'success' => false,
                    'message' => 'Service not properly instantiated'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Service instantiated correctly',
                'details' => 'StackInstallationService resolved from container'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function testInstallerRegistration(): array
    {
        try {
            $installers = $this->stackService->getAvailableLanguages();
            
            $expectedInstallers = ['php', 'nodejs', 'python'];
            $foundInstallers = array_keys($installers);
            
            $missing = array_diff($expectedInstallers, $foundInstallers);
            
            if (!empty($missing)) {
                return [
                    'success' => false,
                    'message' => 'Missing installers: ' . implode(', ', $missing),
                    'details' => 'Found: ' . implode(', ', $foundInstallers)
                ];
            }
            
            return [
                'success' => true,
                'message' => 'All installers registered successfully',
                'details' => 'Installers: ' . implode(', ', $foundInstallers)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function testSupportedLanguages(): array
    {
        try {
            $languages = array_keys($this->stackService->getAvailableLanguages());
            
            if (!is_array($languages) || empty($languages)) {
                return [
                    'success' => false,
                    'message' => 'No supported languages found'
                ];
            }
            
            $expectedLanguages = ['php', 'nodejs', 'python'];
            $missing = array_diff($expectedLanguages, $languages);
            
            if (!empty($missing)) {
                return [
                    'success' => false,
                    'message' => 'Missing expected languages: ' . implode(', ', $missing),
                    'details' => 'Found: ' . implode(', ', $languages)
                ];
            }
            
            return [
                'success' => true,
                'message' => 'All expected languages supported',
                'details' => 'Languages: ' . implode(', ', $languages)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function testLanguageVersions(): array
    {
        try {
            $testResults = [];
            
            $languageVersionTests = [
                'php' => ['8.2', '8.1'],
                'nodejs' => ['18', '16', '14'],
                'python' => ['3.11', '3.10', '3.9']
            ];
            
            foreach ($languageVersionTests as $language => $expectedVersions) {
                $versions = $this->stackService->getSupportedVersions($language);
                
                if (empty($versions)) {
                    $testResults[] = "No versions for {$language}";
                    continue;
                }
                
                $foundExpected = array_intersect($expectedVersions, $versions);
                
                if (empty($foundExpected)) {
                    $testResults[] = "No expected versions for {$language}";
                }
            }
            
            if (!empty($testResults)) {
                return [
                    'success' => false,  
                    'message' => implode('; ', $testResults)
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Version detection working for all languages',
                'details' => 'Tested PHP, Node.js, and Python versions'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function testConfigurationValidation(): array
    {
        try {
            // Test valid configuration
            $validConfig = [
                'programming_language' => 'php',
                'language_version' => '8.2',
                'webserver' => 'nginx',
                'database' => 'mysql',
                'cache' => 'redis'
            ];
            
            $result = $this->stackService->validateServerConfiguration($validConfig);
            
            if (!$result['valid']) {
                return [
                    'success' => false,
                    'message' => 'Valid configuration rejected',
                    'details' => 'Errors: ' . implode(', ', $result['errors'])
                ];
            }
            
            // Test invalid configuration
            $invalidConfig = [
                'programming_language' => 'invalid_language',
                'language_version' => '',
                'webserver' => 'unknown'
            ];
            
            $result = $this->stackService->validateServerConfiguration($invalidConfig);
            
            if ($result['valid']) {
                return [
                    'success' => false,
                    'message' => 'Invalid configuration accepted'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Configuration validation working correctly',
                'details' => 'Valid configs accepted, invalid configs rejected'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function testTimeEstimation(): array
    {
        try {
            $times = [];
            $languages = ['php', 'nodejs', 'python'];
            
            foreach ($languages as $language) {
                $time = $this->stackService->estimateInstallationTime($language);
                
                if (!is_int($time) || $time <= 0) {
                    return [
                        'success' => false,
                        'message' => "Invalid time estimation for {$language}: {$time}"
                    ];
                }
                
                $times[$language] = $time;
            }
            
            // Test unknown language returns default
            $unknownTime = $this->stackService->estimateInstallationTime('unknown');
            if ($unknownTime !== 600) {
                return [
                    'success' => false,
                    'message' => "Unknown language should return 600 seconds, got {$unknownTime}"
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Time estimation working correctly',
                'details' => 'Times: ' . json_encode($times) . ', Unknown: 600s'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
