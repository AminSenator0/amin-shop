<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerFormUploadCache
{
    public const SESSION_KEY = 'banner_form_upload';

    public function storeFromRequest(Request $request): void
    {
        $previous = $this->get();

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                return;
            }

            $token = $previous['token'] ?? (string) Str::uuid();
            $directory = "temp/banner-form/{$token}";

            if ($previous && isset($previous['path'])) {
                Storage::disk('local')->delete($previous['path']);
            }

            $path = $file->store($directory, 'local');

            session([
                self::SESSION_KEY => [
                    'token' => $token,
                    'key' => (string) Str::uuid(),
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                ],
            ]);

            return;
        }

        $cachedKey = $request->input('cached_image');

        if (is_string($cachedKey) && $cachedKey !== '' && $previous && ($previous['key'] ?? '') === $cachedKey) {
            return;
        }
    }

    public function get(): ?array
    {
        $data = session(self::SESSION_KEY);

        if (! is_array($data) || ! isset($data['path'])) {
            return null;
        }

        if (! Storage::disk('local')->exists($data['path'])) {
            session()->forget(self::SESSION_KEY);

            return null;
        }

        return $data;
    }

    public function previewUrl(): ?string
    {
        $data = $this->get();

        return isset($data['key']) ? route('admin.banners.form-upload', $data['key']) : null;
    }

    public function getPathByKey(string $key): ?string
    {
        $data = $this->get();

        if ($data === null || ($data['key'] ?? '') !== $key) {
            return null;
        }

        return $data['path'];
    }

    public function clear(): void
    {
        $data = $this->get();

        if ($data !== null && isset($data['token'])) {
            Storage::disk('local')->deleteDirectory("temp/banner-form/{$data['token']}");
        }

        session()->forget(self::SESSION_KEY);
    }

    public function promote(FileUploadService $uploader): string
    {
        $data = $this->get();

        if ($data === null || ! isset($data['path'])) {
            throw new \RuntimeException('Temporary upload not found.');
        }

        $disk = Storage::disk('local');
        $tempPath = $data['path'];

        if (! $disk->exists($tempPath)) {
            throw new \RuntimeException('Temporary upload not found.');
        }

        $tempFile = $disk->path($tempPath);
        $uploadedFile = new UploadedFile(
            $tempFile,
            $data['name'] ?? basename($tempPath),
            mime_content_type($tempFile) ?: null,
            null,
            true
        );

        $path = $uploader->upload($uploadedFile, 'banner');
        $this->clear();

        return $path;
    }
}
