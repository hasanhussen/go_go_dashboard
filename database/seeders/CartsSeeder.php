<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('carts')->insert([
            ['meal_id' => 1, 'user_id' => 1, 'quantity' => 2, 'old_meal_price' => 10.00, 'old_price' => 20.00, 'order_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['meal_id' => 2, 'user_id' => 1, 'quantity' => 1, 'old_meal_price' => 20.00, 'old_price' => 20.00, 'order_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['meal_id' => 3, 'user_id' => 2, 'quantity' => 3, 'old_meal_price' => 30.00, 'old_price' => 90.00, 'order_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['meal_id' => 4, 'user_id' => 2, 'quantity' => 2, 'old_meal_price' => 40.00, 'old_price' => 80.00, 'order_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['meal_id' => 5, 'user_id' => 2, 'quantity' => 1, 'old_meal_price' => 50.00, 'old_price' => 50.00, 'order_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            
            ['meal_id' => 6, 'user_id' => 3, 'quantity' => 2, 'old_meal_price' => 60.00, 'old_price' => 120.00, 'order_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['meal_id' => 7, 'user_id' => 3, 'quantity' => 1, 'old_meal_price' => 70.00, 'old_price' => 70.00, 'order_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            
            ['meal_id' => 8, 'user_id' => 4, 'quantity' => 2, 'old_meal_price' => 80.00, 'old_price' => 160.00, 'order_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['meal_id' => 9, 'user_id' => 4, 'quantity' => 1, 'old_meal_price' => 90.00, 'old_price' => 90.00, 'order_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['meal_id' => 10, 'user_id' => 4, 'quantity' => 1, 'old_meal_price' => 100.00, 'old_price' => 100.00, 'order_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            
            ['meal_id' => 11, 'user_id' => 5, 'quantity' => 1, 'old_meal_price' => 110.00, 'old_price' => 110.00, 'order_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            
            ['meal_id' => 12, 'user_id' => 6, 'quantity' => 2, 'old_meal_price' => 120.00, 'old_price' => 240.00, 'order_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['meal_id' => 13, 'user_id' => 6, 'quantity' => 1, 'old_meal_price' => 130.00, 'old_price' => 130.00, 'order_id' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
