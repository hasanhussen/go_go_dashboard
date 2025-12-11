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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('status',['0','1','2','3','4','5'])->default(0); //الحالة 5 العامل في الموقع 
            //و الحالة 4 هي تم التسليم

            $table->string('notes')->nullable();

            $table->string('address');
            $table->string('x')->nullable();
            $table->string('y')->nullable();

            $table->decimal('price',10,2);
            $table->decimal('delivery_price',10,2);
            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->foreign('delivery_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
            $table->string('coupon_name')->nullable();
            $table->integer('discount')->nullable();
            $table->decimal('total_price',10,2);

            $table->enum('payment_method', ['cash', 'card'])->default('cash'); // طريقة الدفع
            $table->enum('is_paid',['0','1'])->default(0);
            $table->integer('cart_count');
            $table->text('delete_reason')->nullable();
            //$table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_editing')->default(false);
            $table->timestamp('editing_started_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
