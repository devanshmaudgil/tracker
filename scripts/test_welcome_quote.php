<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controller = new App\Http\Controllers\WelcomeController();
$ref = new ReflectionMethod($controller, 'fetchQuote');
$ref->setAccessible(true);
$quote = $ref->invoke($controller);

echo "Quote: " . $quote['text'] . PHP_EOL;
echo "Author: " . $quote['author'] . PHP_EOL;
