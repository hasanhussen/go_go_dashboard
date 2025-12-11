<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Store;
use App\Models\Meal;
use App\Models\Additional;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
    RolesAndAdminSeeder::class,
    MySeeder::class,
    OrdersSeeder::class,
    CartsSeeder::class,
    AdditionalCartSeeder::class,
    CouponSeeder::class,
]);

    }
}
