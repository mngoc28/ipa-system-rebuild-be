<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::create('/', 'GET'));

use App\Models\AdminUser;
use Illuminate\Support\Facades\DB;

function verifyUser(string $email)
{
    $user = AdminUser::where('email', $email)->first();
    if (!$user) {
        echo "User {$email} not found.\n";
        return;
    }

    echo "--- User: {$user->username} ---\n";
    echo "Primary Role: {$user->role}\n";

    $roles = $user->roles->pluck('code')->toArray();
    echo "All Roles: " . implode(', ', $roles) . "\n";

    echo "Check hasRole('DIRECTOR'): " . ($user->hasRole('DIRECTOR') ? "YES" : "NO") . "\n";
    echo "Check hasRole('ADMIN'): " . ($user->hasRole('ADMIN') ? "YES" : "NO") . "\n";

    echo "Check hasPermission('delegation:manage'): " . ($user->hasPermission('delegation:manage') ? "YES" : "NO") . "\n";
    echo "Check hasPermission('system:settings'): " . ($user->hasPermission('system:settings') ? "YES" : "NO") . "\n";
    echo "\n";
}

echo "VERIFYING ADMIN\n";
$admin = AdminUser::where('username', 'admin')->first();
if ($admin) {
    verifyUser($admin->email);
} else {
    echo "Admin user not found.\n";
}

echo "VERIFYING DIRECTOR\n";
verifyUser('director1@gmail.com');

echo "VERIFYING STAFF\n";
verifyUser('staff1@gmail.com');
