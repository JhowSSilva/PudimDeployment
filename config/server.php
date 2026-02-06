<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firewall Configuration
    |--------------------------------------------------------------------------
    */
    'firewall' => [
        'default_rules' => [
            ['port' => 22, 'protocol' => 'tcp', 'comment' => 'SSH'],
            ['port' => 80, 'protocol' => 'tcp', 'comment' => 'HTTP'],
            ['port' => 443, 'protocol' => 'tcp', 'comment' => 'HTTPS'],
        ],
        'fail2ban' => [
            'enabled' => true,
            'max_retry' => 5,
            'ban_time' => 3600, // seconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'opcache' => [
            'memory_consumption' => 256, // MB
            'max_accelerated_files' => 10000,
            'validate_timestamps' => true,
            'revalidate_freq' => 2,
        ],
        'redis' => [
            'default_max_memory' => '256mb',
            'default_policy' => 'allkeys-lru',
        ],
        'memcached' => [
            'default_memory' => 64, // MB
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | APM (Application Performance Monitoring)
    |--------------------------------------------------------------------------
    */
    'apm' => [
        'performance_score' => [
            'excellent' => 90,
            'good' => 70,
            'fair' => 50,
            'poor' => 30,
        ],
        'response_time_thresholds' => [
            'excellent' => 200, // ms
            'good' => 500,
            'fair' => 1000,
            'poor' => 2000,
        ],
        'slow_query_threshold' => 1, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Deployment Configuration
    |--------------------------------------------------------------------------
    */
    'deployment' => [
        'backup_retention' => 5, // number of backups to keep
        'health_check' => [
            'enabled' => true,
            'timeout' => 10, // seconds
            'critical_disk_usage' => 90, // percentage
        ],
        'auto_rollback' => true,
        'run_tests' => false, // set to true to run tests before deploy
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Service Configuration
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'predictions' => [
            'enabled' => true,
            'default_hours_ahead' => 24,
            'minimum_data_points' => 10,
        ],
        'security' => [
            'scan_interval' => 3600, // seconds
            'auto_block_threats' => false,
        ],
        'resource_optimization' => [
            'auto_cleanup' => true,
            'log_retention_days' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    */
    'database' => [
        'backup' => [
            'default_retention' => 7, // days
            'compression' => true,
            'directory' => '/var/backups/databases',
        ],
        'automated_backups' => [
            'enabled' => true,
            'default_schedule' => 'daily', // hourly, daily, weekly, monthly
        ],
        'replication' => [
            'supported_types' => ['mysql'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing Configuration
    |--------------------------------------------------------------------------
    */
    'billing' => [
        'currency' => 'USD',
        'tax_rate' => 0.0, // 0% by default, customize per region
        'invoice' => [
            'auto_generate' => true,
            'generation_day' => 1, // first day of month
            'payment_due_days' => 14,
        ],
        'usage_tracking' => [
            'enabled' => true,
            'interval' => 3600, // seconds (1 hour)
        ],
        'plans' => [
            'basic' => [
                'name' => 'Basic',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'limits' => [
                    'servers' => 3,
                    'sites' => 10,
                ],
            ],
            'professional' => [
                'name' => 'Professional',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'limits' => [
                    'servers' => 10,
                    'sites' => 50,
                ],
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'limits' => [
                    'servers' => -1, // unlimited
                    'sites' => -1,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */
    'security' => [
        'threat_detection' => [
            'enabled' => true,
            'scan_frequency' => 'hourly',
            'auto_notify' => true,
        ],
        'ip_blocking' => [
            'enabled' => true,
            'max_failed_logins' => 10,
            'block_duration' => 3600, // seconds
        ],
        'malware_scanning' => [
            'enabled' => true,
            'patterns' => [
                'eval(base64_decode',
                'system($_GET',
                'exec($_POST',
                'assert($_REQUEST',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications Configuration
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'channels' => ['mail', 'slack', 'discord'],
        'events' => [
            'deployment_failed' => true,
            'security_threat_detected' => true,
            'server_down' => true,
            'disk_space_critical' => true,
            'ssl_expiring' => true,
        ],
        'slack' => [
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
        ],
        'discord' => [
            'webhook_url' => env('DISCORD_WEBHOOK_URL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'default_processes' => 1,
        'default_timeout' => 60,
        'default_tries' => 3,
        'failed_job_retention' => 7, // days
    ],
];
