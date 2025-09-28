<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\MigrateToZohoCrm;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('migrate:zoho-crm', function () {
    $this->call(MigrateToZohoCrm::class);
})->purpose('Migrate data to Zoho CRM');
