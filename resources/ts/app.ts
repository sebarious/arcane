import './bootstrap';
import '../css/app.css';

import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp, Link } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { MotionPlugin } from '@vueuse/motion';

createInertiaApp( {
    title: ( title ) => ( title ? `${title} · Arcane` : 'Arcane' ),

    resolve: ( name ) => {
        const pages = import.meta.glob<DefineComponent>( './Pages/**/*.vue', { eager: true } );
        const page = pages[`./Pages/${name}.vue`];
        if ( !page ) throw new Error( `Inertia page not found: ${name}` );
        return page;
    },

    setup ( { el, App, props, plugin } ) {
        const app = createApp( { render: () => h( App, props ) } );

        app
            .use( plugin )
            .use( ZiggyVue )
            .use( MotionPlugin )
            .component( 'Link', Link )
            .mount( el );
    },

    progress: { color: '#a78bfa' },
} );
