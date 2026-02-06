<?php

namespace App\Services;

use App\Models\DockerContainer;
use App\Models\Server;
use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DockerManager
{
    public function __construct(
        private SSHService $sshService
    ) {}

    /**
     * List all containers on a server
     */
    public function listContainers(Server $server, bool $all = false): array
    {
        $command = $all ? 'docker ps -a --format json' : 'docker ps --format json';
        $output = $this->sshService->execute($server, $command);

        $containers = [];
        $lines = array_filter(explode("\n", $output));

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if ($data) {
                $containers[] = [
                    'id' => $data['ID'] ?? null,
                    'name' => $data['Names'] ?? null,
                    'image' => $data['Image'] ?? null,
                    'status' => $data['Status'] ?? null,
                    'state' => $data['State'] ?? null,
                    'ports' => $data['Ports'] ?? '',
                ];
            }
        }

        return $containers;
    }

    /**
     * Sync containers from Docker to database
     */
    public function syncContainers(Server $server): int
    {
        $dockerContainers = $this->listContainers($server, true);
        $synced = 0;

        foreach ($dockerContainers as $containerData) {
            $this->syncContainer($server, $containerData['id']);
            $synced++;
        }

        return $synced;
    }

    /**
     * Sync a single container
     */
    private function syncContainer(Server $server, string $containerId): DockerContainer
    {
        $inspect = $this->inspectContainer($server, $containerId);

        return DockerContainer::updateOrCreate(
            [
                'server_id' => $server->id,
                'container_id' => $containerId,
            ],
            [
                'name' => ltrim($inspect['Name'] ?? '', '/'),
                'image' => $inspect['Config']['Image'] ?? null,
                'image_tag' => $this->extractImageTag($inspect['Config']['Image'] ?? ''),
                'status' => $inspect['State']['Status'] ?? 'unknown',
                'started_at' => isset($inspect['State']['StartedAt']) ? 
                    \Carbon\Carbon::parse($inspect['State']['StartedAt']) : null,
                'finished_at' => isset($inspect['State']['FinishedAt']) && $inspect['State']['FinishedAt'] !== '0001-01-01T00:00:00Z' ?
                    \Carbon\Carbon::parse($inspect['State']['FinishedAt']) : null,
                'ports' => $this->parsePortBindings($inspect['NetworkSettings']['Ports'] ?? []),
                'volumes' => $this->parseVolumes($inspect['Mounts'] ?? []),
                'environment' => $inspect['Config']['Env'] ?? [],
                'network' => $this->getNetworkName($inspect['NetworkSettings']['Networks'] ?? []),
                'restart_policy' => $inspect['HostConfig']['RestartPolicy']['Name'] ?? 'no',
                'cpu_limit' => $inspect['HostConfig']['NanoCpus'] ?? null,
                'memory_limit' => $inspect['HostConfig']['Memory'] ?? null,
                'privileged' => $inspect['HostConfig']['Privileged'] ?? false,
                'working_dir' => $inspect['Config']['WorkingDir'] ?? null,
                'command' => is_array($inspect['Config']['Cmd'] ?? null) ? 
                    implode(' ', $inspect['Config']['Cmd']) : null,
                'labels' => $inspect['Config']['Labels'] ?? [],
            ]
        );
    }

    /**
     * Inspect a container
     */
    public function inspectContainer(Server $server, string $containerId): array
    {
        $command = "docker inspect {$containerId}";
        $output = $this->sshService->execute($server, $command);

        $data = json_decode($output, true);
        
        return $data[0] ?? [];
    }

    /**
     * Create a new container
     */
    public function createContainer(Server $server, array $config, ?Site $site = null): DockerContainer
    {
        Log::info("Creating Docker container on server {$server->name}", $config);

        $command = $this->buildCreateCommand($config);
        
        $output = $this->sshService->execute($server, $command);
        $containerId = trim($output);

        // Sync to database
        $container = $this->syncContainer($server, $containerId);

        // Link to site if provided
        if ($site) {
            $container->update(['site_id' => $site->id]);
        }

        Log::info("Container {$container->name} created with ID {$containerId}");

        return $container;
    }

    /**
     * Build docker create command from configuration
     */
    private function buildCreateCommand(array $config): string
    {
        $parts = ['docker run -d'];

        // Name
        if (isset($config['name'])) {
            $parts[] = "--name {$config['name']}";
        }

        // Ports
        if (isset($config['ports'])) {
            foreach ($config['ports'] as $hostPort => $containerPort) {
                $parts[] = "-p {$hostPort}:{$containerPort}";
            }
        }

        // Volumes
        if (isset($config['volumes'])) {
            foreach ($config['volumes'] as $hostPath => $containerPath) {
                $parts[] = "-v {$hostPath}:{$containerPath}";
            }
        }

        // Environment variables
        if (isset($config['environment'])) {
            foreach ($config['environment'] as $key => $value) {
                $parts[] = "-e {$key}=\"{$value}\"";
            }
        }

        // Network
        if (isset($config['network'])) {
            $parts[] = "--network {$config['network']}";
        }

        // Restart policy
        if (isset($config['restart'])) {
            $parts[] = "--restart {$config['restart']}";
        }

        // Memory limit
        if (isset($config['memory'])) {
            $parts[] = "--memory {$config['memory']}";
        }

        // CPU limit
        if (isset($config['cpus'])) {
            $parts[] = "--cpus {$config['cpus']}";
        }

        // Privileged
        if (isset($config['privileged']) && $config['privileged']) {
            $parts[] = "--privileged";
        }

        // Working directory
        if (isset($config['working_dir'])) {
            $parts[] = "--workdir {$config['working_dir']}";
        }

        // Labels
        if (isset($config['labels'])) {
            foreach ($config['labels'] as $key => $value) {
                $parts[] = "--label {$key}=\"{$value}\"";
            }
        }

        // Image (required)
        $parts[] = $config['image'];

        // Command
        if (isset($config['command'])) {
            $parts[] = $config['command'];
        }

        return implode(' ', $parts);
    }

    /**
     * Start a container
     */
    public function startContainer(DockerContainer $container): bool
    {
        $command = "docker start {$container->container_id}";
        $this->sshService->execute($container->server, $command);

        $this->syncContainer($container->server, $container->container_id);

        Log::info("Container {$container->name} started");

        return true;
    }

    /**
     * Stop a container
     */
    public function stopContainer(DockerContainer $container, int $timeout = 10): bool
    {
        $command = "docker stop -t {$timeout} {$container->container_id}";
        $this->sshService->execute($container->server, $command);

        $this->syncContainer($container->server, $container->container_id);

        Log::info("Container {$container->name} stopped");

        return true;
    }

    /**
     * Restart a container
     */
    public function restartContainer(DockerContainer $container, int $timeout = 10): bool
    {
        $command = "docker restart -t {$timeout} {$container->container_id}";
        $this->sshService->execute($container->server, $command);

        $this->syncContainer($container->server, $container->container_id);

        Log::info("Container {$container->name} restarted");

        return true;
    }

    /**
     * Remove a container
     */
    public function removeContainer(DockerContainer $container, bool $force = false, bool $volumes = false): bool
    {
        $flags = [];
        if ($force) $flags[] = '-f';
        if ($volumes) $flags[] = '-v';

        $command = "docker rm " . implode(' ', $flags) . " {$container->container_id}";
        $this->sshService->execute($container->server, $command);

        $container->delete();

        Log::info("Container {$container->name} removed");

        return true;
    }

    /**
     * Get container logs
     */
    public function getLogs(DockerContainer $container, int $lines = 100, bool $follow = false): string
    {
        $flags = ["--tail {$lines}"];
        if ($follow) $flags[] = '-f';

        $command = "docker logs " . implode(' ', $flags) . " {$container->container_id}";
        
        return $this->sshService->execute($container->server, $command);
    }

    /**
     * Get container stats
     */
    public function getStats(DockerContainer $container): array
    {
        $command = "docker stats --no-stream --format json {$container->container_id}";
        $output = $this->sshService->execute($container->server, $command);

        $stats = json_decode($output, true);

        if ($stats) {
            $parsedStats = [
                'cpu_percentage' => (float) str_replace('%', '', $stats['CPUPerc'] ?? '0'),
                'memory_usage' => $this->parseMemoryString($stats['MemUsage'] ?? '0B'),
                'memory_percentage' => (float) str_replace('%', '', $stats['MemPerc'] ?? '0'),
                'network_io' => $stats['NetIO'] ?? '0B / 0B',
                'block_io' => $stats['BlockIO'] ?? '0B / 0B',
            ];

            // Update container stats
            $container->update([
                'stats' => $parsedStats,
                'stats_updated_at' => now(),
            ]);

            return $parsedStats;
        }

        return [];
    }

    /**
     * Execute command in container
     */
    public function executeCommand(DockerContainer $container, string $command, bool $interactive = false): string
    {
        $flags = $interactive ? '-it' : '';
        $dockerCommand = "docker exec {$flags} {$container->container_id} {$command}";

        return $this->sshService->execute($container->server, $dockerCommand);
    }

    /**
     * List images on server
     */
    public function listImages(Server $server): array
    {
        $command = 'docker images --format json';
        $output = $this->sshService->execute($server, $command);

        $images = [];
        $lines = array_filter(explode("\n", $output));

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if ($data) {
                $images[] = [
                    'id' => $data['ID'] ?? null,
                    'repository' => $data['Repository'] ?? null,
                    'tag' => $data['Tag'] ?? null,
                    'size' => $data['Size'] ?? null,
                    'created' => $data['CreatedSince'] ?? null,
                ];
            }
        }

        return $images;
    }

    /**
     * Pull an image
     */
    public function pullImage(Server $server, string $image, ?string $tag = null): bool
    {
        $fullImage = $tag ? "{$image}:{$tag}" : $image;
        $command = "docker pull {$fullImage}";

        $this->sshService->execute($server, $command);

        Log::info("Pulled image {$fullImage} on server {$server->name}");

        return true;
    }

    /**
     * Remove an image
     */
    public function removeImage(Server $server, string $imageId, bool $force = false): bool
    {
        $flags = $force ? '-f' : '';
        $command = "docker rmi {$flags} {$imageId}";

        $this->sshService->execute($server, $command);

        Log::info("Removed image {$imageId} from server {$server->name}");

        return true;
    }

    /**
     * List volumes on server
     */
    public function listVolumes(Server $server): array
    {
        $command = 'docker volume ls --format json';
        $output = $this->sshService->execute($server, $command);

        $volumes = [];
        $lines = array_filter(explode("\n", $output));

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if ($data) {
                $volumes[] = [
                    'name' => $data['Name'] ?? null,
                    'driver' => $data['Driver'] ?? null,
                ];
            }
        }

        return $volumes;
    }

    /**
     * Create a volume
     */
    public function createVolume(Server $server, string $name, ?string $driver = null): bool
    {
        $driverFlag = $driver ? "--driver {$driver}" : '';
        $command = "docker volume create {$driverFlag} {$name}";

        $this->sshService->execute($server, $command);

        Log::info("Created volume {$name} on server {$server->name}");

        return true;
    }

    /**
     * Remove a volume
     */
    public function removeVolume(Server $server, string $name, bool $force = false): bool
    {
        $flags = $force ? '-f' : '';
        $command = "docker volume rm {$flags} {$name}";

        $this->sshService->execute($server, $command);

        Log::info("Removed volume {$name} from server {$server->name}");

        return true;
    }

    /**
     * List networks on server
     */
    public function listNetworks(Server $server): array
    {
        $command = 'docker network ls --format json';
        $output = $this->sshService->execute($server, $command);

        $networks = [];
        $lines = array_filter(explode("\n", $output));

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if ($data) {
                $networks[] = [
                    'id' => $data['ID'] ?? null,
                    'name' => $data['Name'] ?? null,
                    'driver' => $data['Driver'] ?? null,
                    'scope' => $data['Scope'] ?? null,
                ];
            }
        }

        return $networks;
    }

    /**
     * Create a network
     */
    public function createNetwork(Server $server, string $name, ?string $driver = null): bool
    {
        $driverFlag = $driver ? "--driver {$driver}" : '';
        $command = "docker network create {$driverFlag} {$name}";

        $this->sshService->execute($server, $command);

        Log::info("Created network {$name} on server {$server->name}");

        return true;
    }

    /**
     * Remove a network
     */
    public function removeNetwork(Server $server, string $name): bool
    {
        $command = "docker network rm {$name}";

        $this->sshService->execute($server, $command);

        Log::info("Removed network {$name} from server {$server->name}");

        return true;
    }

    /**
     * Deploy using docker-compose
     */
    public function dockerComposeUp(Server $server, string $path, bool $detached = true, bool $build = false): string
    {
        $flags = [];
        if ($detached) $flags[] = '-d';
        if ($build) $flags[] = '--build';

        $command = "cd {$path} && docker-compose up " . implode(' ', $flags);

        return $this->sshService->execute($server, $command);
    }

    /**
     * Stop docker-compose services
     */
    public function dockerComposeDown(Server $server, string $path, bool $volumes = false): string
    {
        $flags = $volumes ? '-v' : '';
        $command = "cd {$path} && docker-compose down {$flags}";

        return $this->sshService->execute($server, $command);
    }

    /**
     * Helper: Extract image tag
     */
    private function extractImageTag(string $image): string
    {
        if (str_contains($image, ':')) {
            return explode(':', $image)[1];
        }
        
        return 'latest';
    }

    /**
     * Helper: Parse port bindings
     */
    private function parsePortBindings(array $ports): array
    {
        $bindings = [];

        foreach ($ports as $containerPort => $hostBindings) {
            if (is_array($hostBindings)) {
                foreach ($hostBindings as $binding) {
                    if (isset($binding['HostPort'])) {
                        $bindings[] = [
                            'host' => $binding['HostPort'],
                            'container' => str_replace('/tcp', '', $containerPort),
                        ];
                    }
                }
            }
        }

        return $bindings;
    }

    /**
     * Helper: Parse volumes
     */
    private function parseVolumes(array $mounts): array
    {
        $volumes = [];

        foreach ($mounts as $mount) {
            $volumes[] = [
                'type' => $mount['Type'] ?? 'bind',
                'source' => $mount['Source'] ?? null,
                'destination' => $mount['Destination'] ?? null,
                'mode' => $mount['Mode'] ?? 'rw',
            ];
        }

        return $volumes;
    }

    /**
     * Helper: Get network name
     */
    private function getNetworkName(array $networks): ?string
    {
        if (empty($networks)) {
            return null;
        }

        return array_key_first($networks);
    }

    /**
     * Helper: Parse memory string to bytes
     */
    private function parseMemoryString(string $memString): int
    {
        // Format: "123.4MiB / 2GiB" - extract first part
        $parts = explode(' / ', $memString);
        $usage = $parts[0] ?? '0B';

        // Parse value and unit
        preg_match('/^([\d.]+)([A-Za-z]+)$/', $usage, $matches);
        
        $value = (float) ($matches[1] ?? 0);
        $unit = strtoupper($matches[2] ?? 'B');

        $multipliers = [
            'B' => 1,
            'KIB' => 1024,
            'MIB' => 1024 * 1024,
            'GIB' => 1024 * 1024 * 1024,
            'KB' => 1000,
            'MB' => 1000 * 1000,
            'GB' => 1000 * 1000 * 1000,
        ];

        return (int) ($value * ($multipliers[$unit] ?? 1));
    }
}
