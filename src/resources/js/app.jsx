import '../css/app.css';
import './bootstrap';
import './i18n';

import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import i18n from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

router.on('success', (event) => {
    const locale = event.detail.page.props.locale;

    if (locale) {
        i18n.changeLanguage(locale);
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        const locale = props.initialPage.props.locale;

        if (locale) {
            i18n.changeLanguage(locale);
        }

        const root = createRoot(el);

        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
