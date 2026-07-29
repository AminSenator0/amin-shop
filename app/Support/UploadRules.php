<?php

namespace App\Support;

use App\Rules\ValidUploadedFile;
use InvalidArgumentException;

class UploadRules
{
    public static function file(string $preset, bool $required = false): array
    {
        $config = self::preset($preset);

        $rules = $required ? ['required'] : ['nullable'];

        return [
            ...$rules,
            'file',
            'image',
            'mimes:'.implode(',', $config['mimes']),
            'max:'.$config['max_size_kb'],
            new ValidUploadedFile($preset),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function files(string $field, string $preset, int $maxCount = 10): array
    {
        self::preset($preset);

        return [
            $field => ['nullable', 'array', 'max:'.$maxCount],
            "{$field}.*" => self::file($preset, required: true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function preset(string $preset): array
    {
        $config = config("uploads.presets.{$preset}");

        if (! is_array($config)) {
            throw new InvalidArgumentException("Upload preset [{$preset}] is not defined.");
        }

        return $config;
    }
}
