import { t as Link___Arcane_default } from "./Link___Arcane-CV1ANrTJ.js";
import { Link, usePage } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderComponent, ssrRenderList, ssrRenderSlot, ssrRenderVNode } from "vue/server-renderer";
import { createBlock, createTextVNode, createVNode, defineComponent, mergeProps, openBlock, ref, resolveDynamicComponent, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Check, Copy, ExternalLink, FileText, LayoutDashboard, LogOut, Menu, PackageSearch, Plus, UserRound, Wallet, X } from "lucide-vue-next";
//#region resources/ts/Layouts/SellerLayout.vue?vue&type=script&setup=true&lang.ts
var SellerLayout_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "SellerLayout",
	__ssrInlineRender: true,
	props: {
		title: {},
		subtitle: {}
	},
	setup(__props) {
		const page = usePage();
		const currentRouteName = () => page.props.route?.name;
		const NAV_LINKS = [
			{
				label: "Dashboard",
				href: "/seller",
				match: "seller.dashboard",
				icon: LayoutDashboard
			},
			{
				label: "Batches",
				href: "/seller/batches",
				match: "seller.batches",
				icon: PackageSearch
			},
			{
				label: "Invoices",
				href: "/seller/invoices",
				match: "seller.invoices",
				icon: FileText
			},
			{
				label: "Wallet",
				href: "/seller/wallet",
				match: "seller.wallet",
				icon: Wallet
			},
			{
				label: "Store profile",
				href: "/seller/profile",
				match: "seller.profile",
				icon: UserRound
			}
		];
		const isActive = (match) => (currentRouteName() ?? "").startsWith(match);
		const batchRequestsEnabled = () => page.props.batchRequestsEnabled ?? true;
		const mobileOpen = ref(false);
		const walletPence = () => page.props.sellerWallet ?? 0;
		function formatPence(pence) {
			return "£" + (pence / 100).toFixed(2);
		}
		const affiliateCode = () => page.props.sellerAffiliateCode ?? null;
		const affiliateCopied = ref(false);
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen bg-[#0d0b14] text-white flex" }, _attrs))}><aside class="hidden lg:flex flex-col w-[248px] shrink-0 h-screen sticky top-0 bg-[#0a0810] border-r border-[rgba(220,193,117,0.1)]"><div class="px-6 py-6 border-b border-[rgba(220,193,117,0.08)]">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/",
				class: "flex items-center gap-2"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<img${ssrRenderAttr("src", unref(Link___Arcane_default))} alt="Arcane" class="h-8 w-auto"${_scopeId}>`);
					else return [createVNode("img", {
						src: unref(Link___Arcane_default),
						alt: "Arcane",
						class: "h-8 w-auto"
					}, null, 8, ["src"])];
				}),
				_: 1
			}, _parent));
			_push(`<p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[11px] tracking-[0.25em] text-[#7b4fe9] uppercase mt-3"> Seller area </p></div><nav class="flex-1 px-3 py-5 flex flex-col gap-1"><!--[-->`);
			ssrRenderList(NAV_LINKS, (link) => {
				_push(ssrRenderComponent(unref(Link), {
					key: link.href,
					href: link.href,
					class: ["flex items-center gap-3 px-3 py-2.5 rounded-[6px] text-sm font-['Jost',sans-serif] font-medium transition-colors", isActive(link.match) ? "bg-[rgba(124,58,237,0.15)] text-white border border-[rgba(124,58,237,0.35)]" : "text-[#a3a3a3] hover:text-white hover:bg-[rgba(255,255,255,0.03)] border border-transparent"]
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) {
							ssrRenderVNode(_push, createVNode(resolveDynamicComponent(link.icon), { class: "size-4 shrink-0" }, null), _parent, _scopeId);
							_push(` ${ssrInterpolate(link.label)}`);
						} else return [(openBlock(), createBlock(resolveDynamicComponent(link.icon), { class: "size-4 shrink-0" })), createTextVNode(" " + toDisplayString(link.label), 1)];
					}),
					_: 2
				}, _parent));
			});
			_push(`<!--]--></nav><div class="px-3 pb-3">`);
			if (batchRequestsEnabled()) _push(ssrRenderComponent(unref(Link), {
				href: "/seller/request-batch",
				class: "flex items-center justify-center gap-2 w-full px-3 py-2.5 rounded-[6px] text-[#0d0b14] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide",
				style: { "background-image": "linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%)" }
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(ssrRenderComponent(unref(Plus), { class: "size-4" }, null, _parent, _scopeId));
						_push(` Request batch `);
					} else return [createVNode(unref(Plus), { class: "size-4" }), createTextVNode(" Request batch ")];
				}),
				_: 1
			}, _parent));
			else {
				_push(`<button type="button" disabled title="Batch requests are temporarily unavailable" class="flex items-center justify-center gap-2 w-full px-3 py-2.5 rounded-[6px] bg-[rgba(255,255,255,0.05)] text-[#71717a] text-sm font-[&#39;Jost&#39;,sans-serif] font-bold uppercase tracking-wide cursor-not-allowed">`);
				_push(ssrRenderComponent(unref(Plus), { class: "size-4" }, null, _parent));
				_push(` Request batch </button>`);
			}
			_push(`</div><div class="px-3 pb-5 pt-2 border-t border-[rgba(220,193,117,0.08)] flex flex-col gap-1"><a href="/" class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-xs text-[#71717a] hover:text-white font-[&#39;Jost&#39;,sans-serif]">`);
			_push(ssrRenderComponent(unref(ExternalLink), { class: "size-3.5" }, null, _parent));
			_push(` Visit main site </a>`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/logout",
				method: "get",
				as: "button",
				class: "flex items-center gap-3 px-3 py-2 rounded-[6px] text-xs text-[#71717a] hover:text-red-400 font-['Jost',sans-serif] text-left w-full"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(ssrRenderComponent(unref(LogOut), { class: "size-3.5" }, null, _parent, _scopeId));
						_push(` Log out `);
					} else return [createVNode(unref(LogOut), { class: "size-3.5" }), createTextVNode(" Log out ")];
				}),
				_: 1
			}, _parent));
			_push(`</div></aside><div class="lg:hidden fixed top-0 left-0 right-0 z-40 flex items-center justify-between px-5 py-4 bg-[#0a0810] border-b border-[rgba(220,193,117,0.1)]">`);
			_push(ssrRenderComponent(unref(Link), { href: "/" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<img${ssrRenderAttr("src", unref(Link___Arcane_default))} alt="Arcane" class="h-8 w-auto"${_scopeId}>`);
					else return [createVNode("img", {
						src: unref(Link___Arcane_default),
						alt: "Arcane",
						class: "h-8 w-auto"
					}, null, 8, ["src"])];
				}),
				_: 1
			}, _parent));
			_push(`<button class="text-[#DCC175]">`);
			if (mobileOpen.value) _push(ssrRenderComponent(unref(X), { size: 22 }, null, _parent));
			else _push(ssrRenderComponent(unref(Menu), { size: 22 }, null, _parent));
			_push(`</button></div>`);
			if (mobileOpen.value) {
				_push(`<div class="lg:hidden fixed inset-0 z-30 bg-[#0a0810] pt-20 px-5 flex flex-col gap-1"><!--[-->`);
				ssrRenderList(NAV_LINKS, (link) => {
					_push(ssrRenderComponent(unref(Link), {
						key: link.href,
						href: link.href,
						onClick: ($event) => mobileOpen.value = false,
						class: ["flex items-center gap-3 px-3 py-3 rounded-[6px] text-base font-['Jost',sans-serif]", isActive(link.match) ? "text-white bg-[rgba(124,58,237,0.15)]" : "text-[#a3a3a3]"]
					}, {
						default: withCtx((_, _push, _parent, _scopeId) => {
							if (_push) {
								ssrRenderVNode(_push, createVNode(resolveDynamicComponent(link.icon), { class: "size-5 shrink-0" }, null), _parent, _scopeId);
								_push(` ${ssrInterpolate(link.label)}`);
							} else return [(openBlock(), createBlock(resolveDynamicComponent(link.icon), { class: "size-5 shrink-0" })), createTextVNode(" " + toDisplayString(link.label), 1)];
						}),
						_: 2
					}, _parent));
				});
				_push(`<!--]-->`);
				if (batchRequestsEnabled()) _push(ssrRenderComponent(unref(Link), {
					href: "/seller/request-batch",
					onClick: ($event) => mobileOpen.value = false,
					class: "mt-4 flex items-center justify-center gap-2 w-full px-3 py-3 rounded-[6px] text-[#0d0b14] font-['Jost',sans-serif] font-bold uppercase tracking-wide",
					style: { "background-image": "linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%)" }
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) {
							_push(ssrRenderComponent(unref(Plus), { class: "size-4" }, null, _parent, _scopeId));
							_push(` Request batch `);
						} else return [createVNode(unref(Plus), { class: "size-4" }), createTextVNode(" Request batch ")];
					}),
					_: 1
				}, _parent));
				else {
					_push(`<button type="button" disabled class="mt-4 flex items-center justify-center gap-2 w-full px-3 py-3 rounded-[6px] bg-[rgba(255,255,255,0.05)] text-[#71717a] font-[&#39;Jost&#39;,sans-serif] font-bold uppercase tracking-wide cursor-not-allowed">`);
					_push(ssrRenderComponent(unref(Plus), { class: "size-4" }, null, _parent));
					_push(` Request batch </button>`);
				}
				_push(ssrRenderComponent(unref(Link), {
					href: "/logout",
					method: "get",
					as: "button",
					class: "mt-6 flex items-center gap-3 px-3 py-3 text-sm text-[#71717a]"
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) {
							_push(ssrRenderComponent(unref(LogOut), { class: "size-4" }, null, _parent, _scopeId));
							_push(` Log out `);
						} else return [createVNode(unref(LogOut), { class: "size-4" }), createTextVNode(" Log out ")];
					}),
					_: 1
				}, _parent));
				_push(`</div>`);
			} else _push(`<!---->`);
			_push(`<div class="flex-1 min-w-0 pt-20 lg:pt-0"><header class="border-b border-[rgba(220,193,117,0.08)] px-6 lg:px-10 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"><div><h1 class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-2xl lg:text-[28px] text-white">${ssrInterpolate(__props.title)}</h1>`);
			if (__props.subtitle) _push(`<p class="font-[&#39;Jost&#39;,sans-serif] text-sm text-[#a3a3a3] mt-1">${ssrInterpolate(__props.subtitle)}</p>`);
			else _push(`<!---->`);
			_push(`</div><div class="flex items-center gap-3">`);
			if (affiliateCode()) {
				_push(`<button type="button" class="flex items-center gap-3 bg-[#13101e] border border-[rgba(201,168,76,0.25)] rounded-[8px] px-4 py-2.5 shrink-0 hover:border-[rgba(201,168,76,0.5)] transition-colors">`);
				ssrRenderVNode(_push, createVNode(resolveDynamicComponent(affiliateCopied.value ? unref(Check) : unref(Copy)), { class: "size-4 text-[#c9a84c]" }, null), _parent);
				_push(`<div class="text-left"><p class="font-[&#39;Jost&#39;,sans-serif] text-[10px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide leading-none">Affiliate code</p><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-sm text-[#c9a84c] leading-tight mt-0.5">${ssrInterpolate(affiliateCopied.value ? "Copied!" : affiliateCode())}</p></div></button>`);
			} else _push(`<!---->`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/seller/wallet",
				class: "flex items-center gap-3 bg-[#13101e] border border-[rgba(201,168,76,0.25)] rounded-[8px] px-4 py-2.5 shrink-0 hover:border-[rgba(201,168,76,0.5)] transition-colors"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(ssrRenderComponent(unref(Wallet), { class: "size-4 text-[#c9a84c]" }, null, _parent, _scopeId));
						_push(`<div${_scopeId}><p class="font-[&#39;Jost&#39;,sans-serif] text-[10px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide leading-none"${_scopeId}>Wallet</p><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-sm text-[#c9a84c] leading-tight mt-0.5"${_scopeId}>${ssrInterpolate(formatPence(walletPence()))}</p></div>`);
					} else return [createVNode(unref(Wallet), { class: "size-4 text-[#c9a84c]" }), createVNode("div", null, [createVNode("p", { class: "font-['Jost',sans-serif] text-[10px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide leading-none" }, "Wallet"), createVNode("p", { class: "font-['Cinzel',sans-serif] font-bold text-sm text-[#c9a84c] leading-tight mt-0.5" }, toDisplayString(formatPence(walletPence())), 1)])];
				}),
				_: 1
			}, _parent));
			_push(`</div></header><main class="px-6 lg:px-10 py-8">`);
			ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
			_push(`</main></div></div>`);
		};
	}
});
//#endregion
//#region resources/ts/Layouts/SellerLayout.vue
var _sfc_setup = SellerLayout_vue_vue_type_script_setup_true_lang_default.setup;
SellerLayout_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Layouts/SellerLayout.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var SellerLayout_default = SellerLayout_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { SellerLayout_default as t };

//# sourceMappingURL=SellerLayout-Brsy-I1H.js.map