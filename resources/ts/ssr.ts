import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from 'vue/server-renderer';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { MotionPlugin } from '@vueuse/motion';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import AppRoot from './Components/AppRoot.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Arcane';

function resolvePage ( name: string ) {
  const pages = import.meta.glob<DefineComponent>( './Pages/**/*.vue' );
  return resolvePageComponent<DefineComponent>( `./Pages/${name}.vue`, pages );
}

// Vue/Inertia's SSR helpers don't inject per-page CSS on their own — Vite
// splits each page's <style> block into its own chunk (see vite-plugin's
// ssrManifestPlugin), which the browser would normally pick up via the
// client-side chunk import, but during SSR there's no browser to do that, so
// without this the page renders with only the shared app.css. The manifest
// (built alongside ssr.js) maps each page's source path straight to its
// built CSS files, so we can resolve it directly from `page.component`
// rather than needing Vue to track which modules a given render touched.
const manifestPath = path.join( path.dirname( fileURLToPath( import.meta.url ) ), 'ssr-manifest.json' );
const manifest: Record<string, string[]> = JSON.parse( readFileSync( manifestPath, 'utf-8' ) );

function cssLinksFor ( component: string ): string[] {
  const files = manifest[`resources/ts/Pages/${component}.vue`] ?? [];
  return files.filter( ( file ) => file.endsWith( '.css' ) )
    .map( ( href ) => `<link rel="stylesheet" href="${href}">` );
}

createServer(
  ( page ) =>
    createInertiaApp( {
      page,
      render: renderToString,
      title: ( title ) => ( title ? `${title} - ${appName}` : appName ),
      resolve: resolvePage,
      setup: ( { App, props, plugin } ) => {
        const app = createSSRApp( { render: () => h( AppRoot, { appComponent: App, appProps: props } ) } );

        app
          .use( plugin )
          .use( ZiggyVue )
          .use( MotionPlugin );

        return app;
      },
    } ).then( ( result ) => {
      result.head.push( ...cssLinksFor( page.component ) );
      return result;
    } ),
  { cluster: true },
);
