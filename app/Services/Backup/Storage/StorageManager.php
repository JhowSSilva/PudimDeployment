<?php

namespace App\Services\Backup\Storage;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;

class StorageManager
{
    /**
     * Get filesystem disk for given provider and credentials
     */
    public function getDisk(string $provider, array $credentials): FilesystemAdapter
    {
        $adapter = match($provider) {
            'aws_s3' => $this->createS3Adapter($credentials),
            'azure_blob' => $this->createAzureAdapter($credentials),
            'google_cloud' => $this->createGcsAdapter($credentials),
            'do_spaces' => $this->createDigitalOceanAdapter($credentials),
            'backblaze_b2' => $this->createBackblazeAdapter($credentials),
            'wasabi' => $this->createWasabiAdapter($credentials),
            'minio' => $this->createMinioAdapter($credentials),
            'local' => $this->createLocalAdapter($credentials),
            default => throw new \InvalidArgumentException("Unsupported storage provider: {$provider}"),
        };

        return new FilesystemAdapter(new Filesystem($adapter), $adapter);
    }

    /**
     * Create AWS S3 adapter
     */
    private function createS3Adapter(array $credentials)
    {
        $client = new \Aws\S3\S3Client([
            'credentials' => [
                'key' => $credentials['access_key'],
                'secret' => $credentials['secret_key'],
            ],
            'region' => $credentials['region'],
            'version' => 'latest',
        ]);

        return new \League\Flysystem\AwsS3V3\AwsS3V3Adapter(
            $client,
            $credentials['bucket']
        );
    }

    /**
     * Create Azure Blob adapter
     */
    private function createAzureAdapter(array $credentials)
    {
        $connectionString = sprintf(
            'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;EndpointSuffix=core.windows.net',
            $credentials['account_name'],
            $credentials['account_key']
        );

        $blobClient = \MicrosoftAzure\Storage\Blob\BlobRestProxy::createBlobService($connectionString);

        return new \League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter(
            $blobClient,
            $credentials['container']
        );
    }

    /**
     * Create Google Cloud Storage adapter
     */
    private function createGcsAdapter(array $credentials)
    {
        $storageClient = new \Google\Cloud\Storage\StorageClient([
            'projectId' => $credentials['project_id'],
            'keyFile' => json_decode($credentials['key_file'], true),
        ]);

        $bucket = $storageClient->bucket($credentials['bucket']);

        return new \Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter(
            $storageClient,
            $bucket
        );
    }

    /**
     * Create DigitalOcean Spaces adapter (S3-compatible)
     */
    private function createDigitalOceanAdapter(array $credentials)
    {
        $client = new \Aws\S3\S3Client([
            'credentials' => [
                'key' => $credentials['access_key'],
                'secret' => $credentials['secret_key'],
            ],
            'region' => $credentials['region'],
            'version' => 'latest',
            'endpoint' => sprintf('https://%s.digitaloceanspaces.com', $credentials['region']),
        ]);

        return new \League\Flysystem\AwsS3V3\AwsS3V3Adapter(
            $client,
            $credentials['space']
        );
    }

    /**
     * Create Backblaze B2 adapter
     */
    private function createBackblazeAdapter(array $credentials)
    {
        // Using S3-compatible API
        $client = new \Aws\S3\S3Client([
            'credentials' => [
                'key' => $credentials['key_id'],
                'secret' => $credentials['application_key'],
            ],
            'region' => 'us-west-002',
            'version' => 'latest',
            'endpoint' => 'https://s3.us-west-002.backblazeb2.com',
        ]);

        return new \League\Flysystem\AwsS3V3\AwsS3V3Adapter(
            $client,
            $credentials['bucket_name']
        );
    }

    /**
     * Create Wasabi adapter (S3-compatible)
     */
    private function createWasabiAdapter(array $credentials)
    {
        $client = new \Aws\S3\S3Client([
            'credentials' => [
                'key' => $credentials['access_key'],
                'secret' => $credentials['secret_key'],
            ],
            'region' => $credentials['region'],
            'version' => 'latest',
            'endpoint' => sprintf('https://s3.%s.wasabisys.com', $credentials['region']),
        ]);

        return new \League\Flysystem\AwsS3V3\AwsS3V3Adapter(
            $client,
            $credentials['bucket']
        );
    }

    /**
     * Create MinIO adapter (S3-compatible)
     */
    private function createMinioAdapter(array $credentials)
    {
        $client = new \Aws\S3\S3Client([
            'credentials' => [
                'key' => $credentials['access_key'],
                'secret' => $credentials['secret_key'],
            ],
            'region' => 'us-east-1',
            'version' => 'latest',
            'endpoint' => $credentials['endpoint'],
            'use_path_style_endpoint' => true,
        ]);

        return new \League\Flysystem\AwsS3V3\AwsS3V3Adapter(
            $client,
            $credentials['bucket']
        );
    }

    /**
     * Create local filesystem adapter
     */
    private function createLocalAdapter(array $credentials)
    {
        return new \League\Flysystem\Local\LocalFilesystemAdapter(
            $credentials['path']
        );
    }
}
