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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('phone');
            $table->string('alt_phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 16, 2)->nullable();
            $table->boolean('is_walk_in')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('cached_balance', 16, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('phone');
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('phone');
            $table->string('alt_phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 16, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('cached_balance', 16, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
    }
};
