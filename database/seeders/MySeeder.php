<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Store;
use App\Models\Meal;
use App\Models\Additional;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class MySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $roles = Role::where('name', '!=', 'admin')->pluck('name')->toArray(); // الأدوار غير الـ admin

    $users = User::factory(5)->create();

    // إعطاء كل مستخدم دور عشوائي
    $users->each(function ($user) use ($roles) {
        $user->assignRole($roles[array_rand($roles)]);
    });
                // 🔹 إنشاء الأقسام الثابتة مع الصور
        $categoriesData = [
            ['type' => 'bakery', 'image' => 'categories/bakery.png'],
            ['type' => 'coffee', 'image' => 'categories/coffee.png'],
            ['type' => 'fashion', 'image' => 'categories/fashion.png'],
            ['type' => 'gifts', 'image' => 'categories/gifts.png'],
            ['type' => 'homeware', 'image' => 'categories/homeware.png'],
            ['type' => 'juices', 'image' => 'categories/juices.png'],
            ['type' => 'pharmacy', 'image' => 'categories/pharmacy.png'],
            ['type' => 'restaurant', 'image' => 'categories/restaurant.png'],
        ];

        foreach ($categoriesData as $data) {
            Category::create($data);
        }

        $categories = Category::all();

        $users->each(function ($user) use ($categories) {
            Store::factory(rand(3, 5))
                ->for($user)
                ->for($categories->random(), 'category')
                ->create()
                ->each(function ($store) {
                        $days = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];

    foreach ($days as $day) {
        $store->workingHours()->create([
            'day' => $day,
            'open_at' => '09:00',
            'close_at' => '17:00',
            // 'is_open' => true,
            // 'is_24' => false,
        ]);
    }
                    // أنشئ 2-5 وجبات لكل متجر
                    Meal::factory(rand(2, 5))
                        ->for($store)
                        ->create()
                        ->each(function ($meal) use ($store) {
                            // أنشئ 3-5 إضافات لكل متجر (ليست مرتبطة بالمنتج مباشرة)
                            $additionals = Additional::factory(rand(3, 5))
                                ->for($store)
                                ->create();

                            // اربط منتج عشوائياً مع بعض الإضافات (Many-to-Many)
                            $meal->additionals()->attach(
                                $additionals->random(rand(1, $additionals->count()))->pluck('id')->toArray()
                            );
                        });
                });
        });
    }
}
