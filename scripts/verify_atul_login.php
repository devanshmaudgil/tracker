<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UserLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

$user = UserLogin::where('username', 'AtulGautam')->with('staffUser')->first();

if (!$user) {
    echo "FAIL: AtulGautam not found\n";
    exit(1);
}

echo "UserLogin ID: {$user->id}\n";
echo "Username: {$user->username}\n";
echo "Staff user: " . ($user->staffUser?->username ?? 'none') . "\n";
echo "Staff email: " . ($user->staffUser?->email ?? 'none') . "\n";
echo "Password check: " . (Hash::check('Atul.G@RADiiX', $user->password) ? 'OK' : 'FAIL') . "\n";
echo "Auth attempt: " . (Auth::attempt(['username' => 'AtulGautam', 'password' => 'Atul.G@RADiiX']) ? 'OK' : 'FAIL') . "\n";
