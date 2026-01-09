import '../css/app.css';
import './bootstrap';
import { ZiggyVue } from 'ziggy-js';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';

// 1. Import the generated Ziggy object from your local file
// @ts-ignore
import { Ziggy } from './ziggy'; 

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                // Change this line to use the Ziggy you imported at the top
                .use(ZiggyVue, Ziggy) 
                .mount(el);
        },
    progress: {
        color: '#4B5563',
    },
});