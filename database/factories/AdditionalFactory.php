<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\ArabicData;
use App\Models\Store;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Additional>
 */
class AdditionalFactory extends Factory
{
    protected $model = \App\Models\Additional::class;

    public function definition(): array
    {

        $store = Store::inRandomOrder()->first();
    $category = $store->category->type;

    $names = ArabicData::$additionals[$category] ?? ['إضافة عامة'];
        return [
            'store_id' => $store->id,
            'name' => $this->faker->randomElement($names),
            // مؤقت: نحط سعر افتراضي — راح نحدث السعر بعد الإنشاء ليصير مساويًا للـ id
            'price' => 0.00,
            'quantity' => $this->faker->numberBetween(1, 100),
            'store_id' => \App\Models\Store::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($additional) {
            // ضبّط السعر ليصير قيمة id كـ decimal (مثلاً id = 5 -> 5.00)
            $additional->update([
                'price' => number_format($additional->id, 2, '.', '')
            ]);
        });
    }
}
