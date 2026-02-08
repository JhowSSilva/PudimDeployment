<?php

namespace App\Http\Controllers;

use App\Models\BackupConfiguration;
use App\Models\BackupFile;
use App\Services\Backup\Storage\StorageManager;
use Illuminate\Http\Request;

class BackupFileController extends Controller
{
    public function __construct(
        private StorageManager $storageManager
    ) {}

    /**
     * Display files for a backup configuration
     */
    public function index(BackupConfiguration $backup)
    {
        $this->authorize('view', $backup);

        $files = $backup->files()
            ->with('job')
            ->latest()
            ->paginate(20);

        return view('backups.files', compact('backup', 'files'));
    }

    /**
     * Download a backup file
     */
    public function download(BackupFile $file)
    {
        $this->authorize('view', $file->configuration);

        try {
            $disk = $this->storageManager->getDisk(
                $file->storage_provider,
                $file->configuration->storage_credentials
            );

            $stream = $disk->readStream($file->storage_path);

            return response()->stream(function() use ($stream) {
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $file->filename . '"',
                'Content-Length' => $file->file_size,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download file: ' . $e->getMessage());
        }
    }

    /**
     * Delete a backup file
     */
    public function destroy(BackupFile $file)
    {
        $this->authorize('delete', $file->configuration);

        try {
            $disk = $this->storageManager->getDisk(
                $file->storage_provider,
                $file->configuration->storage_credentials
            );

            $disk->delete($file->storage_path);
            $file->delete();

            return back()->with('success', 'Backup file deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete file: ' . $e->getMessage());
        }
    }
}
