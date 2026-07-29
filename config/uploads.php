<?php

return [

    'disk' => env('UPLOAD_DISK', 'public'),

    'presets' => [

        'product' => [
            'directory' => 'products',
            'mimes' => ['jpeg', 'jpg', 'png', 'webp', 'gif'],
            'max_size_kb' => 2048,
            'max_width' => 4000,
            'max_height' => 4000,
        ],

        'brand_logo' => [
            'directory' => 'brands',
            'mimes' => ['jpeg', 'jpg', 'png', 'webp'],
            'max_size_kb' => 1024,
            'max_width' => 2000,
            'max_height' => 2000,
            'resize' => [
                'width' => 200,
                'height' => 200,
                'format' => 'webp',
                'quality' => 85,
            ],
        ],

        'banner' => [
            'directory' => 'banners',
            'mimes' => ['jpeg', 'jpg', 'png', 'webp'],
            'max_size_kb' => 4096,
            'max_width' => 4000,
            'max_height' => 4000,
        ],

        'slider' => [
            'directory' => 'sliders',
            'mimes' => ['jpeg', 'jpg', 'png', 'webp'],
            'max_size_kb' => 4096,
            'max_width' => 4000,
            'max_height' => 4000,
            'resize' => [
                'width' => 1600,
                'height' => 1200,
                'fit' => 'cover',
                'format' => 'webp',
                'quality' => 85,
            ],
        ],

        'store_logo' => [
            'directory' => 'store',
            'mimes' => ['jpeg', 'jpg', 'png', 'webp'],
            'max_size_kb' => 2048,
            'max_width' => 1000,
            'max_height' => 1000,
        ],

        'store_favicon' => [
            'directory' => 'store',
            'mimes' => ['jpeg', 'jpg', 'png', 'webp'],
            'max_size_kb' => 512,
            'max_width' => 512,
            'max_height' => 512,
        ],

        'blog_image' => [
            'directory' => 'blog',
            'mimes' => ['jpeg', 'jpg', 'png', 'webp'],
            'max_size_kb' => 2048,
            'max_width' => 4000,
            'max_height' => 4000,
            'resize' => [
                'width' => 1200,
                'height' => 675,
                'fit' => 'cover',
                'format' => 'webp',
                'quality' => 85,
            ],
        ],

        'enamad_image' => [
            'directory' => 'store',
            'mimes' => ['jpeg', 'jpg', 'png', 'webp'],
            'max_size_kb' => 512,
            'max_width' => 400,
            'max_height' => 400,
        ],

    ],

    'mime_extensions' => [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ],

    'blocked_extensions' => [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'exe', 'sh', 'bat', 'cmd', 'com', 'msi',
        'js', 'html', 'htm', 'svg', 'xml',
    ],

];
