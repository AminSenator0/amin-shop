<?php

namespace App\Services;

use App\Exceptions\FileUploadException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function upload(UploadedFile $file, string $preset): string
    {
        $this->validate($file, $preset);

        $config = $this->presetConfig($preset);
        $disk = $this->disk();
        $outputExtension = $this->outputExtension($config);
        $filename = $this->secureFilename($file, $outputExtension);
        $path = $config['directory'].'/'.$filename;

        if (isset($config['resize']) && is_array($config['resize'])) {
            $contents = $this->resizeImage($file, $config['resize']);
            Storage::disk($disk)->put($path, $contents);

            return $path;
        }

        return $file->storeAs($config['directory'], $filename, $disk);
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    public function uploadMany(array $files, string $preset): array
    {
        return array_map(fn (UploadedFile $file) => $this->upload($file, $preset), $files);
    }

    public function replace(UploadedFile $file, ?string $oldPath, string $preset): string
    {
        $path = $this->upload($file, $preset);

        $this->delete($oldPath);

        return $path;
    }

    public function normalizeStored(string $path, string $preset): string
    {
        $config = $this->presetConfig($preset);

        if (! isset($config['resize']) || ! is_array($config['resize'])) {
            return $path;
        }

        $disk = $this->disk();

        if ($this->isUnsafePath($path) || ! Storage::disk($disk)->exists($path)) {
            return $path;
        }

        $fullPath = Storage::disk($disk)->path($path);
        $contents = $this->resizeImageFromPath($fullPath, $config['resize']);
        $extension = $this->outputExtension($config) ?? pathinfo($path, PATHINFO_EXTENSION) ?: 'webp';
        $newPath = $config['directory'].'/'.Str::uuid().'.'.$extension;

        Storage::disk($disk)->put($newPath, $contents);
        $this->delete($path);

        return $newPath;
    }

    public function delete(?string $path, ?string $disk = null): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        if ($this->isUnsafePath($path)) {
            return false;
        }

        $disk = $disk ?? $this->disk();

        if (! Storage::disk($disk)->exists($path)) {
            return false;
        }

        return Storage::disk($disk)->delete($path);
    }

    public function url(?string $path): ?string
    {
        if ($path === null || $path === '' || $this->isUnsafePath($path)) {
            return null;
        }

        $disk = $this->disk();

        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $filesystem */
        $filesystem = Storage::disk($disk);

        return $filesystem->url($path);
    }

    public function validate(UploadedFile $file, string $preset): void
    {
        if (! $file->isValid()) {
            throw new FileUploadException('فایل آپلود شده معتبر نیست.');
        }

        $config = $this->presetConfig($preset);
        $maxBytes = (int) $config['max_size_kb'] * 1024;

        if ($file->getSize() > $maxBytes) {
            throw new FileUploadException('حجم فایل بیش از حد مجاز است.');
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, config('uploads.blocked_extensions', []), true)) {
            throw new FileUploadException('نوع فایل مجاز نیست.');
        }

        $detectedMime = $this->detectMimeType($file);

        if (! array_key_exists($detectedMime, config('uploads.mime_extensions', []))) {
            throw new FileUploadException('نوع فایل مجاز نیست.');
        }

        $allowedMimes = $this->allowedMimeTypes($config['mimes']);

        if (! in_array($detectedMime, $allowedMimes, true)) {
            throw new FileUploadException('فرمت فایل مجاز نیست.');
        }

        $clientMime = $file->getMimeType();

        if ($clientMime && $clientMime !== $detectedMime && ! $this->isCompatibleMime($clientMime, $detectedMime)) {
            throw new FileUploadException('نوع فایل با محتوای آن مطابقت ندارد.');
        }

        if (! $this->isValidImage($file)) {
            throw new FileUploadException('فایل تصویر معتبر نیست.');
        }

        $this->validateImageDimensions($file, $config);
    }

    private function secureFilename(UploadedFile $file, ?string $forcedExtension = null): string
    {
        if ($forcedExtension !== null) {
            return Str::uuid()->toString().'.'.$forcedExtension;
        }

        $detectedMime = $this->detectMimeType($file);
        $extension = config("uploads.mime_extensions.{$detectedMime}", 'bin');

        return Str::uuid()->toString().'.'.$extension;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function outputExtension(array $config): ?string
    {
        $resize = $config['resize'] ?? null;

        if (! is_array($resize)) {
            return null;
        }

        $format = $resize['format'] ?? null;

        return is_string($format) && $format !== '' ? $format : null;
    }

    /**
     * @param  array<string, mixed>  $resize
     */
    private function resizeImage(UploadedFile $file, array $resize): string
    {
        return $this->resizeImageFromPath($file->getRealPath(), $resize);
    }

    /**
     * @param  array<string, mixed>  $resize
     */
    private function resizeImageFromPath(string $path, array $resize): string
    {
        if (! extension_loaded('gd')) {
            throw new FileUploadException('پردازش تصویر در سرور فعال نیست.');
        }

        $info = @getimagesize($path);

        if (! is_array($info) || ! isset($info[0], $info[1], $info[2])) {
            throw new FileUploadException('فایل تصویر معتبر نیست.');
        }

        [$width, $height, $type] = $info;
        $source = $this->createImageResource($path, $type);

        if ($source === false) {
            throw new FileUploadException('فایل تصویر معتبر نیست.');
        }

        $targetWidth = max(1, (int) ($resize['width'] ?? 200));
        $targetHeight = max(1, (int) ($resize['height'] ?? 200));
        $fit = (string) ($resize['fit'] ?? 'contain');

        $this->enableAlpha($source);

        $canvas = $fit === 'cover'
            ? $this->resizeCover($source, $width, $height, $targetWidth, $targetHeight)
            : $this->resizeContain($source, $width, $height, $targetWidth, $targetHeight);

        imagedestroy($source);

        $format = (string) ($resize['format'] ?? 'webp');
        $quality = max(1, min(100, (int) ($resize['quality'] ?? 85)));

        ob_start();
        $saved = match ($format) {
            'png' => imagepng($canvas, null, 6),
            'jpeg', 'jpg' => imagejpeg($canvas, null, $quality),
            default => function_exists('imagewebp') ? imagewebp($canvas, null, $quality) : false,
        };

        $contents = ob_get_clean();
        imagedestroy($canvas);

        if ($saved === false || ! is_string($contents) || $contents === '') {
            throw new FileUploadException('ذخیره تصویر با خطا مواجه شد.');
        }

        return $contents;
    }

    private function resizeContain(\GdImage $source, int $width, int $height, int $maxWidth, int $maxHeight): \GdImage
    {
        $scale = min($maxWidth / $width, $maxHeight / $height, 1.0);
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        if ($canvas === false) {
            throw new FileUploadException('پردازش تصویر با خطا مواجه شد.');
        }

        $this->prepareCanvas($canvas);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $canvas;
    }

    private function resizeCover(\GdImage $source, int $width, int $height, int $targetWidth, int $targetHeight): \GdImage
    {
        $scale = max($targetWidth / $width, $targetHeight / $height);
        $scaledWidth = max(1, (int) round($width * $scale));
        $scaledHeight = max(1, (int) round($height * $scale));

        $scaled = imagecreatetruecolor($scaledWidth, $scaledHeight);

        if ($scaled === false) {
            throw new FileUploadException('پردازش تصویر با خطا مواجه شد.');
        }

        $this->prepareCanvas($scaled);
        imagecopyresampled($scaled, $source, 0, 0, 0, 0, $scaledWidth, $scaledHeight, $width, $height);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($canvas === false) {
            imagedestroy($scaled);
            throw new FileUploadException('پردازش تصویر با خطا مواجه شد.');
        }

        $this->prepareCanvas($canvas);

        $cropX = max(0, (int) floor(($scaledWidth - $targetWidth) / 2));
        $cropY = max(0, (int) floor(($scaledHeight - $targetHeight) / 2));

        imagecopy($canvas, $scaled, 0, 0, $cropX, $cropY, $targetWidth, $targetHeight);
        imagedestroy($scaled);

        return $canvas;
    }

    private function createImageResource(string $path, int $type): \GdImage|false
    {
        return match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    private function enableAlpha(\GdImage $image): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    private function prepareCanvas(\GdImage $image): void
    {
        $this->enableAlpha($image);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);

        if ($transparent !== false) {
            imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $transparent);
        }
    }

    private function detectMimeType(UploadedFile $file): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file->getRealPath());

        return is_string($mime) ? $mime : 'application/octet-stream';
    }

    private function isValidImage(UploadedFile $file): bool
    {
        $info = @getimagesize($file->getRealPath());

        return is_array($info) && isset($info[0], $info[1]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function validateImageDimensions(UploadedFile $file, array $config): void
    {
        $info = @getimagesize($file->getRealPath());

        if (! is_array($info)) {
            throw new FileUploadException('فایل تصویر معتبر نیست.');
        }

        [$width, $height] = $info;

        if (isset($config['resize']) && is_array($config['resize'])) {
            return;
        }

        if (isset($config['max_width']) && $width > $config['max_width']) {
            throw new FileUploadException('عرض تصویر بیش از حد مجاز است.');
        }

        if (isset($config['max_height']) && $height > $config['max_height']) {
            throw new FileUploadException('ارتفاع تصویر بیش از حد مجاز است.');
        }
    }

    /**
     * @param  array<int, string>  $mimes
     * @return array<int, string>
     */
    private function allowedMimeTypes(array $mimes): array
    {
        $map = config('uploads.mime_extensions', []);
        $allowed = [];

        foreach ($mimes as $mime) {
            $normalized = match ($mime) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => null,
            };

            if ($normalized !== null) {
                $allowed[] = $normalized;
            }
        }

        return array_values(array_unique($allowed));
    }

    private function isCompatibleMime(string $clientMime, string $detectedMime): bool
    {
        if ($clientMime === $detectedMime) {
            return true;
        }

        $jpegMimes = ['image/jpeg', 'image/jpg', 'image/pjpeg'];

        return in_array($clientMime, $jpegMimes, true)
            && in_array($detectedMime, $jpegMimes, true);
    }

    private function isUnsafePath(string $path): bool
    {
        return str_contains($path, '..') || str_starts_with($path, '/');
    }

    private function disk(): string
    {
        return (string) config('uploads.disk', 'public');
    }

    /**
     * @return array<string, mixed>
     */
    private function presetConfig(string $preset): array
    {
        $config = config("uploads.presets.{$preset}");

        if (! is_array($config)) {
            throw new FileUploadException("تنظیمات آپلود [{$preset}] تعریف نشده است.");
        }

        return $config;
    }
}
