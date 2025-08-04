<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

echo "<pre>Clearing cache...\n";

$kernel->call('config:clear');
echo "✔ Config cleared\n";

$kernel->call('cache:clear');
echo "✔ App cache cleared\n";

$kernel->call('route:clear');
echo "✔ Route cache cleared\n";

$kernel->call('view:clear');
echo "✔ View cache cleared\n";

$kernel->call('config:cache');
echo "✔ Config re-cached\n";

echo "All done!</pre>";
