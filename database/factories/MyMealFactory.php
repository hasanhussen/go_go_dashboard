<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Meal;
use Illuminate\Support\Facades\File;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class MyMealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition(): array
    {
        $files = File::files(public_path('storage/products'));

        // اختار صورة عشوائية
        $randomFile = count($files) > 0 ? $files[array_rand($files)] : null;

        return [
            'store_id' => Store::factory(), // ينشئ متجر وهمي ويربطه
            'name' => $this->faker->words(2, true), // اسم منتج من كلمتين
            'description' => $this->faker->sentence(8),
            //'note' => $this->faker->optional()->sentence(4),
            'image' => $randomFile 
                ? 'products/' . $randomFile->getFilename()
                : null,
            'is_active' => $this->faker->boolean(90), // غالباً تكون مفعلة
            'points' => $this->faker->numberBetween(0, 100),
            'price' => 0.00, // مؤقت - راح نعدله بعد الإنشاء
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
