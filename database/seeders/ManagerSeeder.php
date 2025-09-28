<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManagerSeeder extends Seeder
{
    private $count = 5;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i = 1; $i <= $this->count; $i++) {
            DB::table('managers')->insert([
                'id' => $i,
                'email' => 'manager'.$i.'@gmail.com',
                'deals_count' => 0,
            ]);
        }
        //
    }
}
