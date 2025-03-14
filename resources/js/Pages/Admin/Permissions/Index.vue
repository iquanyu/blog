<template>
  <AdminLayout title="角色权限管理">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        角色权限管理
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <!-- 角色标签 -->
          <div class="mb-6">
            <div class="flex flex-wrap border-b dark:border-gray-700">
              <div 
                v-for="role in roles" 
                :key="role.id" 
                @click="selectRole(role)"
                class="px-4 py-2 cursor-pointer transition-colors"
                :class="[
                  selectedRole?.id === role.id 
                    ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                ]"
              >
                {{ getRoleDisplayName(role.name) }}
              </div>
            </div>
          </div>

          <!-- 权限组和权限 -->
          <div v-if="selectedRole">
            <div v-if="selectedRole.name === 'super-admin'" class="text-center py-6">
              <p class="text-lg text-gray-600 dark:text-gray-300">
                超级管理员自动拥有所有权限，无需手动分配
              </p>
            </div>
            <div v-else>
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                为 <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ getRoleDisplayName(selectedRole.name) }}</span> 分配权限
              </h3>
              
              <!-- 使用权限组分配 -->
              <div class="mb-8">
                <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">按权限组分配</h4>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                  <div 
                    v-for="(group, key) in groupedPermissions" 
                    :key="key"
                    class="border dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                  >
                    <div class="flex items-center mb-3">
                      <Checkbox 
                        :checked="isGroupSelected(key)"
                        @update:checked="togglePermissionGroup(key)"
                        :indeterminate="isGroupIndeterminate(key)"
                      />
                      <span class="ml-2 font-medium text-gray-800 dark:text-gray-200">{{ group.name }}</span>
                    </div>
                    <div class="ml-6 space-y-2">
                      <div 
                        v-for="permission in group.permissions" 
                        :key="permission.id"
                        class="flex items-center"
                      >
                        <Checkbox 
                          :checked="isPermissionSelected(permission.id)"
                          @update:checked="togglePermission(permission.id)"
                        />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ permission.description }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- 保存按钮 -->
              <div class="flex justify-end mt-6">
                <Button 
                  v-if="hasPermissionChanges" 
                  @click="saveRolePermissions"
                  :class="{ 'opacity-50 cursor-not-allowed': saving }"
                  :disabled="saving"
                >
                  <template v-if="saving">保存中...</template>
                  <template v-else>保存权限</template>
                </Button>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">请选择一个角色来管理其权限</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, usePage, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/Button.vue';
import Checkbox from '@/Components/Checkbox.vue';

// 获取页面属性
const props = defineProps({
  roles: Array,
  allPermissions: Array,
  groupedPermissions: Object
});

// 页面状态
const selectedRole = ref(null);
const selectedPermissions = ref([]);
const originalPermissions = ref([]);
const saving = ref(false);

// 创建表单
const form = useForm({
  permissions: []
});

// 计算属性
const hasPermissionChanges = computed(() => {
  if (!selectedRole.value) return false;
  
  // 检查权限是否有变化
  const currentIds = selectedPermissions.value.map(p => p);
  const originalIds = originalPermissions.value.map(p => p);
  
  // 长度不同肯定有变化
  if (currentIds.length !== originalIds.length) return true;
  
  // 检查是否有新增或删除的权限
  return currentIds.some(id => !originalIds.includes(id)) || 
         originalIds.some(id => !currentIds.includes(id));
});

// 选择角色
const selectRole = (role) => {
  selectedRole.value = role;
  
  // 获取该角色已有的权限
  const rolePermissionIds = role.permissions.map(p => p.id);
  selectedPermissions.value = [...rolePermissionIds];
  originalPermissions.value = [...rolePermissionIds];
};

// 角色显示名称
const getRoleDisplayName = (roleName) => {
  const displayNames = {
    'super-admin': '超级管理员',
    'admin': '管理员',
    'editor': '编辑',
    'author': '作者',
    'contributor': '贡献者',
    'subscriber': '订阅者',
    'member': '会员'
  };
  
  return displayNames[roleName] || roleName;
};

// 权限选择状态
const isPermissionSelected = (permissionId) => {
  return selectedPermissions.value.includes(permissionId);
};

// 权限组选择状态
const isGroupSelected = (groupKey) => {
  if (!props.groupedPermissions[groupKey]) return false;
  
  const groupPermissionIds = props.groupedPermissions[groupKey].permissions.map(p => p.id);
  return groupPermissionIds.every(id => selectedPermissions.value.includes(id));
};

// 权限组部分选择状态
const isGroupIndeterminate = (groupKey) => {
  if (!props.groupedPermissions[groupKey]) return false;
  
  const groupPermissionIds = props.groupedPermissions[groupKey].permissions.map(p => p.id);
  const hasSelected = groupPermissionIds.some(id => selectedPermissions.value.includes(id));
  const hasUnselected = groupPermissionIds.some(id => !selectedPermissions.value.includes(id));
  
  return hasSelected && hasUnselected;
};

// 切换单个权限
const togglePermission = (permissionId) => {
  if (selectedPermissions.value.includes(permissionId)) {
    selectedPermissions.value = selectedPermissions.value.filter(id => id !== permissionId);
  } else {
    selectedPermissions.value.push(permissionId);
  }
};

// 切换权限组
const togglePermissionGroup = (groupKey) => {
  const groupPermissionIds = props.groupedPermissions[groupKey].permissions.map(p => p.id);
  
  if (isGroupSelected(groupKey)) {
    // 如果已全选，则取消全部
    selectedPermissions.value = selectedPermissions.value.filter(id => !groupPermissionIds.includes(id));
  } else {
    // 否则选择全部
    const newPermissions = [...selectedPermissions.value];
    groupPermissionIds.forEach(id => {
      if (!newPermissions.includes(id)) {
        newPermissions.push(id);
      }
    });
    selectedPermissions.value = newPermissions;
  }
};

// 保存角色权限
const saveRolePermissions = () => {
  if (!selectedRole.value || selectedRole.value.name === 'super-admin') return;
  
  saving.value = true;
  
  form.permissions = selectedPermissions.value;
  
  form.put(route('admin.roles.permissions.update', selectedRole.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      originalPermissions.value = [...selectedPermissions.value];
      saving.value = false;
    },
    onError: () => {
      saving.value = false;
    }
  });
};

// 页面挂载时，默认选择第一个角色
onMounted(() => {
  if (props.roles && props.roles.length > 0) {
    selectRole(props.roles[0]);
  }
});
</script> 