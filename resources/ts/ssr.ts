import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from 'vue/server-renderer';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { MotionPlugin } from '@vueuse/motion';

const appName = import.meta.env.VITE_APP_NAME || 'Arcane';

function resolvePage ( name: string ) {
  const pages = import.meta.glob<DefineComponent>( './Pages/**/*.vue' );
  return resolvePageComponent<DefineComponent>( `./Pages/${name}.vue`, pages );
}

createServer(
  ( page ) =>
    createInertiaApp( {
      page,
      render: renderToString,
      title: ( title ) => ( title ? `${title} - ${appName}` : appName ),
      resolve: resolvePage,
      setup: ( { App, props, plugin } ) => {
        const app = createSSRApp( { render: () => h( App, props ) } );

        app
          .use( plugin )
          .use( ZiggyVue )
          .use( MotionPlugin );

        return app;
      },
    } ),
  { cluster: true },
);
