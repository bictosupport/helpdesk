import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import Echo from 'laravel-echo';

import Donut from 'vue-css-donut-chart';
import 'vue-css-donut-chart/dist/vcdonut.css';

// PrimeVue Imports
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import 'primeicons/primeicons.css';

window.Pusher = require('pusher-js');

if (process.env.MIX_PUSHER_APP_KEY && process.env.MIX_PUSHER_APP_CLUSTER) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_APP_KEY,
        cluster: process.env.MIX_PUSHER_APP_CLUSTER,
        forceTLS: true
    });
}

createInertiaApp({
    progress: {
        delay: 100,
        color: '#7366FF',
        includeCSS: true,
        showSpinner: true,
    },
    resolve: name => require(`./Pages/${name}`),
    title: title => title ? `${title}` : ``,
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(PrimeVue, {
                ripple: true,
                theme: {
                    preset: Aura,
                    options: {
                        prefix: 'p',
                        darkModeSelector: 'light',
                        cssLayer: false
                    }
                }
            })
            .mixin({ methods: { route: route } })
            .mixin(require('./base'))
            .use(plugin)
            .use(Donut)
            .mount(el);
    },
});
