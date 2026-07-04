<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controller = new App\Http\Controllers\CalendarController();

foreach (['US', 'CA', 'IN'] as $country) {
    $request = Illuminate\Http\Request::create('/calendar/holidays', 'GET', [
        'country' => $country,
        'year' => 2026,
    ]);
    $response = $controller->holidays($request);
    $data = json_decode($response->getContent(), true);
    echo $country . ' 2026: ' . count($data['holidays']) . " holidays\n";
    foreach (array_slice($data['holidays'], 0, 5) as $h) {
        echo '  ' . $h['date'] . '  ' . $h['name'] . ' (' . $h['type'] . ")\n";
    }
}

// Sanity: US Thanksgiving 2026 must be Nov 26; Canada Victoria Day 2026 must be May 18.
$us = json_decode($controller->holidays(Illuminate\Http\Request::create('/', 'GET', ['country' => 'US', 'year' => 2026]))->getContent(), true);
$ca = json_decode($controller->holidays(Illuminate\Http\Request::create('/', 'GET', ['country' => 'CA', 'year' => 2026]))->getContent(), true);

$find = fn ($list, $name) => collect($list['holidays'])->firstWhere('name', $name)['date'] ?? 'MISSING';
echo "\nChecks:\n";
echo '  US Thanksgiving 2026 = ' . $find($us, 'Thanksgiving Day') . " (expect 2026-11-26)\n";
echo '  CA Victoria Day 2026 = ' . $find($ca, 'Victoria Day') . " (expect 2026-05-18)\n";
echo '  CA Family Day 2026 = ' . $find($ca, 'Family Day') . " (expect 2026-02-16)\n";
echo '  US MLK Day 2026 = ' . $find($us, 'Martin Luther King Jr. Day') . " (expect 2026-01-19)\n";
