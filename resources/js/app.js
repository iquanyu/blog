import './bootstrap';
import '../css/app.css';
import 'highlight.js/styles/github-dark.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/index.js';
import { createPinia } from 'pinia';
import Toast from '@/Components/Toast.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Blog';

// 创建Toast容器和挂载Toast组件
function createToastContainer() {
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        console.log('创建Toast容器');
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        document.body.appendChild(toastContainer);
        
        // 创建Toast应用实例并挂载
        const toastApp = createApp(Toast);
        toastApp.mount('#toast-container');
        
        console.log('Toast组件已挂载到DOM');
    }
}

// 确保DOM加载完成后创建Toast容器
document.addEventListener('DOMContentLoaded', createToastContainer);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        
        // 创建应用实例
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia);
        
        // 挂载应用
        app.mount(el);
        
        // 确保Toast容器已创建
        createToastContainer();
        
        return app;
    },
    progress: {
        color: '#4B5563',
    },
});
