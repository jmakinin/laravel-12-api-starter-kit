<?php

namespace App\Services\FileService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use App\Constants\FileUploadConstants;
use InvalidArgumentException;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\DB;

class FileUploadService
{
    protected $uploadService;

    public function __construct(string $uploadService = 'cloudinary')
    {
        $this->uploadService = $uploadService;
    }

    public function upload($user, UploadedFile $file, array $data)
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

        return DB::transaction(function () use ($file, $folder, $uploadData, $user, $data) {
            //upload here
            $uploadResponse = match ($this->uploadService) {
                'aws' => (new AwsFileService())->upload($file, $folder, $uploadData),
                'cloudinary' => (new CloudinaryFileService())->upload($file, $folder, $uploadData),
                default => throw new InvalidArgumentException("Invalid upload service: {$this->uploadService}")
            };

            $responseData = $uploadResponse->getData(true);

            if ($responseData['status'] ?? false) {
                // Save file information to database
                $mediaAsset = $this->saveToDatabase($user, $file, $responseData['data'], $data);

                // Add database record to response
                $responseData['data']['media_asset'] = $mediaAsset->toArray();

                return response()->json($responseData, $uploadResponse->getStatusCode());
            }

            return $uploadResponse;
        });

        // Upload using selected service
        return match ($this->uploadService) {
            'aws' => (new AwsFileService())->upload($file, $folder, $uploadData),
            'cloudinary' => (new CloudinaryFileService())->upload($file, $folder, $uploadData),
            default => throw new InvalidArgumentException("Invalid upload service: {$this->uploadService}")
        };
    }

    private function saveToDatabase($user, UploadedFile $file, array $uploadResult, array $requestData): MediaAsset
    {
        return MediaAsset::create([
            'user_id' => $requestData['user_id'],
            'uploaded_by' => $user ? $user->id : null,
            'file_type' => $requestData['upload_type'] ?? 'general',
            'file_name' => time() . '_' . uniqid() . '_' . basename($uploadResult['path'] ?? $uploadResult['public_id'] ?? ''),
            'file_path' => $uploadResult['path'] ?? $uploadResult['public_id'] ?? null,
            'file_url' => $uploadResult['url'],
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
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
