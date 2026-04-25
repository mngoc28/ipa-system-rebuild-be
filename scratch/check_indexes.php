<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['ipa_delegation', 'ipa_user', 'ipa_auth_session'];
foreach ($tables as $table) {
    echo "Indexes for $table:\n";
    $indexes = DB::select("SELECT indexname, indexdef FROM pg_indexes WHERE tablename = ?", [$table]);
    foreach ($indexes as $idx) {
        echo "- {$idx->indexname}: {$idx->indexdef}\n";
    }
    echo "\n";
}
