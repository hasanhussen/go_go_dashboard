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
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name');
            $table->string('description');
            $table->string('note')->nullable();
            $table->string('image')->nullable();
            //$table->string('imgname')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('points')->default(0);
            $table->integer('quantity')->nullable();
            $table->decimal('price',10,2)->nullable();
            $table->enum('status', ['0', '1', '2'])->default('0')->comment('0: pending, 1: accepted, 2: banned');
            $table->text('delete_reason')->nullable();
            $table->text('ban_reason')->nullable();
            $table->dateTime('ban_until')->nullable();
            $table->integer('ban_count')->default(0);
            $table->text('appeal')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
