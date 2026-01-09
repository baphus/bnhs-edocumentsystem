import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Eagerly import all pages so the resolver never returns null
const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true });

// Simple fallback component to avoid hard crashes if a page cannot be resolved in production
const FallbackPage: DefineComponent = {
    setup() {
        return () =>
            h(
                'div',
                {
                    style: {
                        minHeight: '100vh',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontFamily: 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                        backgroundColor: '#f3f4f6',
                        color: '#111827',
                        padding: '1.5rem',
                        textAlign: 'center',
                    },
                },
                'Sorry, something went wrong while loading this page. Please refresh the page or try again later.',
            );
    },
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    // Custom resolver that never returns null; logs helpful info in case of mismatch
    resolve: (name) => {
        const key = `./Pages/${name}.vue`;
        const page = pages[key];

        if (!page) {
            console.error(
                '[Inertia] Page component not found for name:',
                name,
                'Expected key:',
                key,
                'Available pages:',
                Object.keys(pages),
            );
            return FallbackPage;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
