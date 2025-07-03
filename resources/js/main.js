import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import i18n from './i18n';
import { useTranslation } from './i18n/useTranslation';
import DataTable from './DataTable.vue';

createInertiaApp({
  resolve: name => DataTable,
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) });
    app.use(plugin);
    app.use(i18n);

    // Get the translation function from the useTranslation hook
    const { t } = useTranslation();

    // Provide the translation function to all components
    app.provide('t', t);

    app.mount(el);
  },
});
