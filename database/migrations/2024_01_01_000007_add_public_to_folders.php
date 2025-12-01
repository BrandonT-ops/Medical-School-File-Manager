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
        Schema::table('folders', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('permissions');
            $table->text('public_permissions')->nullable()->after('is_public'); // read, write, delete
        });

        Schema::table('file_items', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'public_permissions']);
        });

        Schema::table('file_items', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
