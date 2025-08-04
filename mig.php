<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;

$kernel = $app->make(Kernel::class);

echo "Clearing config...\n";
$kernel->call('config:clear');

// Generate cache table migration
echo "Creating cache table migration...\n";
$kernel->call('cache:table');

// Run all migrations including the new cache one
echo "Running migrations...\n";
$kernel->call('migrate', ['--force' => true]);

echo nl2br($kernel->output());
