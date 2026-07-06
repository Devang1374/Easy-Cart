<?php

namespace App\Services;

use Cloudinary\Cloudinary;

class CloudinaryService
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);
    }

    public function upload($file, $folder = 'easycart')
    {
        return $this->cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => $folder,
            ]
        );
    }

    public function destroy(?string $publicId): bool
    {
        if (empty($publicId)) {
            return false;
        }
    
        $result = $this->cloudinary
            ->uploadApi()
            ->destroy($publicId);
    
        return in_array($result['result'] ?? '', ['ok', 'not found']);
    }
}
