<?php

namespace App\Services\FileService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use App\Constants\FileUploadConstants;
use InvalidArgumentException;

class FileUploadService
{
    protected $uploadService;

    public function __construct(string $uploadService = 'cloudinary')
    {
        $this->uploadService = $uploadService;
    }

    public function upload($user, UploadedFile $file, array $data): JsonResponse
    {
        // Validate request
        $this->validateUpload($file);

        // Determine folder
        $folder = $this->getUploadFolder($data['upload_type'] ?? 'others');

        // Prepare upload data
        $uploadData = [
            'user' => $user,
            'upload_type' => $data['upload_type'] ?? 'general',
            'metadata' => $data
        ];

        // Upload using selected service
        return match ($this->uploadService) {
            'aws' => (new AwsFileService())->upload($file, $folder, $uploadData),
            'cloudinary' => (new CloudinaryFileService())->upload($file, $folder, $uploadData),
            default => throw new InvalidArgumentException("Invalid upload service: {$this->uploadService}")
        };
    }

    private function validateUpload(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('Invalid file');
        }

        if (!in_array($file->getMimeType(), FileUploadConstants::ALLOWED_FILE_TYPES)) {
            throw new InvalidArgumentException('File type not allowed');
        }

        if ($file->getSize() > (FileUploadConstants::MAX_FILE_SIZE * 1024)) {
            throw new InvalidArgumentException('File too large');
        }
    }

    private function getUploadFolder(string $type): string
    {
        return match ($type) {
            'profile' => FileUploadConstants::PROFILE_UPLOADS,
            'document' => FileUploadConstants::DOCUMENT_UPLOADS,
            'media' => FileUploadConstants::MEDIA_UPLOADS,
            default => FileUploadConstants::GENERAL_UPLOADS,
        };
    }
}
