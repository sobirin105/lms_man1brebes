<?php

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Log;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing Department Insertion...\n";
    $dept = Department::create([
        'name' => 'Test Dept ' . time(),
        'code' => 'TEST' . time(),
        'description' => 'Test Description'
    ]);
    echo "Department Created: " . $dept->id . "\n";
} catch (\Exception $e) {
    echo "Department Failure: " . $e->getMessage() . "\n";
}

try {
    echo "Testing User Insertion...\n";
    $user = User::create([
        'name' => 'Test User',
        'email' => 'testuser' . time() . '@example.com',
        'password' => bcrypt('password'),
        'role_id' => 1,
        'is_active' => true
    ]);
    echo "User Created: " . $user->id . "\n";
} catch (\Exception $e) {
    echo "User Failure: " . $e->getMessage() . "\n";
}
