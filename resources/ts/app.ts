import './bootstrap';
import '../css/app.css';

import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { MotionPlugin } from '@vueuse/motion';
import AppRoot from './Components/AppRoot.vue';

createInertiaApp( {
    title: ( title ) => ( title ? `${title} · Arcane` : 'Arcane' ),

    // Lazy (non-eager) glob + resolvePageComponent — matches ssr.ts. Eager
    // loading here bundled every page in the app (kiosk, seller dashboard,
    // storefront, everything) into one ~500KB+ file that every visitor had to
    // download and parse before the homepage could even hydrate; this way
    // each page is its own chunk, fetched only when actually visited.
    resolve: ( name ) =>
        resolvePageComponent<DefineComponent>(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>( './Pages/**/*.vue' ),
        ),

    setup ( { el, App, props, plugin } ) {
        const app = createApp( { render: () => h( AppRoot, { appComponent: App, appProps: props } ) } );

        app
            .use( plugin )
            .use( ZiggyVue )
            .use( MotionPlugin )
            .component( 'Link', Link )
            .mount( el );
    },

    progress: { color: '#a78bfa' },
} );
