<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$user = User::create([
    'first_name' => 'Test',
    'last_name' => 'User',
    'email' => 'test@example.com',
    'password' => bcrypt('password123'),
    'role' => 'user'
]);

echo "Created user ID: {$user->id}\n";
echo "Email: {$user->email}\n";
echo "Role: {$user->role}\n";