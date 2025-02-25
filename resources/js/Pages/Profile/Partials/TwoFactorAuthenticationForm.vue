<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const page = usePage();

defineProps({
    requiresConfirmation: Boolean,
});

const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(() => {
    return !enabling.value && page.props.auth.user.two_factor_enabled;
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    useForm({}).post(route('two-factor.enable'), {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showSetupKey(),
            showRecoveryCodes(),
        ]),
        onFinish: () => {
            enabling.value = false;
            confirming.value = true;
        },
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then(response => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get(route('two-factor.secret-key')).then(response => {
        setupKey.value = response.data.secretKey;
    });
};

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes')).then(response => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post(route('two-factor.confirm'), {
        preserveScroll: true,
        preserveState: true,
        errorBag: 'confirmTwoFactorAuthentication',
        onSuccess: () => {
            confirming.value = false;
            enabling.value = false;
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios
        .post(route('two-factor.recovery-codes'))
        .then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    useForm({}).delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            enabling.value = false;
        },
    });
};
</script>

<template>
    <ActionSection>
        <template #title>
            两步验证
        </template>

        <template #description>
            为您的账户添加额外的安全保护。
        </template>

        <template #content>
            <h3 v-if="twoFactorEnabled && !enabling" class="text-lg font-medium text-gray-900 dark:text-gray-100">
                您已启用两步验证。
            </h3>

            <h3 v-else-if="twoFactorEnabled && enabling" class="text-lg font-medium text-gray-900 dark:text-gray-100">
                您已启用两步验证。扫描以下二维码或输入设置密钥到您的手机认证应用。
            </h3>

            <h3 v-else class="text-lg font-medium text-gray-900 dark:text-gray-100">
                您尚未启用两步验证。
            </h3>

            <div class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                <p>
                    启用两步验证后，在登录时系统会要求您输入一个安全的随机令牌。您可以从手机的认证应用获取此令牌。
                </p>
            </div>

            <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p v-if="enabling" class="font-semibold">
                            两步验证现已启用。使用您手机的认证应用扫描以下二维码。
                        </p>
                    </div>

                    <div v-show="enabling" class="mt-4">
                        <div class="dark:p-4 dark:bg-white dark:rounded-lg" v-html="qrCode" />
                    </div>

                    <div v-show="enabling" class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold">
                            设置密钥: <span v-html="setupKey" />
                        </p>
                    </div>

                    <div v-if="recoveryCodes.length > 0 && !enabling" class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold">
                            恢复代码
                        </p>
                    </div>

                    <div v-if="recoveryCodes.length > 0 && !enabling" class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 dark:bg-gray-900 rounded-lg">
                        <div v-for="code in recoveryCodes" :key="code">
                            {{ code }}
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <div v-if="!enabling">
                        <PrimaryButton @click="regenerateRecoveryCodes">
                            重新生成恢复代码
                        </PrimaryButton>
                    </div>

                    <div v-if="enabling">
                        <PrimaryButton
                            class="mr-3"
                            :class="{ 'opacity-25': enabling }"
                            :disabled="enabling"
                            @click="confirmTwoFactorAuthentication"
                        >
                            确认
                        </PrimaryButton>
                    </div>

                    <div v-if="!enabling">
                        <PrimaryButton
                            class="ml-3"
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling"
                            @click="disableTwoFactorAuthentication"
                        >
                            禁用
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <div v-if="!twoFactorEnabled">
                <div class="mt-5">
                    <PrimaryButton
                        type="button"
                        :class="{ 'opacity-25': enabling }"
                        :disabled="enabling"
                        @click="enableTwoFactorAuthentication"
                    >
                        启用
                    </PrimaryButton>
                </div>
            </div>

            <!-- 确认两步验证对话框 -->
            <DialogModal :show="confirming" @close="confirming = false">
                <template #title>
                    确认两步验证
                </template>

                <template #content>
                    <div class="grid gap-y-6">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            请完成您的两步验证设置，输入您的认证应用生成的验证码。
                        </p>

                        <div>
                            <TextInput
                                id="code"
                                v-model="confirmationForm.code"
                                type="text"
                                inputmode="numeric"
                                class="block mt-1 w-1/2"
                                placeholder="验证码"
                                autocomplete="one-time-code"
                            />

                            <InputError :message="confirmationForm.errors.code" class="mt-2" />
                        </div>
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="confirming = false">
                        取消
                    </SecondaryButton>

                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': enabling }"
                        :disabled="enabling"
                        @click="confirmTwoFactorAuthentication"
                    >
                        确认
                    </PrimaryButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
