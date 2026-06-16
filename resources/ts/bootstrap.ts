import axios, { type AxiosInstance } from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        axios: AxiosInstance;
        Echo: Echo<any>;
        Pusher: typeof Pusher;
    }
}
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;
window.Echo = new Echo( {
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY ?? '',
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: Number( import.meta.env.VITE_REVERB_PORT ?? 8080 ),
    wssPort: Number( import.meta.env.VITE_REVERB_PORT ?? 8080 ),
    forceTLS: ( import.meta.env.VITE_REVERB_SCHEME ?? 'https' ) === 'https',
    enabledTransports: ['ws', 'wss'],
} );