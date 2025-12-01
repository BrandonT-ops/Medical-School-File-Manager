<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    protected string $provider;
    protected $client;

    public function __construct()
    {
        $this->provider = env('STORAGE_PROVIDER', 'local');

        if ($this->provider === 'gdrive') {
            $this->initializeGoogleDrive();
        }
    }

    /**
     * Initialize Google Drive client
     */
    protected function initializeGoogleDrive()
    {
        if (!class_exists('\Google_Client')) {
            throw new \Exception('Google API Client not installed. Run: composer require google/apiclient');
        }

        $this->client = new \Google_Client();
        $this->client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $this->client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
    }

    /**
     * Get the current storage provider
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Store a file
     */
    public function store(UploadedFile $file, string $path = 'uploads'): array
    {
        $filename = $this->generateUniqueFilename($file);
        $fullPath = $path . '/' . $filename;

        switch ($this->provider) {
            case 'gdrive':
                return $this->storeToGoogleDrive($file, $filename, $fullPath);

            case 'local':
            default:
                return $this->storeLocally($file, $path, $filename);
        }
    }

    /**
     * Store file locally
     */
    protected function storeLocally(UploadedFile $file, string $path, string $filename): array
    {
        $fullPath = $file->storeAs($path, $filename, 'public');

        return [
            'provider' => 'local',
            'filename' => $filename,
            'filepath' => $fullPath,
            'external_id' => null,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
        ];
    }

    /**
     * Store file to Google Drive
     */
    protected function storeToGoogleDrive(UploadedFile $file, string $filename, string $fullPath): array
    {
        if (!$this->client) {
            throw new \Exception('Google Drive client not initialized');
        }

        $service = new \Google_Service_Drive($this->client);

        $fileMetadata = new \Google_Service_Drive_DriveFile([
            'name' => $filename,
            'parents' => [env('GOOGLE_DRIVE_FOLDER_ID')],
        ]);

        $content = file_get_contents($file->getRealPath());

        $uploadedFile = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $file->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);

        return [
            'provider' => 'gdrive',
            'filename' => $filename,
            'filepath' => $fullPath,
            'external_id' => $uploadedFile->id,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
        ];
    }

    /**
     * Download a file
     */
    public function download(string $filepath, ?string $externalId = null)
    {
        switch ($this->provider) {
            case 'gdrive':
                return $this->downloadFromGoogleDrive($externalId);

            case 'local':
            default:
                return $this->downloadLocally($filepath);
        }
    }

    /**
     * Download file locally
     */
    protected function downloadLocally(string $filepath)
    {
        if (!Storage::disk('public')->exists($filepath)) {
            throw new \Exception('File not found');
        }

        return Storage::disk('public')->path($filepath);
    }

    /**
     * Download file from Google Drive
     */
    protected function downloadFromGoogleDrive(string $fileId)
    {
        if (!$this->client) {
            throw new \Exception('Google Drive client not initialized');
        }

        $service = new \Google_Service_Drive($this->client);
        $response = $service->files->get($fileId, ['alt' => 'media']);

        $tempPath = storage_path('app/temp/' . Str::random(40));
        file_put_contents($tempPath, $response->getBody()->getContents());

        return $tempPath;
    }

    /**
     * Delete a file
     */
    public function delete(string $filepath, ?string $externalId = null): bool
    {
        switch ($this->provider) {
            case 'gdrive':
                return $this->deleteFromGoogleDrive($externalId);

            case 'local':
            default:
                return $this->deleteLocally($filepath);
        }
    }

    /**
     * Delete file locally
     */
    protected function deleteLocally(string $filepath): bool
    {
        if (Storage::disk('public')->exists($filepath)) {
            return Storage::disk('public')->delete($filepath);
        }

        return false;
    }

    /**
     * Delete file from Google Drive
     */
    protected function deleteFromGoogleDrive(string $fileId): bool
    {
        if (!$this->client) {
            throw new \Exception('Google Drive client not initialized');
        }

        $service = new \Google_Service_Drive($this->client);

        try {
            $service->files->delete($fileId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get file URL
     */
    public function getUrl(string $filepath, ?string $externalId = null): string
    {
        switch ($this->provider) {
            case 'gdrive':
                return "https://drive.google.com/file/d/{$externalId}/view";

            case 'local':
            default:
                return Storage::disk('public')->url($filepath);
        }
    }

    /**
     * Check if file exists
     */
    public function exists(string $filepath, ?string $externalId = null): bool
    {
        switch ($this->provider) {
            case 'gdrive':
                return $this->existsOnGoogleDrive($externalId);

            case 'local':
            default:
                return Storage::disk('public')->exists($filepath);
        }
    }

    /**
     * Check if file exists on Google Drive
     */
    protected function existsOnGoogleDrive(string $fileId): bool
    {
        if (!$this->client) {
            return false;
        }

        $service = new \Google_Service_Drive($this->client);

        try {
            $service->files->get($fileId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate unique filename
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitized = Str::slug($originalName);

        return $sanitized . '_' . time() . '_' . Str::random(8) . '.' . $extension;
    }

    /**
     * Get storage info
     */
    public function getStorageInfo(): array
    {
        return [
            'provider' => $this->provider,
            'max_file_size' => env('MAX_FILE_SIZE', 10485760), // 10MB default
            'allowed_extensions' => explode(',', env('ALLOWED_EXTENSIONS', 'pdf,doc,docx,jpg,png')),
        ];
    }
}
