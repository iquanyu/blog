<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import DangerButton from '@/Components/DangerButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.delete(route('current-user.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>
        <template #title>
            删除账户
        </template>

        <template #description>
            永久删除您的账户。
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                一旦您的账户被删除，所有资源和数据将被永久删除。在删除您的账户之前，请下载您想要保留的任何数据或信息。
            </div>

            <div class="mt-5">
                <DangerButton @click="confirmUserDeletion">
                    删除账户
                </DangerButton>
            </div>

            <!-- 删除账户确认对话框 -->
            <DialogModal :show="confirmingUserDeletion" @close="closeModal">
                <template #title>
                    删除账户
                </template>

                <template #content>
                    您确定要删除您的账户吗？一旦您的账户被删除，所有资源和数据将被永久删除。请输入您的密码以确认您要永久删除您的账户。

                    <div class="mt-4">
                        <TextInput
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-3/4"
                            placeholder="密码"
                            autocomplete="current-password"
                            @keyup.enter="deleteUser"
                        />

                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeModal">
                        取消
                    </SecondaryButton>

                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        删除账户
                    </DangerButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
