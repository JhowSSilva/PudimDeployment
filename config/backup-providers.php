<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Storage Providers
    |--------------------------------------------------------------------------
    |
    | Configuration for all supported cloud storage providers.
    | Each provider has its own set of required fields.
    |
    */

    'providers' => [
        'aws_s3' => [
            'name' => 'AWS S3',
            'icon' => 'aws',
            'color' => 'orange',
            'fields' => [
                'access_key' => [
                    'type' => 'text',
                    'label' => 'Access Key ID',
                    'required' => true,
                    'placeholder' => 'AKIAIOSFODNN7EXAMPLE',
                ],
                'secret_key' => [
                    'type' => 'password',
                    'label' => 'Secret Access Key',
                    'required' => true,
                    'placeholder' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
                ],
                'region' => [
                    'type' => 'select',
                    'label' => 'Region',
                    'required' => true,
                    'options' => [
                        'us-east-1' => 'US East (N. Virginia)',
                        'us-east-2' => 'US East (Ohio)',
                        'us-west-1' => 'US West (N. California)',
                        'us-west-2' => 'US West (Oregon)',
                        'ca-central-1' => 'Canada (Central)',
                        'eu-west-1' => 'Europe (Ireland)',
                        'eu-west-2' => 'Europe (London)',
                        'eu-west-3' => 'Europe (Paris)',
                        'eu-central-1' => 'Europe (Frankfurt)',
                        'ap-southeast-1' => 'Asia Pacific (Singapore)',
                        'ap-southeast-2' => 'Asia Pacific (Sydney)',
                        'ap-northeast-1' => 'Asia Pacific (Tokyo)',
                        'ap-northeast-2' => 'Asia Pacific (Seoul)',
                        'sa-east-1' => 'South America (São Paulo)',
                    ],
                ],
                'bucket' => [
                    'type' => 'text',
                    'label' => 'Bucket Name',
                    'required' => true,
                    'placeholder' => 'my-backup-bucket',
                ],
            ],
        ],

        'azure_blob' => [
            'name' => 'Azure Blob Storage',
            'icon' => 'azure',
            'color' => 'blue',
            'fields' => [
                'account_name' => [
                    'type' => 'text',
                    'label' => 'Storage Account Name',
                    'required' => true,
                    'placeholder' => 'mystorageaccount',
                ],
                'account_key' => [
                    'type' => 'password',
                    'label' => 'Account Key',
                    'required' => true,
                    'placeholder' => 'AccountKey=...',
                ],
                'container' => [
                    'type' => 'text',
                    'label' => 'Container Name',
                    'required' => true,
                    'placeholder' => 'backups',
                ],
            ],
        ],

        'google_cloud' => [
            'name' => 'Google Cloud Storage',
            'icon' => 'google-cloud',
            'color' => 'red',
            'fields' => [
                'project_id' => [
                    'type' => 'text',
                    'label' => 'Project ID',
                    'required' => true,
                    'placeholder' => 'my-project-123456',
                ],
                'key_file' => [
                    'type' => 'textarea',
                    'label' => 'Service Account JSON',
                    'required' => true,
                    'placeholder' => '{ "type": "service_account", ... }',
                    'rows' => 6,
                ],
                'bucket' => [
                    'type' => 'text',
                    'label' => 'Bucket Name',
                    'required' => true,
                    'placeholder' => 'my-backups-bucket',
                ],
            ],
        ],

        'do_spaces' => [
            'name' => 'DigitalOcean Spaces',
            'icon' => 'digitalocean',
            'color' => 'blue',
            'fields' => [
                'access_key' => [
                    'type' => 'text',
                    'label' => 'Spaces Access Key',
                    'required' => true,
                ],
                'secret_key' => [
                    'type' => 'password',
                    'label' => 'Spaces Secret Key',
                    'required' => true,
                ],
                'region' => [
                    'type' => 'select',
                    'label' => 'Region',
                    'required' => true,
                    'options' => [
                        'nyc3' => 'New York 3',
                        'sfo3' => 'San Francisco 3',
                        'ams3' => 'Amsterdam 3',
                        'sgp1' => 'Singapore 1',
                        'fra1' => 'Frankfurt 1',
                    ],
                ],
                'space' => [
                    'type' => 'text',
                    'label' => 'Space Name',
                    'required' => true,
                    'placeholder' => 'my-space',
                ],
            ],
        ],

        'backblaze_b2' => [
            'name' => 'Backblaze B2',
            'icon' => 'backblaze',
            'color' => 'red',
            'fields' => [
                'key_id' => [
                    'type' => 'text',
                    'label' => 'Application Key ID',
                    'required' => true,
                ],
                'application_key' => [
                    'type' => 'password',
                    'label' => 'Application Key',
                    'required' => true,
                ],
                'bucket_id' => [
                    'type' => 'text',
                    'label' => 'Bucket ID',
                    'required' => true,
                ],
                'bucket_name' => [
                    'type' => 'text',
                    'label' => 'Bucket Name',
                    'required' => true,
                ],
            ],
        ],

        'wasabi' => [
            'name' => 'Wasabi',
            'icon' => 'wasabi',
            'color' => 'green',
            'fields' => [
                'access_key' => [
                    'type' => 'text',
                    'label' => 'Access Key',
                    'required' => true,
                ],
                'secret_key' => [
                    'type' => 'password',
                    'label' => 'Secret Key',
                    'required' => true,
                ],
                'region' => [
                    'type' => 'select',
                    'label' => 'Region',
                    'required' => true,
                    'options' => [
                        'us-east-1' => 'US East 1 (N. Virginia)',
                        'us-east-2' => 'US East 2 (N. Virginia)',
                        'us-west-1' => 'US West 1 (Oregon)',
                        'eu-central-1' => 'EU Central 1 (Amsterdam)',
                    ],
                ],
                'bucket' => [
                    'type' => 'text',
                    'label' => 'Bucket Name',
                    'required' => true,
                ],
            ],
        ],

        'minio' => [
            'name' => 'MinIO (Self-Hosted)',
            'icon' => 'minio',
            'color' => 'purple',
            'fields' => [
                'endpoint' => [
                    'type' => 'text',
                    'label' => 'Endpoint URL',
                    'required' => true,
                    'placeholder' => 'https://minio.example.com',
                ],
                'access_key' => [
                    'type' => 'text',
                    'label' => 'Access Key',
                    'required' => true,
                ],
                'secret_key' => [
                    'type' => 'password',
                    'label' => 'Secret Key',
                    'required' => true,
                ],
                'bucket' => [
                    'type' => 'text',
                    'label' => 'Bucket Name',
                    'required' => true,
                ],
                'use_ssl' => [
                    'type' => 'checkbox',
                    'label' => 'Use SSL/TLS',
                    'required' => false,
                    'default' => true,
                ],
            ],
        ],

        'local' => [
            'name' => 'Local Storage',
            'icon' => 'hard-drive',
            'color' => 'gray',
            'fields' => [
                'path' => [
                    'type' => 'text',
                    'label' => 'Storage Path',
                    'required' => true,
                    'placeholder' => '/var/backups',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Types
    |--------------------------------------------------------------------------
    */

    'database_types' => [
        'postgresql' => [
            'name' => 'PostgreSQL',
            'icon' => 'database',
            'color' => 'blue',
            'default_port' => 5432,
            'dump_command' => 'pg_dump',
            'restore_command' => 'pg_restore',
        ],
        'mysql' => [
            'name' => 'MySQL/MariaDB',
            'icon' => 'database',
            'color' => 'orange',
            'default_port' => 3306,
            'dump_command' => 'mysqldump',
            'restore_command' => 'mysql',
        ],
        'mongodb' => [
            'name' => 'MongoDB',
            'icon' => 'database',
            'color' => 'green',
            'default_port' => 27017,
            'dump_command' => 'mongodump',
            'restore_command' => 'mongorestore',
        ],
        'redis' => [
            'name' => 'Redis',
            'icon' => 'database',
            'color' => 'red',
            'default_port' => 6379,
            'dump_command' => 'redis-cli',
            'restore_command' => 'redis-cli',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Compression Types
    |--------------------------------------------------------------------------
    */

    'compression_types' => [
        'none' => 'No Compression',
        'zip' => 'ZIP',
        'tar' => 'TAR',
        'tar_gz' => 'TAR.GZ (Recommended)',
        'tar_bz2' => 'TAR.BZ2',
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Frequencies
    |--------------------------------------------------------------------------
    */

    'frequencies' => [
        'hourly' => 'Every Hour',
        'every_6_hours' => 'Every 6 Hours',
        'every_12_hours' => 'Every 12 Hours',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'retention' => 7,
        'compression' => 'tar_gz',
        'timezone' => 'UTC',
        'start_time' => '02:00',
        'delete_local_on_fail' => true,
        'verify_backup' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary Storage
    |--------------------------------------------------------------------------
    */

    'temp_path' => storage_path('app/backups/temp'),

];
