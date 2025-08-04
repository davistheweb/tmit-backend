<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;

$kernel = $app->make(Kernel::class);

echo "Clearing config cache...\n";
$kernel->call('config:clear');

// Optionally clear all caches (uncomment if needed)
// $kernel->call('cache:clear');
// $kernel->call('route:clear');
// $kernel->call('view:clear');

echo "Running migrate:fresh (drops all tables and migrates)...\n";
$kernel->call('migrate:fresh', ['--force' => true]);

echo nl2br($kernel->output());
