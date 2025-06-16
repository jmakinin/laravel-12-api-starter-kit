<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FileService\FileUploadService;

class FileUploadController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index(Request $request)
    {
        return $this->fileUploadService->upload($request->user(), $request->file('file'), $request->all());
    }
}
