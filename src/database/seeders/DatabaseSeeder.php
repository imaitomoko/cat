<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UsersTableSeeder::class);
        $this->call(SchoolsTableSeeder::class);
        $this->call(ClassesTableSeeder::class);
        $this->call(LessonsTableSeeder::class);
        $this->call(User_lessonsTableSeeder::class);
        $this->call(NewsListsTableSeeder::class);
        $this->call(AdminsTableSeeder::class);
        $this->call(TeachersTableSeeder::class);
        $this->call(MailsTableSeeder::class);
        $this->call(SendToSeeder::class);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
