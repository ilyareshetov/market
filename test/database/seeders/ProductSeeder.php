<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 1; $i < 5; ++$i) {
            DB::table('products')->insert([
                'name' => 'Продукт ' . $i,
                'price' => rand(1, $i+10) * 100,
            ]);
        }
    }
}
