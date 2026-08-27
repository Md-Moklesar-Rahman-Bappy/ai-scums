<?php

use App\Models\User;
use App\Services\AI\Tools\AdminAdmissionStatsTool;
use App\Services\AI\Tools\AdminOutstandingFeesTool;
use App\Services\Tenant\TenantManager;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$tenant = app(TenantManager::class);
$tenant->setCurrentTenantId(1);

$user = User::where('institution_id', 1)->first() ?? new User(['institution_id' => 1]);

$fees = (new AdminOutstandingFeesTool)->execute($user);
$stats = (new AdminAdmissionStatsTool)->execute($user);

echo 'Fees tenant scope data: '.json_encode($fees['data']).PHP_EOL;
echo 'Admission tenant scope data: '.json_encode($stats['data']).PHP_EOL;

$tenant->setCurrentTenantId(null);
$fees2 = (new AdminOutstandingFeesTool)->execute($user);
echo 'Null-tenant fees (should be empty): '.json_encode($fees2).PHP_EOL;
