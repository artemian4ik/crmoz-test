<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DealsSeeder extends Seeder
{
    private $count = 50;
    private $sources = ['Source 1', 'Source 2', 'Source 3', 'Source 4', 'Source 5'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       
        for($i = 1; $i <= $this->count; $i++) {
            DB::table('deals')->insert([
            'name' => Str::random(10),
                'customer_id' => rand(1, 50),
                'source' => $this->getRandomSource(),
            ]);
        }
    }

    private function getRandomSource()
    {
        return $this->sources[array_rand($this->sources)];
    }
}
