<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Store;
use App\Models\Meal;
use App\Models\Additional;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class MyStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
 
        $categories = Category::all();

        $user = User::where('email', 'like', 'alexageorge8509@gmail.com')->first();

// إذا المستخدم ما موجود، ممكن تنشئه
if (!$user) {
    $user = User::factory()->create([
        'name' => 'Specific User',
        'email' => 'alexageorge8509@gmail.com',
    ]);
    $user->assignRole('owner'); // اعطيه الدور المناسب
}


Store::factory(5)
    ->for($user)
    ->create()
    ->each(function ($store) {
        $meals = Meal::factory(rand(2, 5))
            ->for($store) // هذا يحط store_id تلقائيًا
            ->create();

        foreach ($meals as $meal) {
            $additionals = Additional::factory(rand(3, 5))
                ->for($store) // إضافات تابعة لنفس المتجر
                ->create();

            $meal->additionals()->attach(
                $additionals->random(rand(1, $additionals->count()))->pluck('id')->toArray()
            );
        }
    });


       
    }
}
