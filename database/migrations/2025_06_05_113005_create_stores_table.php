<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->integer('city_id');
            $table->enum('delivery',['0','1']);
            $table->string('phone')->default('0000000000');
            $table->string('image')->nullable();
            $table->string('cover')->nullable();
            $table->string('special')->nullable();
            // $table->json('working_hours')->nullable();
            $table->string('address');
            $table->integer('followers')->default(0);
            $table->string('x')->nullable();
            $table->string('y')->nullable();
            $table->enum('status',['0','1','2'])->default("0");
            $table->text('delete_reason')->nullable();
            $table->text('ban_reason')->nullable();
            $table->dateTime('ban_until')->nullable();
            $table->integer('ban_count')->default(0);
            $table->text('appeal')->nullable();
            $table->string('deleted_by')->nullable();
            $table->integer('total_ratings')->default(0);
            $table->float('avg_rating', 3, 2)->default(0.00); // متوسط التقييمات
            $table->float('bayesian_score', 3, 2)->default(0.00); // Bayesi
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
