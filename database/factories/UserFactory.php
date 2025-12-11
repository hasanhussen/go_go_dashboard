<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // جيب كل الصور من المجلد
        $files = File::files(public_path('storage/profile_images'));

        // اختار صورة عشوائية
        $randomFile = count($files) > 0 ? $files[array_rand($files)] : null;

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => $this->faker->unique()->numerify('09########'), // رقم هاتف وهمي
            'gender' => $this->faker->numberBetween(0, 1), 
            //'imgname' => null,
            'image' => $randomFile 
                ? 'profile_images/' . $randomFile->getFilename()
                : null,
            'status' => $this->faker->randomElement(['0','1','2']),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
