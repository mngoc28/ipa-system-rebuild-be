<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::create('/', 'GET'));

use Illuminate\Support\Facades\DB;

echo "--- ROLES ---\n";
$roles = DB::table('ipa_role')->get();
foreach ($roles as $r) {
    echo "ID: {$r->id} | Code: {$r->code} | Name: {$r->name}\n";
}

echo "\n--- PERMISSIONS (First 20) ---\n";
$permissions = DB::table('ipa_permission')->limit(20)->get();
foreach ($permissions as $p) {
    echo "ID: {$p->id} | Code: {$p->code} | Name: {$p->name}\n";
}

echo "\n--- PERMISSION COUNT ---\n";
echo DB::table('ipa_permission')->count() . " permissions found.\n";

echo "\n--- ROLE-PERMISSION MAPPINGS ---\n";
$mappings = DB::table('ipa_role_permission')
    ->join('ipa_role', 'ipa_role_permission.role_id', '=', 'ipa_role.id')
    ->join('ipa_permission', 'ipa_role_permission.permission_id', '=', 'ipa_permission.id')
    ->select('ipa_role.code as role_code', 'ipa_permission.code as perm_code')
    ->limit(20)
    ->get();
foreach ($mappings as $m) {
    echo "Role: {$m->role_code} -> Perm: {$m->perm_code}\n";
}

echo "\n--- USER SAMPLES ---\n";
$users = DB::table('ipa_user')->limit(5)->get();
foreach ($users as $u) {
    $roleCodes = DB::table('ipa_user_role')
        ->join('ipa_role', 'ipa_user_role.role_id', '=', 'ipa_role.id')
        ->where('ipa_user_role.user_id', $u->id)
        ->pluck('ipa_role.code')
        ->toArray();
    echo "User: {$u->username} | Roles: " . implode(', ', $roleCodes) . "\n";
}
