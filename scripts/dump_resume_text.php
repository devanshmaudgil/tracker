<?php
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$t = app(App\Services\Resume\PdfTextExtractor::class)->extractFromPath($root . '/Dice_Resume_CV_Mohammad_Nazmus_Sakib.pdf');
file_put_contents(storage_path('app/test_resume_extract.txt'), $t);
echo strlen($t) . " chars written\n";
