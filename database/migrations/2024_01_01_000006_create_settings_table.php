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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'school_name',
                'value' => 'Medical School',
                'type' => 'string',
                'description' => 'The name of the school/institution',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_tagline',
                'value' => 'File Management System',
                'type' => 'string',
                'description' => 'School tagline or subtitle',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_logo',
                'value' => '',
                'type' => 'string',
                'description' => 'Path to school logo image',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_footer',
                'value' => '© 2024 Medical School. All rights reserved.',
                'type' => 'string',
                'description' => 'Footer copyright text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
