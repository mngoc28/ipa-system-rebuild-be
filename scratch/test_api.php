<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminUser;
use Tymon\JWTAuth\Facades\JWTAuth;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$user = AdminUser::where('email', 'admin1@gmail.com')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}

$token = JWTAuth::fromUser($user);
echo "Token: " . $token . "\n\n";

$start = microtime(true);
$response = $kernel->handle(
    Illuminate\Http\Request::create('/api/v1/admin/users', 'GET', [], [], [], [
        'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        'HTTP_ACCEPT' => 'application/json',
    ])
);
$end = microtime(true);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Time: " . ($end - $start) . "s\n";
echo "Content: " . substr($response->getContent(), 0, 500) . "...\n";

$rolesResponse = $kernel->handle(
    Illuminate\Http\Request::create('/api/v1/admin/users/roles', 'GET', [], [], [], [
        'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        'HTTP_ACCEPT' => 'application/json',
    ])
);
echo "\nRoles Status: " . $rolesResponse->getStatusCode() . "\n";
echo "Roles Content: " . $rolesResponse->getContent() . "\n";

$unitsResponse = $kernel->handle(
    Illuminate\Http\Request::create('/api/v1/admin/users/units', 'GET', [], [], [], [
        'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        'HTTP_ACCEPT' => 'application/json',
    ])
);
echo "\nUnits Status: " . $unitsResponse->getStatusCode() . "\n";
echo "Units Content: " . substr($unitsResponse->getContent(), 0, 500) . "...\n";
