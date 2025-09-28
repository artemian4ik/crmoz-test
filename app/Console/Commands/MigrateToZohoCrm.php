<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Deal;
use App\Services\ZohoCrmService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MigrateToZohoCrm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:zoho-crm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from local database to Zoho CRM';

    private ZohoCrmService $zohoService;
    private array $migratedAccounts = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->zohoService = new ZohoCrmService();

        $this->info('Start migration data to Zoho CRM...');

        $startTime = now();
        $migratedCustomers = 0;
        $migratedDeals = 0;
        $errors = 0;

        try {
            $this->info('Start migration customers...');
            $result = $this->migrateCustomers();
            $migratedCustomers = $result['success'];
            $errors += $result['errors'];
            
            $this->info('Start migration deals...');
            $result = $this->migrateDeals();
            $migratedDeals = $result['success'];
            $errors += $result['errors'];
            

            $endTime = now();
            $duration = $endTime->diffInSeconds($startTime);

            $this->info('Migration completed!');
            $this->info("Statistics:");
            $this->info("1. Migrated customers: {$migratedCustomers}");
            $this->info("2. Migrated deals: {$migratedDeals}");
            $this->info("3. Errors: {$errors}");
            $this->info("4. Execution time: {$duration} seconds");

            if ($errors > 0) {
                $this->warn("There are {$errors} errors. Check the logs for details.");
            }

        } catch (\Exception $e) {
            $this->error('Critical error: ' . $e->getMessage());
            Log::error('Critical error during migration to Zoho CRM', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }


    private function migrateCustomers(): array
    {
        $customers = Customer::all();
        
        $success = 0;
        $errors = 0;
        $progressBar = $this->output->createProgressBar($customers->count());
        $progressBar->start();

        foreach ($customers as $customer) {
            try {
                $accountId = $this->zohoService->getAccountIdByEmail([
                    'Email' => $customer->email,
                    'Account_Name' => $customer->first_name . ' ' . $customer->last_name
                ]);
                if ($accountId) {
                    $this->migratedAccounts[$customer->id] = $accountId;
                    $success++;
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error('Error migrating customer to Zoho CRM', [
                    'customer_id' => $customer->id,
                    'customer_email' => $customer->email,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        return ['success' => $success, 'errors' => $errors];
    }


    private function migrateDeals(): array
    {
        $deals = Deal::all();
        
        $success = 0;
        $errors = 0;
        $progressBar = $this->output->createProgressBar($deals->count());
        $progressBar->start();

        foreach ($deals as $deal) {
            try {
                $manager = $this->zohoService->SelectManagerForSource($deal->source);
                $customer = Customer::find($deal->customer_id);

                $dealId = $this->zohoService->createDeal([
                    'Deal_Name' => $deal->name,
                    'Email' => $customer->email ?? '',
                    'Email_Manager' => $manager->email,
                    'Lead_Source' => $deal->source,
                    'Account_Name' => [
                        'id' => $this->migratedAccounts[$deal->customer_id] ?? ''
                    ]
                ]);

                if ($dealId) {
                    $manager->incrementDealsCount();
                    $manager->refresh();
                    $success++;
                    $this->info('Deal migrated successfully ' . $deal->id . ' | EMail:' . $manager->email);
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error('Error migrating deal to Zoho CRM', [
                    'deal_id' => $deal->id,
                    'deal_name' => $deal->name,
                    'customer_id' => $deal->customer_id,
                    'error' => $e->getMessage()
                ]);
            }

            $this->info('Advanced progress bar ' . $deal->id);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        return ['success' => $success, 'errors' => $errors];
    }

    private function createDeal(Deal $deal): ?string
    {
        $accountId = $this->migratedAccounts[$deal->customer_id] ?? null;
        
        if (!$accountId) {
            return null;
        }

        return $this->zohoService->createDeal($dealData);
    }
}