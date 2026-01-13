<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$statuses = \DB::table('job_status')->get();
foreach ($statuses as $s) {
    echo "ID: " . $s->id . " - " . $s->status . "\n";
}
