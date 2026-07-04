<?php

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$jd = file_get_contents(storage_path('app/test_jd.txt'));
$pdf = $root . '/Dice_Resume_CV_Mohammad_Nazmus_Sakib.pdf';
$resume = app(App\Services\Resume\PdfTextExtractor::class)->extractFromPath($pdf);
$sc = app(App\Services\Resume\ResumeMatchScorer::class)->evaluate($jd, $resume);

echo json_encode($sc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
