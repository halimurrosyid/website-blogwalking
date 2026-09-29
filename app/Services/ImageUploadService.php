<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageUploadService
{
    /**
     * Allowed image MIME types.
     *
     * @var array<string>
     */
    protected static array $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/bmp',
    ];

    /**
     * Process an image (UploadedFile or base64 data string), compress, and save as WebP.
     * Includes security validation against web shells, polyglots, and invalid image files.
     *
     * @return string Relative storage path (e.g. screenshots/2026/09/uuid.webp)
     *
     * @throws ValidationException
     */
    public function storeScreenshot(UploadedFile|string $image): string
    {
        $yearMonth = date('Y/m');
        $directory = "screenshots/{$yearMonth}";
        $filename = Str::uuid()->toString().'.webp';
        $relativeFilePath = "{$directory}/{$filename}";

        // Ensure storage directory exists
        Storage::disk('public')->makeDirectory($directory);
        $fullDestinationPath = Storage::disk('public')->path($relativeFilePath);

        $tempPath = null;

        try {
            if ($image instanceof UploadedFile) {
                if (! $image->isValid()) {
                    throw ValidationException::withMessages([
                        'screenshot' => 'File bukti screenshot gagal diunggah ke server.',
                    ]);
                }

                $sourcePath = $image->getRealPath();
                $imageInfo = @getimagesize($sourcePath);

                if ($imageInfo === false || ! in_array($imageInfo['mime'], self::$allowedMimes, true)) {
                    throw ValidationException::withMessages([
                        'screenshot' => 'File yang diunggah bukan gambar valid (PNG, JPG, WebP, GIF).',
                    ]);
                }

                $mime = $imageInfo['mime'];
            } else {
                // Validate base64 string length (max ~15MB base64)
                if (strlen($image) > 15 * 1024 * 1024) {
                    throw ValidationException::withMessages([
                        'screenshot' => 'Ukuran data screenshot clipboard melebihi batas maksimal 10MB.',
                    ]);
                }

                $data = $image;
                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                    $data = substr($data, strpos($data, ',') + 1);
                }

                $binaryData = base64_decode($data, true);
                if ($binaryData === false || empty($binaryData)) {
                    throw ValidationException::withMessages([
                        'screenshot' => 'Format data base64 screenshot tidak valid.',
                    ]);
                }

                $imageInfo = @getimagesizefromstring($binaryData);
                if ($imageInfo === false || ! in_array($imageInfo['mime'], self::$allowedMimes, true)) {
                    throw ValidationException::withMessages([
                        'screenshot' => 'Data gambar dari clipboard tidak valid atau rusak.',
                    ]);
                }

                $mime = $imageInfo['mime'];
                $tempPath = tempnam(sys_get_temp_dir(), 'img_paste_');
                file_put_contents($tempPath, $binaryData);
                $sourcePath = $tempPath;
            }

            // Attempt GD optimization to WebP (re-encoding strips any malicious EXIF or embedded scripts)
            if (extension_loaded('gd') && function_exists('imagewebp')) {
                $imageResource = match (true) {
                    str_contains($mime, 'jpeg') || str_contains($mime, 'jpg') => @imagecreatefromjpeg($sourcePath),
                    str_contains($mime, 'png') => @imagecreatefrompng($sourcePath),
                    str_contains($mime, 'webp') => @imagecreatefromwebp($sourcePath),
                    str_contains($mime, 'bmp') => @imagecreatefrombmp($sourcePath),
                    default => @imagecreatefromstring(file_get_contents($sourcePath)),
                };

                if ($imageResource) {
                    $width = imagesx($imageResource);
                    $height = imagesy($imageResource);
                    $maxWidth = 1600;

                    if ($width > $maxWidth) {
                        $newWidth = $maxWidth;
                        $newHeight = (int) ($height * ($maxWidth / $width));
                        $resized = imagecreatetruecolor($newWidth, $newHeight);

                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);

                        imagecopyresampled($resized, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        imagedestroy($imageResource);
                        $imageResource = $resized;
                    }

                    imagewebp($imageResource, $fullDestinationPath, 80);
                    imagedestroy($imageResource);

                    return $relativeFilePath;
                }
            }

            // Secure Fallback: Store using guaranteed safe extension mapped from verified MIME type
            $safeExt = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => 'png',
            };

            $fallbackFilename = Str::uuid()->toString().'.'.$safeExt;
            $fallbackRelative = "{$directory}/{$fallbackFilename}";

            Storage::disk('public')->put($fallbackRelative, file_get_contents($sourcePath));

            return $fallbackRelative;
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
}
