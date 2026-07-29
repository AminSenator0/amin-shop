<?php

namespace Database\Seeders\Support;

use App\Support\BannerImages;
use App\Support\BlogImages;
use App\Support\CategoryImages;
use App\Support\ProductImages;
use App\Support\SliderImages;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SeedImageDownloader
{
    private const ASSETS_DIR = __DIR__.'/../assets/images';

    private const PRODUCT_RESIZE = [
        'width' => 800,
        'height' => 800,
        'fit' => 'cover',
        'format' => 'webp',
        'quality' => 85,
    ];

    private const CATEGORY_RESIZE = [
        'width' => 600,
        'height' => 600,
        'fit' => 'cover',
        'format' => 'webp',
        'quality' => 85,
    ];

    /** @var array<int, string>|null */
    private ?array $assetFiles = null;

    public function productForCategory(string $categoryName, int $variant = 0): ?string
    {
        $index = CategoryImages::assetIndexForName($categoryName) + $variant;
        $basename = sprintf('product-cat-%02d-v%d', $index, $variant);

        return $this->store($index, 'products', $basename, self::PRODUCT_RESIZE);
    }

    /** @return array<int, string> */
    public function productGalleryForItem(string $sku, string $productName, bool $force = false): array
    {
        $indices = ProductImages::galleryAssetIndices($sku, $productName);
        $paths = [];

        foreach ($indices as $index => $assetIndex) {
            $basename = ProductImages::storageBasename($sku, $index);
            $path = $this->storeWithForce($assetIndex, 'products', $basename, self::PRODUCT_RESIZE, $force);

            if ($path !== null) {
                $paths[] = $path;
            }
        }

        return array_values(array_unique($paths));
    }

    public function product(int $index): ?string
    {
        return $this->store($index, 'products', 'product-'.$index, self::PRODUCT_RESIZE);
    }

    public function categoryForName(string $categoryName, ?string $slug = null, bool $force = false): ?string
    {
        $index = CategoryImages::assetIndexForName($categoryName);
        $basename = CategoryImages::storageBasename($categoryName, $slug);

        return $this->storeWithForce($index, 'categories', $basename, self::CATEGORY_RESIZE, $force);
    }

    public function category(int $index): ?string
    {
        return $this->store($index + 2, 'categories', 'category-'.$index, self::CATEGORY_RESIZE);
    }

    public function brand(int $index, string $slug): ?string
    {
        foreach (['svg', 'png', 'webp'] as $extension) {
            $logoPath = public_path('images/brands/'.$slug.'.'.$extension);

            if (! is_file($logoPath)) {
                continue;
            }

            return $this->storeFromPath($logoPath, 'brands', 'brand-'.$slug, [
                'width' => 200,
                'height' => 200,
                'fit' => 'contain',
                'format' => 'webp',
                'quality' => 90,
            ], force: true);
        }

        return $this->store($index + 4, 'brands', 'brand-'.$slug, [
            'width' => 200,
            'height' => 200,
            'fit' => 'contain',
            'format' => 'webp',
            'quality' => 85,
        ]);
    }

    private const SLIDER_RESIZE = [
        'width' => 1600,
        'height' => 900,
        'fit' => 'cover',
        'format' => 'webp',
        'quality' => 85,
    ];

    public function sliderForTitle(string $title, bool $force = false): ?string
    {
        $index = SliderImages::assetIndexForTitle($title);
        $basename = SliderImages::storageBasename($title);

        return $this->storeWithForce($index, 'sliders', $basename, self::SLIDER_RESIZE, $force);
    }

    public function slider(int $index): ?string
    {
        return $this->store($index, 'sliders', 'slider-'.$index, self::SLIDER_RESIZE);
    }

    private const BANNER_RESIZE = [
        'width' => 1200,
        'height' => 400,
        'fit' => 'cover',
        'format' => 'webp',
        'quality' => 85,
    ];

    public function bannerForTitle(string $title, bool $force = false): ?string
    {
        $index = BannerImages::assetIndexForTitle($title);
        $basename = BannerImages::storageBasename($title);

        return $this->storeWithForce($index, 'banners', $basename, self::BANNER_RESIZE, $force);
    }

    public function banner(int $index): ?string
    {
        return $this->store($index + 3, 'banners', 'banner-'.$index, self::BANNER_RESIZE);
    }

    private const BLOG_RESIZE = [
        'width' => 1200,
        'height' => 675,
        'fit' => 'cover',
        'format' => 'webp',
        'quality' => 85,
    ];

    public function blogForTitle(string $title, bool $force = false): ?string
    {
        $index = BlogImages::assetIndexForTitle($title);
        $basename = BlogImages::storageBasename($title);

        return $this->storeWithForce($index, 'blog', $basename, self::BLOG_RESIZE, $force);
    }

    public function blog(int $index): ?string
    {
        return $this->store($index + 1, 'blog', 'blog-'.$index, self::BLOG_RESIZE);
    }

    public function storeLogo(): ?string
    {
        $source = public_path('images/store-logo.svg');

        if (is_file($source)) {
            $path = 'store/logo.svg';
            Storage::disk('public')->put($path, file_get_contents($source));

            return $path;
        }

        return $this->store(0, 'store', 'logo', [
            'width' => 512,
            'height' => 512,
            'fit' => 'contain',
            'format' => 'png',
            'quality' => 90,
        ]);
    }

    public function storeFavicon(?string $logoPath = null): ?string
    {
        if ($logoPath && str_ends_with(strtolower($logoPath), '.svg')) {
            return null;
        }

        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            return $this->storeFromPath(
                Storage::disk('public')->path($logoPath),
                'store',
                'favicon',
                [
                    'width' => 128,
                    'height' => 128,
                    'fit' => 'contain',
                    'format' => 'png',
                    'quality' => 90,
                ],
                force: true,
            );
        }

        return $this->storeFromPath(
            public_path('images/store-logo.svg'),
            'store',
            'favicon',
            [
                'width' => 128,
                'height' => 128,
                'fit' => 'contain',
                'format' => 'png',
                'quality' => 90,
            ],
            force: true,
        ) ?? $this->store(0, 'store', 'favicon', [
            'width' => 128,
            'height' => 128,
            'fit' => 'contain',
            'format' => 'png',
            'quality' => 90,
        ]);
    }

    public function enamad(): ?string
    {
        return $this->store(8, 'store', 'enamad', [
            'width' => 120,
            'height' => 120,
            'fit' => 'contain',
            'format' => 'png',
            'quality' => 90,
        ]);
    }

    /**
     * @param  array<string, mixed>  $resize
     */
    private function storeWithForce(int $index, string $directory, string $basename, array $resize, bool $force = false): ?string
    {
        $extension = ($resize['format'] ?? 'webp') === 'png' ? 'png' : 'webp';
        $path = trim($directory, '/').'/'.$basename.'.'.$extension;
        $disk = Storage::disk('public');

        if ($force && $disk->exists($path)) {
            $disk->delete($path);
        }

        return $this->store($index, $directory, $basename, $resize);
    }

    /**
     * @param  array<string, mixed>  $resize
     */
    private function store(int $index, string $directory, string $basename, array $resize): ?string
    {
        $extension = ($resize['format'] ?? 'webp') === 'png' ? 'png' : 'webp';
        $path = trim($directory, '/').'/'.$basename.'.'.$extension;
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return $path;
        }

        $source = $this->assetPath($index);

        if ($source === null) {
            return null;
        }

        return $this->storeFromPath($source, $directory, $basename, $resize);
    }

    /**
     * @param  array<string, mixed>  $resize
     */
    private function storeFromPath(string $source, string $directory, string $basename, array $resize, bool $force = false): ?string
    {
        $extension = ($resize['format'] ?? 'webp') === 'png' ? 'png' : 'webp';
        $path = trim($directory, '/').'/'.$basename.'.'.$extension;
        $disk = Storage::disk('public');

        if ($disk->exists($path) && ! $force) {
            return $path;
        }

        $contents = $this->resizeFromPath($source, $resize);

        if ($contents === null) {
            return null;
        }

        $disk->makeDirectory(trim($directory, '/'));
        $disk->put($path, $contents);

        return $path;
    }

    private function assetPath(int $index): ?string
    {
        $files = $this->assetFiles();

        if ($files === []) {
            return null;
        }

        $file = $files[$index % count($files)];

        return is_file($file) ? $file : null;
    }

    /** @return array<int, string> */
    private function assetFiles(): array
    {
        if ($this->assetFiles !== null) {
            return $this->assetFiles;
        }

        if (! is_dir(self::ASSETS_DIR)) {
            $this->assetFiles = [];

            return $this->assetFiles;
        }

        $this->assetFiles = collect(File::files(self::ASSETS_DIR))
            ->filter(fn ($file) => preg_match('/\.(jpe?g|png|webp)$/i', $file->getFilename()) === 1)
            ->sortBy(fn ($file) => $file->getFilename())
            ->map(fn ($file) => $file->getPathname())
            ->values()
            ->all();

        return $this->assetFiles;
    }

    /**
     * @param  array<string, mixed>  $resize
     */
    private function resizeFromPath(string $path, array $resize): ?string
    {
        if (! extension_loaded('gd')) {
            return file_get_contents($path) ?: null;
        }

        $info = @getimagesize($path);

        if (! is_array($info) || ! isset($info[0], $info[1], $info[2])) {
            return null;
        }

        [$width, $height, $type] = $info;
        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if ($source === false) {
            return null;
        }

        $targetWidth = max(1, (int) ($resize['width'] ?? 800));
        $targetHeight = max(1, (int) ($resize['height'] ?? 600));
        $fit = (string) ($resize['fit'] ?? 'cover');
        $format = (string) ($resize['format'] ?? 'webp');
        $quality = max(1, min(100, (int) ($resize['quality'] ?? 85)));

        $canvas = $fit === 'contain'
            ? $this->resizeContain($source, $width, $height, $targetWidth, $targetHeight)
            : $this->resizeCover($source, $width, $height, $targetWidth, $targetHeight);

        imagedestroy($source);

        ob_start();
        $saved = match ($format) {
            'png' => imagepng($canvas, null, 6),
            'jpeg', 'jpg' => imagejpeg($canvas, null, $quality),
            default => function_exists('imagewebp') ? imagewebp($canvas, null, $quality) : false,
        };

        $contents = ob_get_clean();
        imagedestroy($canvas);

        if ($saved === false || ! is_string($contents) || $contents === '') {
            return null;
        }

        return $contents;
    }

    private function resizeContain(\GdImage $source, int $width, int $height, int $maxWidth, int $maxHeight): \GdImage
    {
        $scale = min($maxWidth / $width, $maxHeight / $height, 1.0);
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
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
        $this->prepareCanvas($scaled);
        imagecopyresampled($scaled, $source, 0, 0, 0, 0, $scaledWidth, $scaledHeight, $width, $height);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        $this->prepareCanvas($canvas);

        $cropX = max(0, (int) floor(($scaledWidth - $targetWidth) / 2));
        $cropY = max(0, (int) floor(($scaledHeight - $targetHeight) / 2));

        imagecopy($canvas, $scaled, 0, 0, $cropX, $cropY, $targetWidth, $targetHeight);
        imagedestroy($scaled);

        return $canvas;
    }

    private function prepareCanvas(\GdImage $image): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);

        if ($transparent !== false) {
            imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $transparent);
        }
    }
}
