<?php

namespace App\Constants;

class FileUploadConstants
{
    public const UPLOAD_SUCCESS = 'File uploaded successfully';
    public const UPLOAD_FAILURE = 'File upload failed';
    public const INVALID_FILE_TYPE = 'Invalid file type';
    public const FILE_TOO_LARGE = 'File size exceeds the limit';
    public const FILE_NOT_FOUND = 'File not found';
    public const MAX_FILE_SIZE = 5242880; // 5 MB in bytes
    public const ALLOWED_FILE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'video/mp4'
    ];
    public const UPLOAD_DIRECTORY = 'uploads/files/';
    public const PROFILE_UPLOADS = 'uploads/profiles';
    public const DOCUMENT_UPLOADS = 'uploads/documents';
    public const MEDIA_UPLOADS = 'uploads/media';
    public const GENERAL_UPLOADS = 'uploads/general';
}
