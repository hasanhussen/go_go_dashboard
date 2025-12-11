<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $coupons = [];

    for ($i = 1; $i <= 25; $i++) { // مثال: إنشاء 25 كوبون
        $coupons[] = [
            'name' => 'COUPON'.$i,
            'discount' => rand(5,50),
            'count' => rand(10,200),
            'notes' => 'كوبون رقم '.$i,
            'details' => ['total_price','products_price','delivery_price'][array_rand(['total_price','products_price','delivery_price'])],
            'status' => ['active','inactive','not_started','expired','exhausted'][array_rand(['active','inactive','not_started','expired','exhausted'])],
            'valid_from' => Carbon::now()->subDays(rand(0,10)),
            'valid_to' => Carbon::now()->addDays(rand(5,60)),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    DB::table('coupons')->insert($coupons);
}

}
      
