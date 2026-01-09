import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, DefineComponent, defineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Eagerly import all pages so the resolver never returns null
// Using eager: true ensures all components are loaded upfront, avoiding CSP issues with dynamic imports
const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true });

// Simple fallback component to avoid hard crashes if a page cannot be resolved in production
const FallbackPage = defineComponent({
    setup() {
        return () =>
            h(
                'div',
                {
                    style: {
                        minHeight: '100vh',
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontFamily: 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                        backgroundColor: '#f3f4f6',
                        color: '#111827',
                        padding: '1.5rem',
                        textAlign: 'center',
                    },
                },
                [
                    h('h1', { style: { fontSize: '1.5rem', fontWeight: 'bold', marginBottom: '1rem' } }, 'Page Not Found'),
                    h('p', { style: { marginBottom: '1rem' } }, 'Sorry, something went wrong while loading this page.'),
                    h('button', {
                        style: {
                            padding: '0.5rem 1rem',
                            backgroundColor: '#2563eb',
                            color: 'white',
                            border: 'none',
                            borderRadius: '0.375rem',
                            cursor: 'pointer',
                        },
                        onClick: () => window.location.reload(),
                    }, 'Refresh Page'),
                ],
            );
    },
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    // Custom resolver that never returns null; works with eager imports
    resolve: (name) => {
        try {
            // With eager: true, pages are already resolved components
            // The key format matches: ./Pages/ComponentName.vue
            const key = `./Pages/${name}.vue`;
            const pageModule = pages[key];

            if (!pageModule) {
                console.error(
                    '[Inertia] Page component not found for name:',
                    name,
                    'Expected key:',
                    key,
                    'Available pages:',
                    Object.keys(pages).map(k => k.replace('./Pages/', '').replace('.vue', '')),
                );
                return FallbackPage;
            }

            // With eager imports, the module is already resolved
            // Extract the component (could be default export or named export)
            const component = (pageModule as any).default || pageModule;

            if (!component) {
                console.error('[Inertia] Page component module has no default export:', name);
                return FallbackPage;
            }

            return component;
        } catch (error) {
            console.error('[Inertia] Error resolving page component:', name, error);
            return FallbackPage;
        }
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
