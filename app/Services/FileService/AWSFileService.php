<?php

namespace App\Services\FileService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Traits\Response;

class AwsFileService
{
    use Response;

    public function upload(UploadedFile $file, string $folder, array $data): JsonResponse
    {
        try {
            $path = Storage::disk('s3')->put($folder, $file);
            $url = Storage::disk('s3')->url($path);

            return $this->successResponse([
                'url' => $url,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(['error' => $e->getMessage()]);
        }
    }
}
