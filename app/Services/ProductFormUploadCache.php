<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductFormUploadCache
{
    public const SESSION_KEY = 'product_form_uploads';

    public function storeFromRequest(Request $request): void
    {
        $previous = $this->get();
        $token = $previous['token'] ?? (string) Str::uuid();
        $directory = "temp/product-form/{$token}";

        $previousItems = collect($previous['items'] ?? [])->keyBy('key');
        $items = [];
        $rewrittenSort = [];
        $sort = json_decode($request->input('image_sort', '[]'), true) ?: [];
        $newFiles = array_values($request->file('images', []) ?? []);
        $primary = $request->input('primary_image');

        if ($sort !== []) {
            foreach ($sort as $entry) {
                if (! is_string($entry)) {
                    continue;
                }

                if (str_starts_with($entry, 'existing:')) {
                    $rewrittenSort[] = $entry;

                    continue;
                }

                if (str_starts_with($entry, 'cached:')) {
                    $key = substr($entry, 7);
                    $cached = $previousItems->get($key);

                    if (is_array($cached)) {
                        $items[] = $cached;
                        $rewrittenSort[] = $entry;
                    }

                    continue;
                }

                if (str_starts_with($entry, 'new:')) {
                    $index = (int) substr($entry, 4);

                    if (! isset($newFiles[$index]) || ! $newFiles[$index] instanceof UploadedFile) {
                        continue;
                    }

                    $stored = $this->storeUploadedFile($newFiles[$index], $directory);
                    $items[] = $stored;
                    $cachedToken = 'cached:'.$stored['key'];
                    $rewrittenSort[] = $cachedToken;

                    if ($primary === $entry) {
                        $primary = $cachedToken;
                    }
                }
            }
        } else {
            foreach ($newFiles as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $stored = $this->storeUploadedFile($file, $directory);
                $items[] = $stored;
                $rewrittenSort[] = 'cached:'.$stored['key'];
            }

            if ($request->hasFile('image')) {
                $stored = $this->storeUploadedFile($request->file('image'), $directory);
                array_unshift($items, $stored);
                array_unshift($rewrittenSort, 'cached:'.$stored['key']);

                if ($primary === null || $primary === '') {
                    $primary = 'cached:'.$stored['key'];
                }
            }
        }

        if ($primary !== null && str_starts_with((string) $primary, 'new:')) {
            $index = (int) substr((string) $primary, 4);
            $cachedToken = $rewrittenSort[$index] ?? null;

            if (is_string($cachedToken) && str_starts_with($cachedToken, 'cached:')) {
                $primary = $cachedToken;
            } elseif (isset($items[$index]['key'])) {
                $primary = 'cached:'.$items[$index]['key'];
            }
        }

        $this->deleteUnusedFiles($previous['items'] ?? [], $items);

        session([
            self::SESSION_KEY => [
                'token' => $token,
                'product_id' => $request->route('product')?->id,
                'items' => $items,
                'image_sort' => json_encode(array_values($rewrittenSort), JSON_UNESCAPED_UNICODE),
                'primary_image' => $primary,
                'remove_images' => $request->input('remove_images', []),
            ],
        ]);
    }

    public function get(): ?array
    {
        $data = session(self::SESSION_KEY);

        if (! is_array($data)) {
            return null;
        }

        $items = collect($data['items'] ?? [])
            ->filter(fn (array $item) => isset($item['path']) && Storage::disk('local')->exists($item['path']))
            ->values()
            ->all();

        if ($items === []) {
            return ($data['items'] ?? []) === [] ? $data : null;
        }

        $validKeys = collect($items)->pluck('key')->filter()->all();
        $sort = json_decode($data['image_sort'] ?? '[]', true) ?: [];
        $sort = array_values(array_filter($sort, function ($entry) use ($validKeys) {
            if (! is_string($entry) || ! str_starts_with($entry, 'cached:')) {
                return true;
            }

            return in_array(substr($entry, 7), $validKeys, true);
        }));

        $primary = $data['primary_image'] ?? null;

        if (is_string($primary) && str_starts_with($primary, 'cached:')) {
            $key = substr($primary, 7);

            if (! in_array($key, $validKeys, true)) {
                $primary = isset($sort[0]) ? (str_starts_with($sort[0], 'cached:') ? $sort[0] : null) : null;
            }
        }

        return [
            ...$data,
            'items' => $items,
            'image_sort' => json_encode($sort, JSON_UNESCAPED_UNICODE),
            'primary_image' => $primary,
        ];
    }

    public function hasItems(): bool
    {
        return count($this->get()['items'] ?? []) > 0;
    }

    public function getPathByKey(string $key): ?string
    {
        foreach ($this->get()['items'] ?? [] as $item) {
            if (($item['key'] ?? null) === $key) {
                return $item['path'] ?? null;
            }
        }

        return null;
    }

    public function clear(): void
    {
        $data = $this->get();

        if ($data !== null) {
            Storage::disk('local')->deleteDirectory("temp/product-form/{$data['token']}");
        }

        session()->forget(self::SESSION_KEY);
    }

    public function promote(string $tempPath, FileUploadService $uploader): string
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($tempPath)) {
            throw new \RuntimeException('Temporary upload not found.');
        }

        $tempFile = $disk->path($tempPath);
        $uploadedFile = new UploadedFile(
            $tempFile,
            basename($tempPath),
            mime_content_type($tempFile) ?: null,
            null,
            true
        );

        $path = $uploader->upload($uploadedFile, 'product');
        $disk->delete($tempPath);

        return $path;
    }

    /**
     * @param  array<int, array<string, mixed>>  $previousItems
     * @param  array<int, array<string, mixed>>  $nextItems
     */
    private function deleteUnusedFiles(array $previousItems, array $nextItems): void
    {
        $keptPaths = collect($nextItems)->pluck('path')->filter()->all();
        $disk = Storage::disk('local');

        foreach ($previousItems as $item) {
            $path = $item['path'] ?? null;

            if ($path && ! in_array($path, $keptPaths, true) && $disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }

    /**
     * @return array{key: string, path: string, name: string}
     */
    private function storeUploadedFile(UploadedFile $file, string $directory): array
    {
        return [
            'key' => (string) Str::uuid(),
            'path' => $file->store($directory, 'local'),
            'name' => $file->getClientOriginalName(),
        ];
    }
}
