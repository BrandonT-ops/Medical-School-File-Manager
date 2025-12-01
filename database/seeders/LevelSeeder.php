<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Level;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['name' => 'General', 'slug' => 'general', 'description' => 'General files not specific to any level', 'order' => 0],
            ['name' => 'Level 1', 'slug' => 'level-1', 'description' => 'First year medical students', 'order' => 1],
            ['name' => 'Level 2', 'slug' => 'level-2', 'description' => 'Second year medical students', 'order' => 2],
            ['name' => 'Level 3', 'slug' => 'level-3', 'description' => 'Third year medical students', 'order' => 3],
            ['name' => 'Level 4', 'slug' => 'level-4', 'description' => 'Fourth year medical students', 'order' => 4],
            ['name' => 'Level 5', 'slug' => 'level-5', 'description' => 'Fifth year medical students', 'order' => 5],
            ['name' => 'Level 6', 'slug' => 'level-6', 'description' => 'Sixth year medical students', 'order' => 6],
            ['name' => 'Level 7', 'slug' => 'level-7', 'description' => 'Seventh year medical students', 'order' => 7],
        ];

        foreach ($levels as $level) {
            Level::create($level);
        }

        $this->command->info('Levels seeded successfully!');
    }
}
