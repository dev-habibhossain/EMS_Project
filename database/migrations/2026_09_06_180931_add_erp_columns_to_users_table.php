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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('role_id')->nullable()->after('password')->constrained()->restrictOnDelete();
            $table->string('locale', 8)->default('en')->after('role_id');
            $table->foreignId('default_warehouse_id')->nullable()->after('locale')->constrained('warehouses')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('default_warehouse_id');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropConstrainedForeignId('default_warehouse_id');
            $table->dropSoftDeletes();
            $table->dropColumn(['username', 'phone', 'locale', 'is_active']);
        });
    }
};
