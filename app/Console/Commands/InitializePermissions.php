<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PermissionInitializerService;

class InitializePermissions extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'permissions:initialize {--force : 强制重新初始化所有权限}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '初始化权限系统，创建默认角色和权限';

    /**
     * 执行命令
     */
    public function handle(PermissionInitializerService $permissionInitializer)
    {
        $this->info('开始初始化权限系统...');
        
        try {
            // 检查是否强制重新初始化
            if ($this->option('force')) {
                $this->warn('强制重新初始化权限系统...');
            }
            
            // 初始化权限系统
            $permissionInitializer->initialize();
            
            $this->info('权限系统初始化完成！');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('权限系统初始化失败: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
