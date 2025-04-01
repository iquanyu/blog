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

        // 创建普通用户
        $this->command->info('创建普通用户...');
        $users = User::factory(30)->create();


        $this->command->info('用户创建完成！');
    }
} 