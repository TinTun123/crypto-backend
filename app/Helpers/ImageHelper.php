<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    public static function storeImage($userId, $imageFile, $imageType, $type)
    {
        // Generate a unique filename for the image
        if ($imageType === 'front') {
            $filename = $userId . '_' . 'front' . '.' . $imageFile->getClientOriginalExtension();
        } elseif ($imageType === 'back') {
            $filename = $userId . '_' . 'back' . '.' . $imageFile->getClientOriginalExtension();
        } elseif ($imageType === 'profile') {
            $filename = $userId . '_profile' . '.' . $imageFile->getClientOriginalExtension();
        }


        if ($imageType === 'front') {

            $imagePath = $type . '/' . $userId . '/'. 'idFront' . '/' . $filename;

        } elseif ($imageType === 'back') {

            $imagePath = $type . '/' . $userId . '/'. 'idBack' . '/' . $filename;
            
        } elseif ($imageType === 'profile') {

            $imagePath = $type . '/' . $userId . '/' . $filename;
        }

        if (Storage::exists("public/{$imagePath}")) {
            Storage::delete($imagePath);
        }
        // Store the image in the specified folder under storage/public
        // $imagePath = $userId . '/'. 'idCard' . '/' . $filename;
        Storage::disk('public')->put($imagePath, file_get_contents($imageFile));

        $public_url = asset("storage/{$imagePath}");

        // Return the path to the stored image (relative to the storage/public folder)
        return $public_url;
    }

    public static function getContentTypeFromExtension($extension)
    {
        // Define a mapping of common file extensions to content types
        $extensionToContentType = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'svg' => 'image/svg+xml', // You can add more mappings as needed
        ];

        // Check if the extension exists in the mapping, otherwise default to 'application/octet-stream'
        return $extensionToContentType[$extension] ?? 'application/octet-stream';
    }
}
