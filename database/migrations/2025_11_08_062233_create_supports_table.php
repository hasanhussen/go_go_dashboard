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
        Schema::create('supports', function (Blueprint $table) {
            $table->id(); // primary key
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('role')->nullable(); // user | owner | delivery
            $table->string('subject');
            $table->text('message');
            $table->string('image')->nullable(); // أو 'image' حسب الـ API
            $table->string('status')->default('new');
            $table->string('type')->default('general');
            $table->text('reply')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supports');
    }
};
