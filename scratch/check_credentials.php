<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::create('/', 'GET')); // Boot providers

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

$email = 'director1@gmail.com';
$password = 'password';

$user = AdminUser::where('email', $email)->orWhere('username', $email)->first();

if (!$user) {
    echo "User not found\n";
    exit;
}

echo "User found: " . $user->email . "\n";
echo "Password hash: " . $user->password . "\n";

if (Hash::check($password, $user->password)) {
    echo "Password check: SUCCESS\n";
} else {
    echo "Password check: FAILED\n";
}
