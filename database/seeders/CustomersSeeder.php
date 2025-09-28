<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomersSeeder extends Seeder
{
    private $count = 50;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i = 1; $i <= $this->count; $i++) {
            DB::table('customers')->insert([
                'first_name' => 'First Name '.$i,
                'last_name' => 'Last Name '.$i,
                'email' => 'email'.$i.'@gmail.com',
            ]);
        }
    }
}
