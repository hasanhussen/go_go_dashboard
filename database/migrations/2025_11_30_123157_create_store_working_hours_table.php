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
        Schema::create('store_working_hours', function (Blueprint $table) {
        $table->id();
        $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();

        $table->enum('day', [
            'السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'
        ]);

        $table->time('open_at')->nullable();
        $table->time('close_at')->nullable();

        //$table->boolean('is_open')->default(true); // مفتوح أو لا
        // $table->boolean('is_24')->default(false); // مفتوح دائمًا

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_working_hours');
    }
};
