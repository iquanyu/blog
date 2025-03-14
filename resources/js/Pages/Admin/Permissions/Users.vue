<template>
  <AdminLayout title="用户角色管理">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        用户角色管理
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
          <!-- 用户表格 -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    用户
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    电子邮箱
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    当前角色
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    操作
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="user in users.data" :key="user.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img v-if="user.profile_photo_url" :src="user.profile_photo_url" class="h-10 w-10 rounded-full" alt="">
                        <div v-else class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                          <span class="text-gray-500 dark:text-gray-300 font-medium">
                            {{ user.name.charAt(0).toUpperCase() }}
                          </span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ user.name }}
                        </div>
                        <div v-if="user.created_at" class="text-xs text-gray-500 dark:text-gray-400">
                          加入于 {{ formatDate(user.created_at) }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex flex-wrap gap-1">
                      <span 
                        v-for="role in user.roles" 
                        :key="role.id"
                        class="px-2 py-1 text-xs rounded-full"
                        :class="getRoleBadgeClass(role.name)"
                      >
                        {{ getRoleDisplayName(role.name) }}
                      </span>
                      <span v-if="!user.roles.length" class="text-sm text-gray-500 dark:text-gray-400">
                        无角色
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Button
                      @click="openUserRoleDialog(user)"
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                      分配角色
                    </Button>
                    
                    <!-- 显示模拟登录按钮 -->
                    <Link
                      :href="route('admin.impersonate', user.id)"
                      class="ml-2 text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                    >
                      模拟登录
                    </Link>
                  </td>
                </tr>
                <tr v-if="users.data.length === 0">
                  <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                    没有找到任何用户
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- 分页 -->
          <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-800">
            <Pagination :links="users.links" />
          </div>
        </div>
      </div>
    </div>
    
    <!-- 角色分配对话框 -->
    <DialogModal :show="!!selectedUser" @close="closeUserRoleDialog">
      <div class="px-6 py-4">
        <div class="text-lg font-medium text-gray-900 dark:text-gray-100">
          为 {{ selectedUser?.name }} 分配角色
        </div>
        
        <div class="mt-4 space-y-4">
          <div v-for="role in roles" :key="role.id" class="flex items-center">
            <Checkbox 
              :checked="isRoleSelected(role.id)"
              @update:checked="toggleRole(role.id)"
              :id="`role-${role.id}`"
            />
            <label :for="`role-${role.id}`" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
              {{ getRoleDisplayName(role.name) }}
            </label>
          </div>
        </div>
      </div>
      
      <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 text-right">
        <Button @click="closeUserRoleDialog" class="mr-2" secondary>
          取消
        </Button>
        <Button 
          @click="saveUserRoles"
          :class="{ 'opacity-50 cursor-not-allowed': saving }"
          :disabled="saving"
        >
          <template v-if="saving">保存中...</template>
          <template v-else>保存</template>
        </Button>
      </div>
    </DialogModal>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage, useForm,Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/Button.vue';
import Checkbox from '@/Components/Checkbox.vue';
import DialogModal from '@/Components/DialogModal.vue';
import Pagination from '@/Components/Pagination.vue';

// 获取页面属性
const props = defineProps({
  users: Object,
  roles: Array
});

// 页面状态
const selectedUser = ref(null);
const selectedRoles = ref([]);
const saving = ref(false);

// 格式化日期
const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('zh-CN', options);
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

// 获取角色徽章样式
const getRoleBadgeClass = (roleName) => {
  const classes = {
    'super-admin': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
    'admin': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100',
    'editor': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
    'author': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
    'contributor': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-100',
    'subscriber': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100',
    'member': 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-100'
  };
  
  return classes[roleName] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

// 打开角色分配对话框
const openUserRoleDialog = (user) => {
  selectedUser.value = user;
  selectedRoles.value = user.roles.map(role => role.id);
};

// 关闭角色分配对话框
const closeUserRoleDialog = () => {
  selectedUser.value = null;
  selectedRoles.value = [];
};

// 切换角色选择
const toggleRole = (roleId) => {
  if (selectedRoles.value.includes(roleId)) {
    selectedRoles.value = selectedRoles.value.filter(id => id !== roleId);
  } else {
    selectedRoles.value.push(roleId);
  }
};

// 检查角色是否选中
const isRoleSelected = (roleId) => {
  return selectedRoles.value.includes(roleId);
};

// 保存用户角色
const saveUserRoles = () => {
  if (!selectedUser.value) return;
  
  saving.value = true;
  
  router.put(route('admin.users.roles.update', selectedUser.value.id), {
    roles: selectedRoles.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      closeUserRoleDialog();
      saving.value = false;
    },
    onError: () => {
      saving.value = false;
    }
  });
};
</script> 