<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdditionalCartSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('additional_cart')->insert([
            ['additional_id' => 1, 'cart_id' => 1, 'quantity' => 1, 'old_additional_price' => 1.00, 'created_at' => now(), 'updated_at' => now()],
            ['additional_id' => 2, 'cart_id' => 1, 'quantity' => 2, 'old_additional_price' => 2.00, 'created_at' => now(), 'updated_at' => now()],
            ['additional_id' => 3, 'cart_id' => 2, 'quantity' => 1, 'old_additional_price' => 3.00, 'created_at' => now(), 'updated_at' => now()],

            ['additional_id' => 1, 'cart_id' => 3, 'quantity' => 1, 'old_additional_price' => 1.00, 'created_at' => now(), 'updated_at' => now()],
            ['additional_id' => 2, 'cart_id' => 4, 'quantity' => 2, 'old_additional_price' => 2.00, 'created_at' => now(), 'updated_at' => now()],

            ['additional_id' => 3, 'cart_id' => 6, 'quantity' => 1, 'old_additional_price' => 3.00, 'created_at' => now(), 'updated_at' => now()],
            ['additional_id' => 4, 'cart_id' => 7, 'quantity' => 1, 'old_additional_price' => 4.00, 'created_at' => now(), 'updated_at' => now()],

            ['additional_id' => 5, 'cart_id' => 8, 'quantity' => 2, 'old_additional_price' => 5.00, 'created_at' => now(), 'updated_at' => now()],
            ['additional_id' => 6, 'cart_id' => 9, 'quantity' => 1, 'old_additional_price' => 6.00, 'created_at' => now(), 'updated_at' => now()],

            ['additional_id' => 7, 'cart_id' => 12, 'quantity' => 1, 'old_additional_price' => 7.00, 'created_at' => now(), 'updated_at' => now()],
            ['additional_id' => 8, 'cart_id' => 13, 'quantity' => 2, 'old_additional_price' => 8.00, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
