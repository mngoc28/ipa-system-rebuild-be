<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Support\Facades\Route;

$routes = Route::getRoutes();

echo "--- NEW ROUTE STRUCTURE VERIFICATION ---\n";
$prefixes = ['admin', 'director', 'manager', 'staff'];

foreach ($prefixes as $p) {
    echo "\n[{$p} cluster]\n";
    $count = 0;
    foreach ($routes as $route) {
        if (str_starts_with($route->uri(), "api/v1/{$p}")) {
            echo "- " . $route->uri() . " (" . implode('|', $route->methods()) . ")\n";
            $count++;
            if ($count >= 5) {
                echo "... and more\n";
                break;
            }
        }
    }
    if ($count == 0) {
        echo "No routes found for prefix {$p}\n";
    }
}

echo "\n--- COMMON ROUTES ---\n";
foreach ($routes as $route) {
    $uri = $route->uri();
    if (
        str_starts_with($uri, "api/v1") &&
        !str_starts_with($uri, "api/v1/admin") &&
        !str_starts_with($uri, "api/v1/director") &&
        !str_starts_with($uri, "api/v1/manager") &&
        !str_starts_with($uri, "api/v1/staff")
    ) {
        echo "- {$uri}\n";
    }
}
