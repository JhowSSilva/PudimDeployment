<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\TerminalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileTransferController extends Controller
{
    /**
     * Upload file to server
     */
    public function upload(Server $server, Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB max
            'remote_path' => 'required|string|max:500',
        ]);

        try {
            $terminal = new TerminalService($server);
            
            if (!$terminal->connect()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to connect to server'
                ], 500);
            }

            $file = $request->file('file');
            $localPath = $file->getRealPath();
            $remotePath = $request->remote_path;
            
            // Ensure remote directory exists
            $remoteDir = dirname($remotePath);
            $terminal->execute("mkdir -p {$remoteDir}");
            
            // Upload via SFTP
            $sftp = $terminal->getSftp();
            $success = $sftp->put($remotePath, $localPath, \phpseclib3\Net\SFTP::SOURCE_LOCAL_FILE);
            
            if ($success) {
                // Set proper permissions
                $terminal->execute("chmod 644 {$remotePath}");
                
                $terminal->disconnect();
                
                return response()->json([
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'path' => $remotePath,
                    'size' => $file->getSize()
                ]);
            } else {
                throw new \Exception('SFTP upload failed');
            }

        } catch (\Exception $e) {
            Log::error('File upload error', [
                'server_id' => $server->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download file from server
     */
    public function download(Server $server, Request $request)
    {
        $request->validate([
            'remote_path' => 'required|string|max:500',
        ]);

        try {
            $terminal = new TerminalService($server);
            
            if (!$terminal->connect()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to connect to server'
                ], 500);
            }

            $remotePath = $request->remote_path;
            
            // Get file info
            $fileInfo = $terminal->execute("stat -c '%s %n' {$remotePath}");
            if (empty($fileInfo)) {
                throw new \Exception('File not found');
            }

            // Download via SFTP
            $sftp = $terminal->getSftp();
            $content = $sftp->get($remotePath);
            
            if ($content === false) {
                throw new \Exception('Failed to download file');
            }

            $terminal->disconnect();
            
            $filename = basename($remotePath);
            
            return response()->streamDownload(function() use ($content) {
                echo $content;
            }, $filename);

        } catch (\Exception $e) {
            Log::error('File download error', [
                'server_id' => $server->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List files in directory
     */
    public function list(Server $server, Request $request)
    {
        $request->validate([
            'path' => 'string|max:500',
        ]);

        try {
            $terminal = new TerminalService($server);
            
            if (!$terminal->connect()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to connect to server'
                ], 500);
            }

            $path = $request->get('path', '~');
            
            // List files with details
            $output = $terminal->execute("ls -lAh --time-style=long-iso {$path}");
            
            $lines = explode("\n", trim($output));
            $files = [];
            
            // Skip first line (total)
            array_shift($lines);
            
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                
                $parts = preg_split('/\s+/', $line, 9);
                if (count($parts) < 9) continue;
                
                $files[] = [
                    'permissions' => $parts[0],
                    'type' => substr($parts[0], 0, 1) === 'd' ? 'directory' : 'file',
                    'size' => $parts[4],
                    'date' => $parts[5] . ' ' . $parts[6],
                    'name' => $parts[8],
                ];
            }
            
            $terminal->disconnect();
            
            return response()->json([
                'success' => true,
                'path' => $path,
                'files' => $files
            ]);

        } catch (\Exception $e) {
            Log::error('File list error', [
                'server_id' => $server->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete file on server
     */
    public function delete(Server $server, Request $request)
    {
        $request->validate([
            'remote_path' => 'required|string|max:500',
        ]);

        try {
            $terminal = new TerminalService($server);
            
            if (!$terminal->connect()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to connect to server'
                ], 500);
            }

            $remotePath = $request->remote_path;
            
            // Delete file
            $output = $terminal->execute("rm -f {$remotePath}");
            
            $terminal->disconnect();
            
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('File delete error', [
                'server_id' => $server->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
