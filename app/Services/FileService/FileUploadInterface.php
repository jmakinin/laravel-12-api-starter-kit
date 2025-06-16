<?php

namespace App\Services\FileService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

interface FileUploadInterface
{
    /**
     * Upload a file to the storage service
     */
    public function upload(UploadedFile $file, string $folder, array $data = []): JsonResponse;

    /**
     * Delete a file from the storage service
     */
    // public function delete(string $identifier): JsonResponse;
}
