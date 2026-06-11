import './bootstrap'
import '../css/app.css'

import { createApp, h, type DefineComponent } from 'vue'
import { createInertiaApp, Link } from '@inertiajs/vue3'
import { ZiggyVue } from 'ziggy-js'

createInertiaApp({
    title: (title) => (title ? `${title} · Arcane` : 'Arcane'),

    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true })
        const page = pages[`./Pages/${name}.vue`]
        if (!page) throw new Error(`Inertia page not found: ${name}`)
        return page
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('Link', Link)
            .mount(el)
    },

    progress: { color: '#a78bfa' },
})
