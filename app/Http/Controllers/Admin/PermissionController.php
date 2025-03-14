<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\PermissionGroupService;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\TemporaryPermission;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    protected $permissionGroupService;

    public function __construct(PermissionGroupService $permissionGroupService)
    {
        $this->permissionGroupService = $permissionGroupService;
        $this->middleware(['permission:assign_roles'])->except(['getCurrentUserPermissions']);
    }

    /**
     * 显示角色权限管理页面
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $allPermissions = Permission::all();
        
        // 获取权限组信息
        $permissionGroups = $this->permissionGroupService->getAllGroups();
        
        // 将权限按组组织，并添加可读的中文名称
        $groupedPermissions = [];
        foreach ($permissionGroups as $group => $permissions) {
            $items = Permission::whereIn('name', $permissions)->get();
            
            // 为每个权限添加中文描述
            $items->transform(function ($item) {
                $item->description = $this->permissionGroupService->getPermissionDescription($item->name);
                return $item;
            });
            
            $groupedPermissions[$group] = [
                'name' => $this->permissionGroupService->getGroupDisplayName($group),
                'permissions' => $items
            ];
        }

        return Inertia::render('Admin/Permissions/Index', [
            'roles' => $roles,
            'allPermissions' => $allPermissions,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }
    
    /**
     * 显示用户权限分配页面
     */
    public function userPermissions()
    {
        $users = User::with('roles', 'permissions')->paginate(20);
        $roles = Role::all();

        return Inertia::render('Admin/Permissions/Users', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
    
    /**
     * 显示临时权限管理页面
     */
    public function temporaryPermissions()
    {
        $temporaryPermissions = TemporaryPermission::with(['user', 'grantor'])
            ->orderBy('expires_at', 'desc')
            ->paginate(20);
        
        $users = User::all();
        $permissions = Permission::all();
        
        return Inertia::render('Admin/Permissions/Temporary', [
            'temporaryPermissions' => $temporaryPermissions,
            'users' => $users,
            'permissions' => $permissions,
        ]);
    }

    /**
     * 更新角色权限
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        try {
            $validated = $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id',
            ]);
            
            // 超级管理员角色不能修改
            if ($role->name === 'super-admin') {
                return back()->with('error', '超级管理员角色权限不可修改');
            }
    
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
    
            return back()->with('success', "角色 '{$role->name}' 的权限已更新");
        } catch (\Exception $e) {
            Log::error('更新角色权限失败', [
                'role' => $role->name,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', '更新角色权限失败：' . $e->getMessage());
        }
    }

    /**
     * 为角色分配权限组
     */
    public function assignPermissionGroup(Request $request, Role $role)
    {
        try {
            $validated = $request->validate([
                'groups' => 'required|array',
                'groups.*' => 'string',
            ]);
            
            // 超级管理员角色不能修改
            if ($role->name === 'super-admin') {
                return back()->with('error', '超级管理员角色权限不可修改');
            }
    
            $permissions = $this->permissionGroupService->getPermissionsByGroups($validated['groups']);
            $role->syncPermissions($permissions);
    
            return back()->with('success', "角色 '{$role->name}' 的权限组已更新");
        } catch (\Exception $e) {
            Log::error('分配权限组失败', [
                'role' => $role->name,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', '分配权限组失败：' . $e->getMessage());
        }
    }
    
    /**
     * 为用户分配角色
     */
    public function assignUserRoles(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id',
            ]);
    
            $roles = Role::whereIn('id', $validated['roles'])->get();
            $user->syncRoles($roles);
    
            return back()->with('success', "用户 '{$user->name}' 的角色已更新");
        } catch (\Exception $e) {
            Log::error('分配用户角色失败', [
                'user' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', '分配用户角色失败：' . $e->getMessage());
        }
    }
    
    /**
     * 创建临时权限
     */
    public function storeTemporaryPermission(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'permission' => 'required|exists:permissions,name',
                'conditions' => 'nullable|array',
                'expires_at' => 'required|date|after:now',
                'reason' => 'nullable|string|max:255',
            ]);
            
            $validated['granted_by'] = auth()->id();
            
            $user = User::find($validated['user_id']);
            
            $options = [
                'expires_at' => $validated['expires_at'],
                'conditions' => $validated['conditions'] ?? null,
                'granted_by' => $validated['granted_by'],
                'reason' => $validated['reason'] ?? null,
            ];
            
            $user->grantTemporaryPermission($validated['permission'], $options);
    
            return back()->with('success', '临时权限已授予');
        } catch (\Exception $e) {
            Log::error('创建临时权限失败', [
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', '创建临时权限失败：' . $e->getMessage());
        }
    }
    
    /**
     * 删除临时权限
     */
    public function destroyTemporaryPermission(TemporaryPermission $temporaryPermission)
    {
        try {
            $temporaryPermission->delete();
            
            return back()->with('success', '临时权限已删除');
        } catch (\Exception $e) {
            Log::error('删除临时权限失败', [
                'id' => $temporaryPermission->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', '删除临时权限失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取当前用户的权限和角色信息
     */
    public function getCurrentUserPermissions()
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'permissions' => [],
                'roles' => []
            ]);
        }
        
        return response()->json([
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles' => $user->getRoleNames()
        ]);
    }
}
