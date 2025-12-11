<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use App\Helpers\ArabicData;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $category = $this->faker->randomElement(array_keys(ArabicData::$storeNames));
        // جيب كل الصور من المجلد
        $files = File::files(public_path('storage/stores'));

        // اختار صورة عشوائية
        $randomFile = count($files) > 0 ? $files[array_rand($files)] : null;

        // جيب كل الصور من المجلد
        $coverFiles = File::files(public_path('storage/coverstores'));

        // اختار صورة عشوائية
        $randomcoverFiles = count($coverFiles) > 0 ? $coverFiles[array_rand($coverFiles)] : null;

        return [
            'user_id' => \App\Models\User::factory(), // ينشئ مستخدم وهمي ويربطه
            'category_id' => \App\Models\Category::where('type', $category)->first()->id,
            'name' => $storeName = $this->faker->randomElement(ArabicData::$storeNames[$category]),
            'city_id' => $this->faker->numberBetween(1, 20),
            'delivery' => $this->faker->randomElement(['0','1']),
            'phone' => $this->faker->numerify('09########'),
            'image' => ArabicData::$storeProfileImages[$category][$storeName] ?? null,
            'cover' => ArabicData::$storeCoverImages[$category][$storeName] ?? null,
            'special' => $this->faker->sentence(3),
            'address' => ArabicData::$storeAddresses[$category][$storeName] ?? $this->faker->address(),
            'followers' => $this->faker->numberBetween(0, 5000),
            'x' => $this->faker->latitude(),
            'y' => $this->faker->longitude(),
            'status' => $this->faker->randomElement(['0','1','2']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
