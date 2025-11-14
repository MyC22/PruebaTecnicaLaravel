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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreign('order_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('amount');
            $table->string('status', 20)->default('pending');
            $table->string('payment_method', 20)->default('tarjeta');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
