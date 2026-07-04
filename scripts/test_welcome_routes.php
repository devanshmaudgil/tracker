<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\UserLogin;
use Illuminate\Support\Facades\Auth;

$login = UserLogin::first();
if (!$login) {
    echo "SKIP: no user in DB\n";
    exit(0);
}

Auth::login($login);

$routes = [
    'GET /welcome' => '/welcome',
    'GET /guide' => '/guide',
    'GET /tracker' => '/tracker/info',
];

foreach ($routes as $label => $uri) {
    $request = Illuminate\Http\Request::create($uri, 'GET');
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    echo $label . ': HTTP ' . $status;
    if ($status >= 300 && $status < 400) {
        echo ' -> ' . $response->headers->get('Location');
    }
    echo PHP_EOL;
    $kernel->terminate($request, $response);
}
