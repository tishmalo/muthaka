<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function storeImage(UploadedFile $file, string $directory): array
    {
        $this->validateImage($file);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs(trim($directory, '/'), $filename, 'local');

        [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];

        return [
            'image_path' => $path,
            'thumbnail_path' => $this->makeThumbnail($file, $directory, $filename),
            'width' => $width,
            'height' => $height,
        ];
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }

    public function validateImage(UploadedFile $file): void
    {
        $maxKilobytes = (int) config('tuko.media.max_upload_kb', 5120);
        $allowed = config('tuko.media.allowed_mimes', ['image/jpeg', 'image/png', 'image/webp']);

        if (!$file->isValid()) {
            abort(422, 'Uploaded image is invalid');
        }

        if (!in_array($file->getMimeType(), $allowed, true)) {
            abort(422, 'Unsupported image type');
        }

        if (($file->getSize() / 1024) > $maxKilobytes) {
            abort(422, 'Image is too large');
        }
    }

    private function makeThumbnail(UploadedFile $file, string $directory, string $filename): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        $size = @getimagesize($file->getRealPath());
        if (!$size) {
            return null;
        }

        [$width, $height] = $size;
        $max = 512;
        $ratio = min($max / max($width, 1), $max / max($height, 1), 1);
        $thumbWidth = max(1, (int) round($width * $ratio));
        $thumbHeight = max(1, (int) round($height * $ratio));

        $source = match ($file->getMimeType()) {
            'image/jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : null,
            default => null,
        };

        if (!$source) {
            return null;
        }

        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

        $thumbPath = trim($directory, '/').'/thumbs/'.pathinfo($filename, PATHINFO_FILENAME).'.jpg';
        $temp = tempnam(sys_get_temp_dir(), 'tuko-thumb-');
        imagejpeg($thumb, $temp, 82);

        Storage::disk('local')->put($thumbPath, file_get_contents($temp));
        @unlink($temp);
        imagedestroy($thumb);
        imagedestroy($source);

        return $thumbPath;
    }
}
