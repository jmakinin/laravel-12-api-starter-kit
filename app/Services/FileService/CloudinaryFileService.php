<?php

namespace App\Services\FileService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Traits\Response;

class CloudinaryFileService implements FileUploadInterface
{
    use Response;

    /**
     * Upload file to Cloudinary
     */
    public function upload(UploadedFile $file, string $folder, array $data = []): JsonResponse
    {
        try {

            // Upload to Cloudinary using Storage facade
            $path = Storage::disk('cloudinary')->put($folder, $file);

            // Get the full URL
            $url = Storage::disk('cloudinary')->url($path);

            return $this->successResponse([
                'url' => $url,
                'size' => $file->getSize(),
                'path' => $path
            ], "upload successful");
        } catch (\Exception $e) {
            return $this->errorResponse(['error' => $e->getMessage()]);
        }
    }
}
