<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\AdminUser;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

echo "Starting test...\n";

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$user = AdminUser::where('email', 'admin1@gmail.com')->first();
$token = JWTAuth::fromUser($user);

// Trigger a PUT request to update profile
$response = $kernel->handle(
    Illuminate\Http\Request::create('/api/v1/profile', 'PUT', [
        'fullName' => 'Admin Updated',
        'phone' => '0123456789',
    ], [], [], [
        'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        'HTTP_ACCEPT' => 'application/json',
    ])
);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";

// The terminate method should run after the kernel handles the request
// In a real request, it's called by the server. Here we might need to call it manually or rely on the kernel's terminate()
$kernel->terminate(Illuminate\Http\Request::capture(), $response);

echo "\nLatest Audit Log:\n";
$latestLog = DB::table('ipa_audit_log')->orderBy('id', 'desc')->first();
print_r($latestLog);
