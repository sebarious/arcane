import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { renderToString } from "vue/server-renderer";
import { createSSRApp, h } from "vue";
import { ZiggyVue } from "ziggy-js";
import { MotionPlugin } from "@vueuse/motion";
//#region node_modules/laravel-vite-plugin/inertia-helpers/index.js
async function resolvePageComponent(path, pages) {
	for (const p of Array.isArray(path) ? path : [path]) {
		const page = pages[p];
		if (typeof page === "undefined") continue;
		return typeof page === "function" ? page() : page;
	}
	throw new Error(`Page not found: ${path}`);
}
//#endregion
//#region resources/ts/ssr.ts
var appName = "Arcane";
function resolvePage(name) {
	const pages = /* #__PURE__ */ Object.assign({
		"./Pages/Auth/ForgotPassword.vue": () => import("./assets/ForgotPassword-B-r2EEpY.js"),
		"./Pages/Auth/Login.vue": () => import("./assets/Login-Cn0ZmYau.js"),
		"./Pages/Auth/ResetPassword.vue": () => import("./assets/ResetPassword-BDGNKG8R.js"),
		"./Pages/Sell/Create.vue": () => import("./assets/Create-lM63Pil9.js"),
		"./Pages/Sell/ThankYou.vue": () => import("./assets/ThankYou-D5GjAv2e.js"),
		"./Pages/Seller/BatchRequest.vue": () => import("./assets/BatchRequest-CT5mtSdW.js"),
		"./Pages/Seller/BatchShow.vue": () => import("./assets/BatchShow-BGf7SdAo.js"),
		"./Pages/Seller/BatchesIndex.vue": () => import("./assets/BatchesIndex-CKMSPFWv.js"),
		"./Pages/Seller/Dashboard.vue": () => import("./assets/Dashboard-DVyALoHA.js"),
		"./Pages/SellerApplications/Create.vue": () => import("./assets/Create-D9Jstvcb.js"),
		"./Pages/SellerApplications/ThankYou.vue": () => import("./assets/ThankYou-B3QZXXVt.js"),
		"./Pages/Storefront/BatchListShow.vue": () => import("./assets/BatchListShow-lV537PKE.js"),
		"./Pages/Storefront/StoreIndex.vue": () => import("./assets/StoreIndex-tT5Xy6S-.js"),
		"./Pages/Storefront/StoreShow.vue": () => import("./assets/StoreShow-T9Z4DxjM.js"),
		"./Pages/Welcome.vue": () => import("./assets/Welcome-C4mGKelr.js")
	});
	return resolvePageComponent(`./Pages/${name}.vue`, pages);
}
var renderPage = (page) => createInertiaApp({
	page,
	render: renderToString,
	title: (title) => title ? `${title} - ${appName}` : appName,
	resolve: resolvePage,
	setup: ({ App, props, plugin }) => {
		const app = createSSRApp({ render: () => h(App, props) });
		app.use(plugin).use(ZiggyVue).use(MotionPlugin);
		return app;
	}
});
createServer(renderPage, { cluster: true });
//#endregion
export { renderPage as default };

//# sourceMappingURL=ssr.js.map