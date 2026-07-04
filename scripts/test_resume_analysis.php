<?php

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

$app = require_once $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

set_time_limit(300);

$jd = file_get_contents(storage_path('app/test_jd.txt'));
$pdf = $root . '/Dice_Resume_CV_Mohammad_Nazmus_Sakib.pdf';

if (! is_readable($pdf)) {
    fwrite(STDERR, "PDF not found: {$pdf}\n");
    exit(1);
}

echo "JD length: " . strlen($jd) . " chars\n";
echo "PDF: {$pdf}\n";
echo "AI status: " . json_encode(app(App\Services\Ai\AiManager::class)->status()) . "\n\n";

$start = microtime(true);

try {
    $result = app(App\Services\Resume\ResumeAnalysisService::class)->analyze($jd, $pdf);
    $elapsed = round(microtime(true) - $start, 1);

    echo "=== SUCCESS in {$elapsed}s ===\n";
    echo "Provider: {$result['provider']} / {$result['model']}\n";
    echo str_repeat('-', 60) . "\n";
    echo $result['analysis'] . "\n";
} catch (Throwable $e) {
    $elapsed = round(microtime(true) - $start, 1);
    fwrite(STDERR, "=== FAILED after {$elapsed}s ===\n");
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
