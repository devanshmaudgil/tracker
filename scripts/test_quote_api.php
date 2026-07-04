<?php
$urls = [
    'https://api.quotable.io/random?tags=inspirational',
    'https://zenquotes.io/api/random',
    'https://quotegarden.io/api/v3/quotes/random',
];
foreach ($urls as $url) {
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $body = @file_get_contents($url, false, $ctx);
    echo $url . ': ' . ($body ? 'OK - ' . substr($body, 0, 120) : 'FAIL') . PHP_EOL;
}
