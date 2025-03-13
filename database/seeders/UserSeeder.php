<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('开始创建用户...');

        // 创建超级管理员
        $this->command->info('创建超级管理员...');
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('super-admin');

        // 创建编辑
        $this->command->info('创建编辑...');
        $editors = User::factory(3)->create();
        foreach ($editors as $editor) {
            $editor->assignRole('editor');
        }

        // 创建作者
        $this->command->info('创建作者...');
        $authors = User::factory(10)->create();
        foreach ($authors as $author) {
            $author->assignRole('author');
        }

        // 创建普通用户
        $this->command->info('创建普通用户...');
        $users = User::factory(20)->create();
        foreach ($users as $user) {
            $user->assignRole('normal-user');
        }

        // 创建未验证用户
        $this->command->info('创建未验证用户...');
        $unverifiedUsers = User::factory(5)
            ->unverified()
            ->create();
        foreach ($unverifiedUsers as $user) {
            $user->assignRole('normal-user');
        }

        $this->command->info('用户创建完成！');
    }
} 