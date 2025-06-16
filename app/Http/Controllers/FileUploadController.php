<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FileService\FileUploadService;
use App\Constants\FileUploadConstants;
use Illuminate\Http\UploadedFile;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index(Request $request)
    {


        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,docx,mp4|max:10240', // adjust as needed
        ]);

        try {

            $file = $request->file('file');

            // Upload to Cloudinary using Storage facade
            $path = Storage::disk('cloudinary')->put('uploads', $file);

            // Get the full URL
            $url = Storage::disk('cloudinary')->url($path);

            return response()->json([
                'status' => true,
                'url' => $url,
                'path' => $path,
                'message' => 'File uploaded successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }

        //   return $this->fileUploadService->upload($file->getRealPath(), FileUploadConstants::PROFILE_UPLOADS, [$request->user(), $request->upload_type]);

    }
}
