<?php

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Facade;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = microtime(true);
$url = Cloudinary::getUrl('avatars/43_PON5M.jpg');
$end = microtime(true);

echo "Cloudinary URL: $url\n";
echo "Time taken: " . ($end - $start) . " seconds\n";
