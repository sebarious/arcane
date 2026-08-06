import { createInertiaApp, usePage } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { renderToString, ssrInterpolate, ssrRenderAttrs, ssrRenderComponent, ssrRenderVNode } from "vue/server-renderer";
import { computed, createSSRApp, createVNode, defineComponent, h, mergeProps, resolveDynamicComponent, useSSRContext } from "vue";
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
//#region resources/ts/Components/ImpersonationBanner.vue?vue&type=script&setup=true&lang.ts
var ImpersonationBanner_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "ImpersonationBanner",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const impersonating = computed(() => !!page.props?.impersonating);
		const userName = computed(() => page.props?.auth?.user?.name);
		return (_ctx, _push, _parent, _attrs) => {
			if (impersonating.value) _push(`<div${ssrRenderAttrs(mergeProps({ class: "fixed inset-x-0 top-0 z-[999] w-full bg-[#c9a84c] text-[#0d0b14] font-['Jost',sans-serif]" }, _attrs))}><div class="flex items-center justify-center gap-3 px-4 py-2 text-sm"><span class="font-semibold">Viewing as ${ssrInterpolate(userName.value)}</span><button type="button" class="font-semibold uppercase tracking-wide underline underline-offset-2 hover:opacity-70 transition-opacity"> Stop impersonating </button></div></div>`);
			else _push(`<!---->`);
		};
	}
});
//#endregion
//#region resources/ts/Components/ImpersonationBanner.vue
var _sfc_setup$1 = ImpersonationBanner_vue_vue_type_script_setup_true_lang_default.setup;
ImpersonationBanner_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/ImpersonationBanner.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var ImpersonationBanner_default = ImpersonationBanner_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/AppRoot.vue?vue&type=script&setup=true&lang.ts
var AppRoot_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "AppRoot",
	__ssrInlineRender: true,
	props: {
		appComponent: {},
		appProps: {}
	},
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			ssrRenderVNode(_push, createVNode(resolveDynamicComponent(__props.appComponent), __props.appProps, null), _parent);
			_push(ssrRenderComponent(ImpersonationBanner_default, null, null, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/ts/Components/AppRoot.vue
var _sfc_setup = AppRoot_vue_vue_type_script_setup_true_lang_default.setup;
AppRoot_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/AppRoot.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var AppRoot_default = AppRoot_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/ssr.ts
var appName = "Arcane";
function resolvePage(name) {
	const pages = /* #__PURE__ */ Object.assign({
		"./Pages/Auth/ForgotPassword.vue": () => import("./assets/ForgotPassword-DzhrZgNv.js"),
		"./Pages/Auth/Login.vue": () => import("./assets/Login-D7W14W4U.js"),
		"./Pages/Auth/ResetPassword.vue": () => import("./assets/ResetPassword-h4mRRJ9Y.js"),
		"./Pages/Legal/PrivacyPolicy.vue": () => import("./assets/PrivacyPolicy-_YNB1nH8.js"),
		"./Pages/Legal/Terms.vue": () => import("./assets/Terms-zXgKrPCi.js"),
		"./Pages/Marketing/AffiliateProgram.vue": () => import("./assets/AffiliateProgram-Bfu3lvVI.js"),
		"./Pages/Sell/Create.vue": () => import("./assets/Create-Cla8fuK2.js"),
		"./Pages/Sell/ThankYou.vue": () => import("./assets/ThankYou-BkpMIu0d.js"),
		"./Pages/Seller/BatchRequest.vue": () => import("./assets/BatchRequest-7KoArm1g.js"),
		"./Pages/Seller/BatchShow.vue": () => import("./assets/BatchShow-DaiqL_KJ.js"),
		"./Pages/Seller/BatchesIndex.vue": () => import("./assets/BatchesIndex-DTZ143lG.js"),
		"./Pages/Seller/Dashboard.vue": () => import("./assets/Dashboard-ITwRJwPl.js"),
		"./Pages/Seller/InvoicesIndex.vue": () => import("./assets/InvoicesIndex-D_tZyadH.js"),
		"./Pages/Seller/Pending.vue": () => import("./assets/Pending-Djg2XsJ9.js"),
		"./Pages/Seller/Profile.vue": () => import("./assets/Profile-QSfj9J0L.js"),
		"./Pages/Seller/Wallet.vue": () => import("./assets/Wallet-B2q7AFaC.js"),
		"./Pages/SellerApplications/Create.vue": () => import("./assets/Create-D9yTTBbr.js"),
		"./Pages/SellerApplications/ThankYou.vue": () => import("./assets/ThankYou-BBLnHmj9.js"),
		"./Pages/Storefront/BatchListShow.vue": () => import("./assets/BatchListShow-CAMDIXix.js"),
		"./Pages/Storefront/CardListIndex.vue": () => import("./assets/CardListIndex-C8Poma2x.js"),
		"./Pages/Storefront/StoreIndex.vue": () => import("./assets/StoreIndex-B7qPaSTU.js"),
		"./Pages/Storefront/StoreShow.vue": () => import("./assets/StoreShow-CXXmcYMG.js"),
		"./Pages/Welcome.vue": () => import("./assets/Welcome-BKIrO3o4.js")
	});
	return resolvePageComponent(`./Pages/${name}.vue`, pages);
}
var renderPage = (page) => createInertiaApp({
	page,
	render: renderToString,
	title: (title) => title ? `${title} - ${appName}` : appName,
	resolve: resolvePage,
	setup: ({ App, props, plugin }) => {
		const app = createSSRApp({ render: () => h(AppRoot_default, {
			appComponent: App,
			appProps: props
		}) });
		app.use(plugin).use(ZiggyVue).use(MotionPlugin);
		return app;
	}
});
createServer(renderPage, { cluster: true });
//#endregion
export { renderPage as default };

//# sourceMappingURL=ssr.js.map