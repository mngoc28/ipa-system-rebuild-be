<?php

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$usernameOrEmail = 'manager1@gmail.com';
$password = '111111';

echo "Record counts:\n";
echo "ipa_user: " . DB::table('ipa_user')->count() . "\n";
echo "ipa_user_role: " . DB::table('ipa_user_role')->count() . "\n";
echo "ipa_role_permission: " . DB::table('ipa_role_permission')->count() . "\n";
echo "ipa_org_unit: " . DB::table('ipa_org_unit')->count() . "\n";
echo "-------------------\n";

// 1. Database Query
$start = microtime(true);
$user = AdminUser::query()
    ->with(['roles', 'roles.permissions', 'unit'])
    ->where('email', $usernameOrEmail)
    ->orWhere('username', $usernameOrEmail)
    ->first();
$end = microtime(true);
echo "DB Query time: " . ($end - $start) . " seconds\n";

if (!$user) {
    echo "User not found\n";
    exit;
}

// 2. Hash Check
$start = microtime(true);
$check = Hash::check($password, $user->password);
$end = microtime(true);
echo "Hash::check time: " . ($end - $start) . " seconds\n";

// 3. toArray (which triggers accessors)
$start = microtime(true);
$userData = $user->toArray();
$end = microtime(true);
echo "toArray() time: " . ($end - $start) . " seconds\n";

// 4. JWT generation
$start = microtime(true);
$token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
$end = microtime(true);
echo "JWT generation time: " . ($end - $start) . " seconds\n";

// 5. Session insert
$start = microtime(true);
DB::table('ipa_auth_session')->insert([
    'user_id' => $user->id,
    'access_token_jti' => \Illuminate\Support\Str::random(40),
    'refresh_token_hash' => hash('sha256', \Illuminate\Support\Str::random(60)),
    'ip_address' => '127.0.0.1',
    'user_agent' => 'Console',
    'issued_at' => now(),
    'expires_at' => now()->addMinutes(config('jwt.refresh_ttl', 20160)),
    'created_at' => now(),
    'updated_at' => now(),
]);
$end = microtime(true);
echo "Session insert time: " . ($end - $start) . " seconds\n";
