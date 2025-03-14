<template>
  <AdminLayout title="临时权限管理">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        临时权限管理
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- 创建临时权限卡片 -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-6 p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            创建临时权限
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 用户选择 -->
            <div>
              <InputLabel for="user_id" value="用户" />
              <Select
                id="user_id"
                v-model="form.user_id"
                class="mt-1 block w-full"
                :options="userOptions"
                placeholder="选择用户"
              />
              <InputError :message="form.errors.user_id" class="mt-2" />
            </div>
            
            <!-- 权限选择 -->
            <div>
              <InputLabel for="permission" value="权限" />
              <Select
                id="permission"
                v-model="form.permission"
                class="mt-1 block w-full"
                :options="permissionOptions"
                placeholder="选择权限"
              />
              <InputError :message="form.errors.permission" class="mt-2" />
            </div>
            
            <!-- 上下文条件 -->
            <div>
              <InputLabel value="上下文条件（可选）" />
              <div class="mt-1 space-y-2">
                <div 
                  v-for="(condition, index) in conditions" 
                  :key="index"
                  class="flex items-center space-x-2"
                >
                  <TextInput
                    v-model="condition.key"
                    class="flex-1"
                    placeholder="键名"
                  />
                  <TextInput
                    v-model="condition.value"
                    class="flex-1"
                    placeholder="值"
                  />
                  <Button type="button" @click="removeCondition(index)" danger>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </Button>
                </div>
                <Button type="button" @click="addCondition" secondary>
                  添加条件
                </Button>
              </div>
              <InputError :message="form.errors.conditions" class="mt-2" />
            </div>
            
            <!-- 权限过期时间 -->
            <div>
              <InputLabel for="expires_at" value="过期时间" />
              <TextInput
                id="expires_at"
                type="datetime-local"
                v-model="form.expires_at"
                class="mt-1 block w-full"
              />
              <InputError :message="form.errors.expires_at" class="mt-2" />
            </div>
            
            <!-- 说明 -->
            <div class="md:col-span-2">
              <InputLabel for="reason" value="授权原因（可选）" />
              <TextArea
                id="reason"
                v-model="form.reason"
                class="mt-1 block w-full"
                placeholder="请说明授权此临时权限的原因"
              />
              <InputError :message="form.errors.reason" class="mt-2" />
            </div>
          </div>
          
          <div class="flex justify-end mt-6">
            <Button @click="createTemporaryPermission" :disabled="form.processing">
              <template v-if="form.processing">创建中...</template>
              <template v-else>创建临时权限</template>
            </Button>
          </div>
        </div>
        
        <!-- 临时权限列表 -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    用户
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    权限
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    条件
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    过期时间
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    授权人
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    操作
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="permission in temporaryPermissions.data" :key="permission.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ permission.user.name }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">
                      {{ getPermissionDisplayName(permission.permission) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      {{ permission.permission }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div v-if="permission.conditions" class="text-sm text-gray-500 dark:text-gray-400">
                      <div v-for="(value, key) in permission.conditions" :key="key" class="whitespace-nowrap">
                        <span class="font-medium">{{ key }}:</span> {{ value }}
                      </div>
                    </div>
                    <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                      无条件限制
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="text-sm px-2 py-1 rounded-full" 
                      :class="getExpirationClass(permission.expires_at)"
                    >
                      {{ formatDateTime(permission.expires_at) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ permission.grantor?.name || '系统' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Button
                      @click="confirmDelete(permission)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                      danger
                    >
                      删除
                    </Button>
                  </td>
                </tr>
                <tr v-if="temporaryPermissions.data.length === 0">
                  <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                    没有找到任何临时权限
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- 分页 -->
          <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-800">
            <Pagination :links="temporaryPermissions.links" />
          </div>
        </div>
      </div>
    </div>
    
    <!-- 删除确认对话框 -->
    <DialogModal :show="!!permissionToDelete" @close="cancelDelete">
      <div class="px-6 py-4">
        <div class="text-lg font-medium text-gray-900 dark:text-gray-100">
          确认删除临时权限
        </div>
        
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
          确定要删除授予 <span class="font-semibold">{{ permissionToDelete?.user?.name }}</span> 的
          <span class="font-semibold">{{ getPermissionDisplayName(permissionToDelete?.permission) }}</span> 临时权限吗？
          此操作无法撤销。
        </div>
      </div>
      
      <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 text-right">
        <Button @click="cancelDelete" class="mr-2" secondary>
          取消
        </Button>
        <Button 
          @click="deleteTemporaryPermission"
          :class="{ 'opacity-50 cursor-not-allowed': deleting }"
          :disabled="deleting"
          danger
        >
          <template v-if="deleting">删除中...</template>
          <template v-else>确认删除</template>
        </Button>
      </div>
    </DialogModal>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/Button.vue';
import Checkbox from '@/Components/Checkbox.vue';
import DialogModal from '@/Components/DialogModal.vue';
import Pagination from '@/Components/Pagination.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Select from '@/Components/Select.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';

// 获取页面属性
const props = defineProps({
  temporaryPermissions: Object,
  users: Array,
  permissions: Array,
});

// 页面状态
const conditions = ref([]);
const permissionToDelete = ref(null);
const deleting = ref(false);

// 表单
const form = useForm({
  user_id: '',
  permission: '',
  conditions: {},
  expires_at: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().slice(0, 16), // 默认一天后过期
  reason: '',
});

// 计算属性 - 用户选项
const userOptions = computed(() => {
  return props.users.map(user => ({
    value: user.id,
    label: `${user.name} (${user.email})`
  }));
});

// 计算属性 - 权限选项
const permissionOptions = computed(() => {
  return props.permissions.map(permission => ({
    value: permission.name,
    label: getPermissionDisplayName(permission.name)
  }));
});

// 获取权限显示名称
const getPermissionDisplayName = (permissionName) => {
  const displayNames = {
    'create_post': '创建文章',
    'edit_post': '编辑自己的文章',
    'delete_post': '删除自己的文章',
    'publish_post': '发布文章',
    'edit_others_posts': '编辑他人的文章',
    'delete_others_posts': '删除他人的文章',
    'edit_published_post': '编辑已发布的文章',
    'edit_categories': '管理分类',
    'edit_tags': '管理标签',
    'list_users': '查看用户列表',
    'create_user': '创建用户',
    'edit_user': '编辑用户',
    'delete_user': '删除用户',
    'assign_roles': '分配角色和权限',
    'create_role': '创建角色',
    'edit_role': '编辑角色',
    'delete_role': '删除角色',
    'access_admin': '访问管理后台',
    'manage_settings': '管理站点设置',
    // 兼容旧版权限名称
    'edit_own_post': '编辑自己的文章',
    'edit_others_post': '编辑他人的文章',
    'delete_own_post': '删除自己的文章',
    'delete_others_post': '删除他人的文章',
    'manage_categories': '管理分类',
    'manage_tags': '管理标签',
  };
  
  return displayNames[permissionName] || permissionName;
};

// 格式化日期时间
const formatDateTime = (dateTimeString) => {
  const options = { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  return new Date(dateTimeString).toLocaleString('zh-CN', options);
};

// 获取过期时间样式
const getExpirationClass = (expirationDate) => {
  const now = new Date();
  const expiration = new Date(expirationDate);
  
  if (expiration < now) {
    return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'; // 已过期
  }
  
  const difference = expiration - now;
  const hoursRemaining = difference / (1000 * 60 * 60);
  
  if (hoursRemaining < 24) {
    return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100'; // 24小时内过期
  } else if (hoursRemaining < 72) {
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100'; // 3天内过期
  } else {
    return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100'; // 更长时间
  }
};

// 添加条件
const addCondition = () => {
  conditions.value.push({ key: '', value: '' });
};

// 移除条件
const removeCondition = (index) => {
  conditions.value.splice(index, 1);
};

// 监听条件变化，更新表单
watch(conditions, (newConditions) => {
  const formattedConditions = {};
  
  newConditions.forEach(condition => {
    if (condition.key && condition.value) {
      formattedConditions[condition.key] = condition.value;
    }
  });
  
  form.conditions = formattedConditions;
}, { deep: true });

// 创建临时权限
const createTemporaryPermission = () => {
  form.post(route('admin.permissions.temporary.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      conditions.value = [];
    }
  });
};

// 确认删除
const confirmDelete = (permission) => {
  permissionToDelete.value = permission;
};

// 取消删除
const cancelDelete = () => {
  permissionToDelete.value = null;
};

// 删除临时权限
const deleteTemporaryPermission = () => {
  if (!permissionToDelete.value) return;
  
  deleting.value = true;
  
  router.delete(route('admin.permissions.temporary.destroy', permissionToDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      permissionToDelete.value = null;
      deleting.value = false;
    },
    onError: () => {
      deleting.value = false;
    }
  });
};
</script> 