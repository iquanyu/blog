<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 创建管理员用户
        // $admin = User::factory()->create([
        //     'name' => '管理员',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password'),
        // ]);
        // $admin->assignRole('admin');

        // 创建编辑用户
        $editor = User::factory()->create([
            'name' => '编辑',
            'email' => 'editor@example.com',
            'password' => Hash::make('password'),
        ]);
        $editor->assignRole('editor');

        // 创建多个作者用户
        User::factory()->count(5)->create()->each(function ($user) {
            $user->assignRole('creator');
        });
    }
} 