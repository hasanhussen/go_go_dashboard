<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Meal;
use Illuminate\Support\Facades\File;
use App\Helpers\ArabicData;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Meal>
 */
class MealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
     public function definition(): array
    {

    //     $store = Store::inRandomOrder()->first();
    // $category = $store->category->type;

    // $names = ArabicData::$meals[$category] ?? ['منتج عام'];

    //     $files = File::files(public_path('storage/products'));

    //     // اختار صورة عشوائية
    //     $randomFile = count($files) > 0 ? $files[array_rand($files)] : null;

    $category = $this->faker->randomElement(array_keys(ArabicData::$meals));
$category = $this->faker->randomElement(array_keys(ArabicData::$meals));

$store = Store::whereHas('category', function($q) use ($category) {
    $q->where('type', $category);
})->inRandomOrder()->first();

if (!$store) {
    // إذا ما في متجر بنفس الفئة، نوقف إنشاء المنتج أو نختار متجر عشوائي
    $store = Store::inRandomOrder()->first();
    $category = $store->category->type; // نعيد تحديث الفئة لتتطابق مع المتجر
}

$mealName = $this->faker->randomElement(ArabicData::$meals[$category]);



    return [
    'store_id' => $store->id,
    'name' => $mealName,
    'description' => ArabicData::$mealDescriptions[$category][$mealName] ?? $this->faker->sentence(8),
    'image' => ArabicData::$mealImages[$category][$mealName] ?? null,
    'is_active' => $this->faker->boolean(90),
    'points' => $this->faker->numberBetween(0, 100),
    'price' => 0.00,
    'status' => $this->faker->randomElement(['0','1','2']),
    'quantity' => $this->faker->numberBetween(1, 100),
    'created_at' => now(),
    'updated_at' => now(),
];
    }

    public function configure()
    {
        return $this->afterCreating(function (Meal $meal) {
            // السعر = id * 10
            $meal->update([
                'price' => number_format($meal->id * 10, 2, '.', '')
            ]);
        });
    }
}
