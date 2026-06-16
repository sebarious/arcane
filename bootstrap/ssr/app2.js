import { computed, createApp, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, h, mergeProps, onBeforeUnmount, onMounted, openBlock, ref, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { renderToString, ssrIncludeBooleanAttr, ssrInterpolate, ssrLooseContain, ssrLooseEqual, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { Link, createInertiaApp, useForm, usePage } from "@inertiajs/vue3";
import axios from "axios";
import Echo from "laravel-echo";
import Pusher from "pusher-js";
import { ZiggyVue } from "ziggy-js";
import createServer from "@inertiajs/vue3/server";
//#region bootstrap/ssr/app2.js
var __defProp = Object.defineProperty;
var __exportAll = (all, no_symbols) => {
	let target = {};
	for (var name in all) __defProp(target, name, {
		get: all[name],
		enumerable: true
	});
	if (!no_symbols) __defProp(target, Symbol.toStringTag, { value: "Module" });
	return target;
};
var Login_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Login",
	__ssrInlineRender: true,
	setup(__props) {
		const form = useForm({
			email: "",
			password: "",
			remember: false
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "futuristic-grid" }, _attrs))}><div class="min-h-screen flex items-center justify-center bg-arcane-bg/80 text-arcane-text"><div class="card-panel w-full max-w-md p-6"><div class="mb-6 text-center"><div class="font-display text-2xl tracking-[0.3em] text-arcane-accent mb-2"><img src="/images/logo.png" alt="Arcane" class="h-20 mx-auto"></div><p class="text-arcane-muted text-sm"> Sign in to access your dashboard. </p></div><form class="space-y-4"><div><label class="block text-sm font-medium mb-1" for="email">Email</label><input id="email"${ssrRenderAttr("value", unref(form).email)} type="email" autocomplete="email" required class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white">`);
			if (unref(form).errors.email) _push(`<div class="text-xs text-red-400 mt-1">${ssrInterpolate(unref(form).errors.email)}</div>`);
			else _push(`<!---->`);
			_push(`</div><div><label class="block text-sm font-medium mb-1" for="password">Password</label><input id="password"${ssrRenderAttr("value", unref(form).password)} type="password" autocomplete="current-password" required class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white">`);
			if (unref(form).errors.password) _push(`<div class="text-xs text-red-400 mt-1">${ssrInterpolate(unref(form).errors.password)}</div>`);
			else _push(`<!---->`);
			_push(`</div><div class="flex items-center justify-between text-xs text-arcane-muted"><label class="inline-flex items-center gap-2"><input${ssrIncludeBooleanAttr(Array.isArray(unref(form).remember) ? ssrLooseContain(unref(form).remember, null) : unref(form).remember) ? " checked" : ""} type="checkbox" class="rounded border-arcane-border bg-arcane-surface"><span>Remember me</span></label></div><div class="outline-root w-full"><button type="submit" class="btn-primary w-full justify-center outline-inner"${ssrIncludeBooleanAttr(unref(form).processing) ? " disabled" : ""}>`);
			if (unref(form).processing) _push(`<span>Signing in…</span>`);
			else _push(`<span>Sign in</span>`);
			_push(`</button></div></form></div></div></div>`);
		};
	}
});
var Login_exports = /* @__PURE__ */ __exportAll({ default: () => Login_default });
var _sfc_setup$13 = Login_vue_vue_type_script_setup_true_lang_default.setup;
Login_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Auth/Login.vue");
	return _sfc_setup$13 ? _sfc_setup$13(props, ctx) : void 0;
};
var Login_default = Login_vue_vue_type_script_setup_true_lang_default;
var Header_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Header",
	__ssrInlineRender: true,
	setup(__props) {
		const user = (usePage()?.props?.auth)?.user;
		console.log(user);
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<header${ssrRenderAttrs(mergeProps({ class: "sticky top-0 border-b border-arcane-border/60 bg-arcane-bg/90 backdrop-blur z-10" }, _attrs))}><div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/",
				class: "flex items-center gap-2"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<img src="/images/logo.png" alt="Arcane" class="h-10"${_scopeId}>`);
					else return [createVNode("img", {
						src: "/images/logo.png",
						alt: "Arcane",
						class: "h-10"
					})];
				}),
				_: 1
			}, _parent));
			_push(`<nav class="flex items-center gap-4 text-sm text-white/70">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/stores",
				class: "hover:text-white"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Stores`);
					else return [createTextVNode("Stores")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), {
				href: "/sell",
				class: "hover:text-white"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Sell to us`);
					else return [createTextVNode("Sell to us")];
				}),
				_: 1
			}, _parent));
			if (!unref(user)?.id) {
				_push(`<!--[-->`);
				_push(ssrRenderComponent(unref(Link), {
					href: "/login",
					class: "btn-ghost text-xs"
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`Log in`);
						else return [createTextVNode("Log in")];
					}),
					_: 1
				}, _parent));
				_push(`<div class="outline-root">`);
				_push(ssrRenderComponent(unref(Link), {
					href: "/apply",
					class: "btn-primary text-xs outline-inner"
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`<span${_scopeId}>Become a seller</span>`);
						else return [createVNode("span", null, "Become a seller")];
					}),
					_: 1
				}, _parent));
				_push(`</div><!--]-->`);
			} else _push(`<div class="outline-root"><a href="/dashboard" class="btn-primary text-xs outline-inner"><span>Dashboard</span></a></div>`);
			_push(`</nav></div></header>`);
		};
	}
});
var _sfc_setup$12 = Header_vue_vue_type_script_setup_true_lang_default.setup;
Header_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Layout/Header.vue");
	return _sfc_setup$12 ? _sfc_setup$12(props, ctx) : void 0;
};
var Header_default = Header_vue_vue_type_script_setup_true_lang_default;
var Create_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Create",
	__ssrInlineRender: true,
	setup(__props) {
		const form = useForm({
			customer_name: "",
			customer_email: "",
			customer_phone: "",
			customer_postcode: "",
			description: "",
			images: []
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen bg-arcane-bg text-arcane-text flex flex-col" }, _attrs))}>`);
			_push(ssrRenderComponent(Header_default, null, null, _parent));
			_push(`<main class="flex-1"><div class="max-w-4xl mx-auto px-6 py-8"><div class="card-panel p-6"><h1 class="font-display text-2xl mb-2"> Sell your cards to Arcane </h1><p class="text-arcane-muted text-sm mb-6"> Upload photos of the cards or sealed product you want to sell and tell us what’s in the lot. We’ll review and email you an offer. </p><form class="space-y-4"><div class="grid md:grid-cols-2 gap-4"><div><label class="block text-xs font-medium mb-1">Name</label><input${ssrRenderAttr("value", unref(form).customer_name)} type="text" class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:ring-2 focus:ring-arcane-accent focus:outline-none" required>`);
			if (unref(form).errors.customer_name) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors.customer_name)}</div>`);
			else _push(`<!---->`);
			_push(`</div><div><label class="block text-xs font-medium mb-1">Email</label><input${ssrRenderAttr("value", unref(form).customer_email)} type="email" class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:ring-2 focus:ring-arcane-accent focus:outline-none" required>`);
			if (unref(form).errors.customer_email) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors.customer_email)}</div>`);
			else _push(`<!---->`);
			_push(`</div></div><div class="grid md:grid-cols-2 gap-4"><div><label class="block text-xs font-medium mb-1">Phone (optional)</label><input${ssrRenderAttr("value", unref(form).customer_phone)} type="text" class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:ring-2 focus:ring-arcane-accent focus:outline-none"></div><div><label class="block text-xs font-medium mb-1">Postcode (optional)</label><input${ssrRenderAttr("value", unref(form).customer_postcode)} type="text" class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:ring-2 focus:ring-arcane-accent focus:outline-none"></div></div><div><label class="block text-xs font-medium mb-1">What are you selling?</label><textarea rows="4" class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:ring-2 focus:ring-arcane-accent focus:outline-none" placeholder="e.g. 3 binders of Pokémon EX-era cards, 2 sealed ETBs, mixed bulk…" required>${ssrInterpolate(unref(form).description)}</textarea>`);
			if (unref(form).errors.description) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors.description)}</div>`);
			else _push(`<!---->`);
			_push(`</div><div><label class="block text-xs font-medium mb-1">Photos (up to 8)</label><input type="file" accept="image/*" multiple class="block w-full text-sm text-arcane-muted" required>`);
			if (unref(form).errors.images) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors.images)}</div>`);
			else _push(`<!---->`);
			if (unref(form).errors["images.0"]) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors["images.0"])}</div>`);
			else _push(`<!---->`);
			_push(`</div><div class="outline-root w-full mt-4"><button type="submit" class="btn-primary outline-inner w-full justify-center"${ssrIncludeBooleanAttr(unref(form).processing) ? " disabled" : ""}>`);
			if (unref(form).processing) _push(`<span>Sending…</span>`);
			else _push(`<span>Submit</span>`);
			_push(`</button></div></form></div></div></main></div>`);
		};
	}
});
var Create_exports = /* @__PURE__ */ __exportAll({ default: () => Create_default });
var _sfc_setup$11 = Create_vue_vue_type_script_setup_true_lang_default.setup;
Create_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Sell/Create.vue");
	return _sfc_setup$11 ? _sfc_setup$11(props, ctx) : void 0;
};
var Create_default = Create_vue_vue_type_script_setup_true_lang_default;
var ThankYou_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "ThankYou",
	__ssrInlineRender: true,
	props: { reference: {} },
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen bg-arcane-bg text-arcane-text flex flex-col" }, _attrs))}><header class="border-b border-arcane-border/60 bg-arcane-bg/90 backdrop-blur"><div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between"><a href="/" class="font-display text-xl tracking-[0.3em] text-arcane-accent"> ARCANE </a></div></header><main class="flex-1"><div class="max-w-4xl mx-auto px-6 py-8"><div class="card-panel p-6 text-center space-y-3"><h1 class="font-display text-2xl"> Thanks – we’ve got your submission </h1><p class="text-arcane-muted text-sm"> Your reference is <strong>${ssrInterpolate(__props.reference)}</strong>. </p><p class="text-arcane-muted text-xs max-w-md mx-auto"> We’ll review your photos and get back to you by email with an offer. You don’t need to send anything yet – we’ll confirm the details first. </p></div></div></main></div>`);
		};
	}
});
var ThankYou_exports = /* @__PURE__ */ __exportAll({ default: () => ThankYou_default });
var _sfc_setup$10 = ThankYou_vue_vue_type_script_setup_true_lang_default.setup;
ThankYou_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Sell/ThankYou.vue");
	return _sfc_setup$10 ? _sfc_setup$10(props, ctx) : void 0;
};
var ThankYou_default = ThankYou_vue_vue_type_script_setup_true_lang_default;
var SellerHeader_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "SellerHeader",
	__ssrInlineRender: true,
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<header${ssrRenderAttrs(mergeProps({ class: "sticky top-0 border-b border-arcane-border/60 bg-arcane-bg/90 backdrop-blur z-10" }, _attrs))}><div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/",
				class: "flex items-center gap-2"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<img src="/images/logo.png" alt="Arcane" class="h-10"${_scopeId}>`);
					else return [createVNode("img", {
						src: "/images/logo.png",
						alt: "Arcane",
						class: "h-10"
					})];
				}),
				_: 1
			}, _parent));
			_push(`<nav class="flex items-center gap-4 text-sm text-white/70">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/seller",
				class: "hover:text-white"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Dashboard`);
					else return [createTextVNode("Dashboard")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), {
				href: "/seller/batches",
				class: "hover:text-white"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Batches`);
					else return [createTextVNode("Batches")];
				}),
				_: 1
			}, _parent));
			_push(`<div class="outline-root">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/seller/request-batch",
				class: "btn-primary text-xs outline-inner"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<span${_scopeId}>Buy more</span>`);
					else return [createVNode("span", null, "Buy more")];
				}),
				_: 1
			}, _parent));
			_push(`</div></nav></div></header>`);
		};
	}
});
var _sfc_setup$9 = SellerHeader_vue_vue_type_script_setup_true_lang_default.setup;
SellerHeader_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Layout/SellerHeader.vue");
	return _sfc_setup$9 ? _sfc_setup$9(props, ctx) : void 0;
};
var SellerHeader_default = SellerHeader_vue_vue_type_script_setup_true_lang_default;
var BatchRequest_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "BatchRequest",
	__ssrInlineRender: true,
	props: {
		stores: {},
		products: {}
	},
	setup(__props) {
		const props = __props;
		const form = useForm({
			store_id: props.stores[0]?.id ?? null,
			game: "pokemon",
			type: "ruby",
			notes: ""
		});
		const games = computed(() => {
			const seen = /* @__PURE__ */ new Set();
			return props.products.filter((p) => {
				if ([
					"mtg",
					"lorcana",
					"onepiece"
				].includes(p.game)) return false;
				if (seen.has(p.game)) return false;
				seen.add(p.game);
				return true;
			}).map((p) => ({
				value: p.game,
				label: p.game_label
			}));
		});
		const typesForGame = computed(() => props.products.filter((p) => p.game === form.game));
		const selectedProduct = computed(() => props.products.find((p) => p.game === form.game && p.type === form.type));
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen bg-arcane-bg text-arcane-text" }, _attrs))}>`);
			_push(ssrRenderComponent(SellerHeader_default, null, null, _parent));
			_push(`<main class="max-w-3xl mx-auto px-6 py-8"><div class="card-panel p-6"><h1 class="font-display text-2xl mb-2">Request a new batch</h1><p class="text-arcane-muted text-sm mb-6"> Choose your store, game, and product. We&#39;ll review and dispatch. </p><form class="space-y-4"><div><label class="block text-xs font-medium mb-1">Store</label><select class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white" required><!--[-->`);
			ssrRenderList(__props.stores, (store) => {
				_push(`<option${ssrRenderAttr("value", store.id)}${ssrIncludeBooleanAttr(Array.isArray(unref(form).store_id) ? ssrLooseContain(unref(form).store_id, store.id) : ssrLooseEqual(unref(form).store_id, store.id)) ? " selected" : ""}>${ssrInterpolate(store.name)}</option>`);
			});
			_push(`<!--]--></select></div><div class="grid grid-cols-2 gap-4"><div><label class="block text-xs font-medium mb-1">Game</label><select class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white" required><!--[-->`);
			ssrRenderList(games.value, (g) => {
				_push(`<option${ssrRenderAttr("value", g.value)}${ssrIncludeBooleanAttr(Array.isArray(unref(form).game) ? ssrLooseContain(unref(form).game, g.value) : ssrLooseEqual(unref(form).game, g.value)) ? " selected" : ""}>${ssrInterpolate(g.label)}</option>`);
			});
			_push(`<!--]--></select></div><div><label class="block text-xs font-medium mb-1">Product</label><select class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white" required><!--[-->`);
			ssrRenderList(typesForGame.value, (p) => {
				_push(`<option${ssrRenderAttr("value", p.type)}${ssrIncludeBooleanAttr(Array.isArray(unref(form).type) ? ssrLooseContain(unref(form).type, p.type) : ssrLooseEqual(unref(form).type, p.type)) ? " selected" : ""}>${ssrInterpolate(p.type_label)} — ${ssrInterpolate(p.packs)} packs, £${ssrInterpolate(p.price_pounds.toFixed(2))}</option>`);
			});
			_push(`<!--]--></select></div></div>`);
			if (selectedProduct.value) _push(`<div class="card-panel p-3 bg-arcane-elevated text-xs text-arcane-muted"><strong class="text-arcane-text">${ssrInterpolate(selectedProduct.value.type_label)}</strong> — ${ssrInterpolate(selectedProduct.value.packs)} sealed mystery packs, invoiced at <strong class="text-arcane-text">£${ssrInterpolate(selectedProduct.value.price_pounds.toFixed(2))}</strong> ex VAT. </div>`);
			else _push(`<!---->`);
			_push(`<div><label class="block text-xs font-medium mb-1">Notes (optional)</label><textarea rows="3" class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white" placeholder="Anything we should know about delivery, timing, etc.">${ssrInterpolate(unref(form).notes)}</textarea></div><div class="outline-root w-full"><button type="submit" class="btn-primary w-full justify-center outline-inner"${ssrIncludeBooleanAttr(unref(form).processing) ? " disabled" : ""}>`);
			if (unref(form).processing) _push(`<span>Submitting…</span>`);
			else _push(`<span>Submit</span>`);
			_push(`</button></div></form></div></main></div>`);
		};
	}
});
var BatchRequest_exports = /* @__PURE__ */ __exportAll({ default: () => BatchRequest_default });
var _sfc_setup$8 = BatchRequest_vue_vue_type_script_setup_true_lang_default.setup;
BatchRequest_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/BatchRequest.vue");
	return _sfc_setup$8 ? _sfc_setup$8(props, ctx) : void 0;
};
var BatchRequest_default = BatchRequest_vue_vue_type_script_setup_true_lang_default;
var BatchShow_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "BatchShow",
	__ssrInlineRender: true,
	props: {
		batch: {},
		packs: {}
	},
	setup(__props) {
		const statusLabel = (status) => {
			switch (status) {
				case "draft": return "Draft";
				case "committed": return "Live";
				case "dispatched": return "Dispatched";
				case "completed": return "Completed";
				case "cancelled": return "Cancelled";
				default: return status;
			}
		};
		const bandLabel = (band) => {
			switch (band) {
				case "common": return "Common";
				case "rare": return "Rare";
				case "super": return "Super";
				case "legendary": return "Legendary";
				case "mythic": return "Mythic";
				default: return band ?? "—";
			}
		};
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen bg-arcane-bg text-arcane-text" }, _attrs))}>`);
			_push(ssrRenderComponent(SellerHeader_default, null, null, _parent));
			_push(`<main class="max-w-6xl mx-auto px-6 py-8 space-y-6"><section class="card-panel p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4"><div><h1 class="font-display text-2xl mb-1">${ssrInterpolate(__props.batch.reference)}</h1><p class="text-arcane-muted text-sm">${ssrInterpolate(__props.batch.store.name)} · ${ssrInterpolate((__props.batch.type ?? "").toUpperCase())} · ${ssrInterpolate(__props.batch.pack_count)} packs </p><p class="text-arcane-muted text-xs mt-1"> Status: ${ssrInterpolate(statusLabel(__props.batch.status))}</p></div></section><section class="card-panel p-4 overflow-x-auto"><h2 class="text-lg font-semibold mb-3">Packs</h2><table class="min-w-full text-sm"><thead class="text-arcane-muted border-b border-arcane-border/60"><tr class="text-left"><th class="py-2 pr-4">#</th><th class="py-2 pr-4">Card</th><th class="py-2 pr-4">Set</th><th class="py-2 pr-4">Band</th><th class="py-2 pr-4">Status</th></tr></thead><tbody>`);
			if (__props.packs.length === 0) _push(`<tr><td colspan="5" class="py-4 text-arcane-muted text-sm"> No packs found for this batch. </td></tr>`);
			else _push(`<!---->`);
			_push(`<!--[-->`);
			ssrRenderList(__props.packs, (pack) => {
				_push(`<tr class="border-b border-arcane-border/40"><td class="py-2 pr-4"> #${ssrInterpolate(pack.sequence)}</td><td class="py-2 pr-4">${ssrInterpolate(pack.card?.name ?? "—")}</td><td class="py-2 pr-4 text-arcane-muted text-xs">${ssrInterpolate(pack.card?.set ?? "")} `);
				if (pack.card?.number) _push(`<span>· ${ssrInterpolate(pack.card.number)}</span>`);
				else _push(`<!---->`);
				_push(`</td><td class="py-2 pr-4 text-xs"><span class="rarity-pill bg-arcane-border/40 text-arcane-muted">${ssrInterpolate(bandLabel(pack.card?.band ?? null))}</span></td><td class="py-2 pr-4 text-xs"><span class="rarity-pill bg-arcane-border/40 text-arcane-muted">${ssrInterpolate(statusLabel(pack.status))}</span></td></tr>`);
			});
			_push(`<!--]--></tbody></table></section></main></div>`);
		};
	}
});
var BatchShow_exports = /* @__PURE__ */ __exportAll({ default: () => BatchShow_default });
var _sfc_setup$7 = BatchShow_vue_vue_type_script_setup_true_lang_default.setup;
BatchShow_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/BatchShow.vue");
	return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
var BatchShow_default = BatchShow_vue_vue_type_script_setup_true_lang_default;
var BatchesIndex_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "BatchesIndex",
	__ssrInlineRender: true,
	props: {
		batches: {},
		storesById: {}
	},
	setup(__props) {
		const statusLabel = (status) => {
			switch (status) {
				case "draft": return "Draft";
				case "committed": return "Live";
				case "dispatched": return "Dispatched";
				case "completed": return "Completed";
				case "cancelled": return "Cancelled";
				default: return status;
			}
		};
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen bg-arcane-bg text-arcane-text" }, _attrs))}>`);
			_push(ssrRenderComponent(SellerHeader_default, null, null, _parent));
			_push(`<main class="max-w-6xl mx-auto px-6 py-8 space-y-6"><section><h1 class="font-display text-3xl mb-2">Batches</h1><p class="text-arcane-muted text-sm"> All Arcane mystery pack batches allocated to your store(s). </p></section><section class="card-panel p-4 overflow-x-auto"><table class="min-w-full text-sm"><thead class="text-arcane-muted border-b border-arcane-border/60"><tr class="text-left"><th class="py-2 pr-4">Reference</th><th class="py-2 pr-4">Store</th><th class="py-2 pr-4">Product</th><th class="py-2 pr-4 text-right">Packs</th><th class="py-2 pr-4">Status</th><th class="py-2"></th></tr></thead><tbody>`);
			if (__props.batches.data.length === 0) _push(`<tr><td colspan="9" class="py-4 text-arcane-muted text-sm"> No batches yet. </td></tr>`);
			else _push(`<!---->`);
			_push(`<!--[-->`);
			ssrRenderList(__props.batches.data, (batch) => {
				_push(`<tr class="border-b border-arcane-border/40"><td class="py-2 pr-4">${ssrInterpolate(batch.reference)}</td><td class="py-2 pr-4">${ssrInterpolate(__props.storesById[batch.store_id]?.name ?? "Store")}</td><td class="py-2 pr-4 uppercase text-xs text-arcane-muted">${ssrInterpolate(batch.type ?? "")}</td><td class="py-2 pr-4 text-right">${ssrInterpolate(batch.pack_count)}</td><td class="py-2 pr-4 text-xs"><span class="rarity-pill bg-arcane-border/40 text-arcane-muted">${ssrInterpolate(statusLabel(batch.status))}</span></td><td class="py-2 text-right">`);
				_push(ssrRenderComponent(unref(Link), {
					href: `/seller/batches/${batch.id}`,
					class: "btn-ghost text-xs"
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(` View `);
						else return [createTextVNode(" View ")];
					}),
					_: 2
				}, _parent));
				_push(`</td></tr>`);
			});
			_push(`<!--]--></tbody></table><div class="mt-4 flex justify-end gap-1 text-xs"><!--[-->`);
			ssrRenderList(__props.batches.links, (link) => {
				_push(`<!--[-->`);
				if (link?.url) _push(ssrRenderComponent(unref(Link), {
					href: link.url,
					class: ["px-2 py-1 rounded border border-arcane-border/60", link.active ? "bg-arcane-accent text-arcane-bg" : "text-arcane-muted hover:bg-arcane-elevated"]
				}, null, _parent));
				else _push(`<!---->`);
				_push(`<!--]-->`);
			});
			_push(`<!--]--></div></section></main></div>`);
		};
	}
});
var BatchesIndex_exports = /* @__PURE__ */ __exportAll({ default: () => BatchesIndex_default });
var _sfc_setup$6 = BatchesIndex_vue_vue_type_script_setup_true_lang_default.setup;
BatchesIndex_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/BatchesIndex.vue");
	return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
var BatchesIndex_default = BatchesIndex_vue_vue_type_script_setup_true_lang_default;
var Dashboard_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Dashboard",
	__ssrInlineRender: true,
	props: {
		stores: {},
		batches: {},
		progress: {}
	},
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen bg-arcane-bg text-arcane-text" }, _attrs))}>`);
			_push(ssrRenderComponent(SellerHeader_default, null, null, _parent));
			_push(`<main class="max-w-6xl mx-auto px-6 py-8 space-y-8"><section><h1 class="font-display text-3xl mb-2"> Seller dashboard </h1><p class="text-arcane-muted text-sm"> Overview of your Arcane mystery card inventory. </p></section><section class="grid md:grid-cols-3 gap-4"><div class="card-panel p-4"><h2 class="text-sm text-arcane-muted mb-1">Stores</h2><p class="text-2xl font-semibold">${ssrInterpolate(__props.stores.length)}</p></div><div class="card-panel p-4"><h2 class="text-sm text-arcane-muted mb-1">Active batches</h2><p class="text-2xl font-semibold">${ssrInterpolate(__props.batches.length)}</p></div><div class="card-panel p-4"><h2 class="text-sm text-arcane-muted mb-1">Total packs</h2><p class="text-2xl font-semibold">${ssrInterpolate(__props.batches.reduce((sum, b) => sum + b.pack_count, 0))}</p></div></section><section><div class="flex items-center justify-between mb-3"><h2 class="text-lg font-semibold">Recent batches</h2>`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/seller/batches",
				class: "text-xs text-arcane-muted hover:text-arcane-accent"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(` View all `);
					else return [createTextVNode(" View all ")];
				}),
				_: 1
			}, _parent));
			_push(`</div>`);
			if (__props.batches.length === 0) _push(`<div class="text-arcane-muted text-sm"> No batches yet. Once your first Arcane shipment is generated it will appear here. </div>`);
			else {
				_push(`<div class="space-y-2"><!--[-->`);
				ssrRenderList(__props.batches, (batch) => {
					_push(`<div class="card-panel p-4 flex items-center justify-between gap-4"><div class="flex-1"><div class="text-sm font-semibold">${ssrInterpolate(batch.reference)}</div><div class="text-xs text-arcane-muted">${ssrInterpolate(__props.stores.find((s) => s.id === batch.store_id)?.name ?? "Store")} · ${ssrInterpolate((batch.type ?? "").toUpperCase())} · ${ssrInterpolate(batch.pack_count)} packs </div></div><div class="text-right text-xs text-arcane-muted"><div> Sold: <span class="text-arcane-text font-semibold">${ssrInterpolate(__props.progress[batch.id]?.sold ?? 0)} / ${ssrInterpolate(__props.progress[batch.id]?.total ?? batch.pack_count)}</span></div></div><div>`);
					_push(ssrRenderComponent(unref(Link), {
						href: `/seller/batches/${batch.id}`,
						class: "btn-ghost text-xs"
					}, {
						default: withCtx((_, _push, _parent, _scopeId) => {
							if (_push) _push(` View `);
							else return [createTextVNode(" View ")];
						}),
						_: 2
					}, _parent));
					_push(`</div></div>`);
				});
				_push(`<!--]--></div>`);
			}
			_push(`</section></main></div>`);
		};
	}
});
var Dashboard_exports = /* @__PURE__ */ __exportAll({ default: () => Dashboard_default });
var _sfc_setup$5 = Dashboard_vue_vue_type_script_setup_true_lang_default.setup;
Dashboard_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/Dashboard.vue");
	return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
var Dashboard_default = Dashboard_vue_vue_type_script_setup_true_lang_default;
var BatchListShow_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "BatchListShow",
	__ssrInlineRender: true,
	props: {
		store: {},
		batch: {},
		bands: {}
	},
	setup(__props) {
		const props = __props;
		const bands = ref(props.bands);
		const odds = {};
		Object.entries(bands.value).forEach(([band, info]) => {
			odds[band] = info.count;
		});
		const totalOdds = computed(() => Object.values(odds).reduce((sum, x) => sum + x, 0));
		const bandOrder = [
			{
				key: "mythic",
				label: "Mythic"
			},
			{
				key: "legendary",
				label: "Legendary"
			},
			{
				key: "super",
				label: "Super"
			},
			{
				key: "rare",
				label: "Rare"
			},
			{
				key: "common",
				label: "Common"
			}
		];
		const pillClass = (band) => {
			if (!band) return "bg-arcane-border text-arcane-muted";
			return {
				common: "bg-arcane-common/20 text-arcane-common",
				rare: "bg-arcane-rare/20 text-arcane-rare",
				super: "bg-arcane-super/20 text-arcane-super",
				legendary: "bg-arcane-legendary/20 text-arcane-legendary",
				mythic: "bg-arcane-mythic/20 text-arcane-mythic"
			}[band];
		};
		const oddsBarClass = (band) => {
			return {
				common: "bg-arcane-common/40",
				rare: "bg-arcane-rare/40",
				super: "bg-arcane-super/40",
				legendary: "bg-arcane-legendary/40",
				mythic: "bg-arcane-mythic/40"
			}[band];
		};
		let channel = null;
		onMounted(() => {
			if (!window.Echo) return;
			channel = window.Echo.channel(`store.${props.store.id}`).listen(".PackSold", (payload) => {
				const band = payload.band;
				if (!band) return;
				const current = bands.value[band];
				if (!current) return;
				const newCount = Math.max(0, current.count - 1);
				bands.value = {
					...bands.value,
					[band]: {
						...current,
						count: newCount
					}
				};
			});
		});
		onBeforeUnmount(() => {
			if (channel && window.Echo) window.Echo.leaveChannel(`store.${props.store.id}`);
		});
		const imageLoading = (band) => band === "mythic" || band === "legendary" ? "eager" : "lazy";
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen flex flex-col" }, _attrs))}>`);
			_push(ssrRenderComponent(Header_default, null, null, _parent));
			_push(`<main class="flex-1 bg-arcane-bg"><div class="max-w-6xl mx-auto px-6 py-8 space-y-8"><section class="card-panel p-4 md:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4"><div><h1 class="font-display text-2xl mb-1"> Card list – ${ssrInterpolate(__props.batch.reference)}</h1><p class="text-arcane-muted text-sm">${ssrInterpolate(__props.store.name)} · <span class="uppercase">${ssrInterpolate(__props.batch.type ?? "")}</span>`);
			if (__props.batch.game_label) _push(`<span> · ${ssrInterpolate(__props.batch.game_label)}</span>`);
			else _push(`<!---->`);
			_push(`</p><p class="text-arcane-muted text-xs mt-2"> Showing cards still in sealed Arcane packs for this batch. </p></div><div class="text-xs text-arcane-muted md:text-right"><p><span class="text-arcane-text font-semibold">${ssrInterpolate(Object.entries(bands.value).reduce((acc, [, info]) => acc + info.count, 0))}/${ssrInterpolate(__props.batch.pack_count)}</span> packs</p></div></section><section class="card-panel p-4 md:p-5"><div class="mt-3 space-y-2"><div class="flex items-center justify-between text-[11px] text-arcane-muted"><span>Hit odds by rarity (approx.)</span></div><div class="flex h-2 rounded-full overflow-hidden border border-arcane-border/60 bg-arcane-surface/70"><!--[-->`);
			ssrRenderList(bandOrder, (band) => {
				_push(`<div class="${ssrRenderClass(oddsBarClass(band.key))}" style="${ssrRenderStyle({ width: (odds[band.key] / totalOdds.value * 100).toFixed(1) + "%" })}"></div>`);
			});
			_push(`<!--]--></div><div class="flex flex-wrap gap-2 mt-1 text-[10px] text-arcane-muted"><!--[-->`);
			ssrRenderList(bandOrder, (band) => {
				_push(`<div class="flex items-center gap-1"><span class="${ssrRenderClass([oddsBarClass(band.key), "inline-flex w-3 h-1 rounded-full"])}"></span><span>${ssrInterpolate(band.label)}: ~${ssrInterpolate((odds[band.key] / totalOdds.value * 100).toFixed(1))}%</span></div>`);
			});
			_push(`<!--]--></div></div></section><section class="space-y-6"><!--[-->`);
			ssrRenderList(bandOrder, (band) => {
				_push(`<div class="card-panel p-4 md:p-5"><div class="flex items-center justify-between mb-3"><div class="flex items-center gap-2"><span class="${ssrRenderClass([pillClass(band.key), "rarity-pill"])}">${ssrInterpolate(band.label)}</span></div><span class="text-sm text-arcane-muted">${ssrInterpolate(bands.value[band.key]?.count ?? 0)} remaining </span></div>`);
				if ((bands.value[band.key]?.count ?? 0) === 0) _push(`<div class="text-xs text-arcane-muted"> No cards of this band are currently left in sealed Arcane packs for this batch. </div>`);
				else {
					_push(`<div><div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"><!--[-->`);
					ssrRenderList(bands.value[band.key].cards, (card) => {
						_push(`<div class="flex flex-col items-center text-center gap-1"><div class="w-full max-w-[130px] aspect-[245/342] rounded-lg overflow-hidden border border-arcane-border/60 bg-arcane-surface/80 flex items-center justify-center">`);
						if (card.image) _push(`<img${ssrRenderAttr("src", card.image)} alt="" class="w-full h-full object-cover transition"${ssrRenderAttr("loading", imageLoading(band.key))}>`);
						else _push(`<div class="text-[10px] text-arcane-muted px-2"> Image not available </div>`);
						_push(`</div><div class="w-full max-w-[130px]"><div class="text-[11px] font-semibold truncate">${ssrInterpolate(card.name)}</div><div class="text-[10px] text-arcane-muted truncate">${ssrInterpolate(card.set)} · ${ssrInterpolate(card.number)}</div></div></div>`);
					});
					_push(`<!--]--></div>`);
					if ((bands.value[band.key]?.count ?? 0) > bands.value[band.key].cards.length) _push(`<p class="text-[11px] text-arcane-muted mt-2"> Showing ${ssrInterpolate(bands.value[band.key].cards.length)} of ${ssrInterpolate(bands.value[band.key].count)} remaining ${ssrInterpolate(band.label.toLowerCase())}. </p>`);
					else _push(`<!---->`);
					_push(`</div>`);
				}
				_push(`</div>`);
			});
			_push(`<!--]--></section></div></main></div>`);
		};
	}
});
var BatchListShow_exports = /* @__PURE__ */ __exportAll({ default: () => BatchListShow_default });
var _sfc_setup$4 = BatchListShow_vue_vue_type_script_setup_true_lang_default.setup;
BatchListShow_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Storefront/BatchListShow.vue");
	return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
var BatchListShow_default = BatchListShow_vue_vue_type_script_setup_true_lang_default;
var Store_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Store",
	__ssrInlineRender: true,
	props: { store: {} },
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "outline-root relativ" }, _attrs))}><div class="card-panel flex items-center gap-5 outline-inner py-5"><span></span><div class="w-24 h-24 rounded-lg overflow-hidden border border-arcane-border/60 bg-arcane-surface/80 flex items-center p-2">`);
			if (__props.store.logo) _push(`<img${ssrRenderAttr("src", __props.store.logo)} alt="" class="h-full w-full object-contain" loading="lazy">`);
			else _push(`<!--[--><!--]-->`);
			_push(`</div><div class="w-full"><div class="text-sm font-semibold truncate">${ssrInterpolate(__props.store.name)}</div><div class="text-xs text-arcane-muted truncate mt-2"><span class="text-arcane-accent2 hover:underline"> Visit store </span></div></div></div>`);
			_push(ssrRenderComponent(unref(Link), {
				href: `/${__props.store.slug}`,
				class: "absolute inset-0"
			}, null, _parent));
			_push(`</div>`);
		};
	}
});
var _sfc_setup$3 = Store_vue_vue_type_script_setup_true_lang_default.setup;
Store_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Cards/Store.vue");
	return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
var Store_default = Store_vue_vue_type_script_setup_true_lang_default;
var StoreIndex_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "StoreIndex",
	__ssrInlineRender: true,
	props: { stores: {} },
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen flex flex-col" }, _attrs))}>`);
			_push(ssrRenderComponent(Header_default, null, null, _parent));
			_push(`<main class="flex-1"><div class="max-w-6xl mx-auto px-6 py-10 space-y-6"><section class="flex flex-col md:flex-row md:items-end md:justify-between gap-3"><div><h1 class="font-display text-3xl mb-2"> Arcane partner stores </h1><p class="text-arcane-muted text-sm max-w-xl"> These shops stock Arcane single-card mystery packs. Tap a store to view the live card lists and recent pulls. </p></div></section><section>`);
			if (__props.stores.length === 0) _push(`<div class="text-arcane-muted text-sm"> No stores are live yet. </div>`);
			else {
				_push(`<div class="grid gap-4 md:grid-cols-2"><!--[-->`);
				ssrRenderList(__props.stores, (store) => {
					_push(ssrRenderComponent(Store_default, {
						key: store.id,
						store
					}, null, _parent));
				});
				_push(`<!--]--></div>`);
			}
			_push(`</section></div></main></div>`);
		};
	}
});
var StoreIndex_exports = /* @__PURE__ */ __exportAll({ default: () => StoreIndex_default });
var _sfc_setup$2 = StoreIndex_vue_vue_type_script_setup_true_lang_default.setup;
StoreIndex_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Storefront/StoreIndex.vue");
	return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
var StoreIndex_default = StoreIndex_vue_vue_type_script_setup_true_lang_default;
var StoreShow_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "StoreShow",
	__ssrInlineRender: true,
	props: {
		store: {},
		batches: {},
		recentPulls: {}
	},
	setup(__props) {
		const bandPillClass = (band) => {
			if (!band) return "bg-arcane-border text-arcane-muted";
			return {
				common: "bg-arcane-common/20 text-arcane-common",
				rare: "bg-arcane-rare/20 text-arcane-rare",
				super: "bg-arcane-super/20 text-arcane-super",
				legendary: "bg-arcane-legendary/20 text-arcane-legendary",
				mythic: "bg-arcane-mythic/20 text-arcane-mythic"
			}[band];
		};
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen flex flex-col" }, _attrs))}>`);
			_push(ssrRenderComponent(Header_default, null, null, _parent));
			_push(`<main class="flex-1 bg-arcane-bg"><div class="max-w-6xl mx-auto px-6 py-8 space-y-8"><section class="card-panel p-4 md:p-5"><div class="flex items-center justify-between mb-3"><h2 class="text-sm font-semibold">Recent pulls</h2><span class="text-[11px] text-arcane-muted"> Latest cards pulled from Arcane packs at this store. </span></div>`);
			if (__props.recentPulls.length === 0) _push(`<div class="text-xs text-arcane-muted"> No recent pulls yet. Come back after a few packs have been opened. </div>`);
			else {
				_push(`<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"><!--[-->`);
				ssrRenderList(__props.recentPulls, (pull) => {
					_push(`<div class="flex flex-col items-center text-center gap-1"><div class="w-full max-w-[130px] aspect-[245/342] rounded-lg overflow-hidden border border-arcane-border/60 bg-arcane-surface/80 flex items-center justify-center opacity-60">`);
					if (pull.card?.image) _push(`<img${ssrRenderAttr("src", pull.card.image)} alt="" class="w-full h-full object-cover" loading="lazy">`);
					else _push(`<div class="text-[10px] text-arcane-muted px-2"> Image not available </div>`);
					_push(`</div><div class="w-full max-w-[130px]"><div class="text-[11px] font-semibold truncate">${ssrInterpolate(pull.card?.name ?? "Unknown")}</div><div class="text-[10px] text-arcane-muted truncate">${ssrInterpolate(pull.card?.set)} · ${ssrInterpolate(pull.card?.number)}</div><div class="mt-1 flex items-center justify-center gap-1 text-[10px] text-arcane-muted"><span class="${ssrRenderClass([bandPillClass(pull.card?.band ?? null), "rarity-pill"])}">${ssrInterpolate(pull.card?.band ?? "")}</span></div></div></div>`);
				});
				_push(`<!--]--></div>`);
			}
			_push(`</section><section class="card-panel p-4 md:p-5"><div class="flex items-center justify-between mb-3"><h2 class="text-sm font-semibold">Card lists</h2><span class="text-[11px] text-arcane-muted"> Tap a list to view remaining hits for that batch. </span></div>`);
			if (__props.batches.length === 0) _push(`<div class="text-xs text-arcane-muted"> No Arcane batches are currently live at this store. </div>`);
			else {
				_push(`<div class="space-y-2"><!--[-->`);
				ssrRenderList(__props.batches, (batch) => {
					_push(ssrRenderComponent(unref(Link), {
						key: batch.id,
						href: `/store/${__props.store.slug}/batch/${batch.id}`,
						class: "flex items-center justify-between gap-3 border border-arcane-border/60 rounded-lg px-3 py-2.5 bg-arcane-surface/80 hover:border-arcane-accent/60 transition"
					}, {
						default: withCtx((_, _push, _parent, _scopeId) => {
							if (_push) {
								_push(`<div class="space-y-2"${_scopeId}><div class="text-sm font-semibold"${_scopeId}>${ssrInterpolate(batch.reference)}</div><div class="flex items-center gap-2"${_scopeId}>`);
								if (batch.game_label) _push(`<div${_scopeId}><span class="px-2 py-[2px] rounded-full text-[10px] bg-arcane-elevated text-arcane-muted border border-arcane-border/70"${_scopeId}>${ssrInterpolate(batch.game_label)}</span></div>`);
								else _push(`<!---->`);
								_push(`<div class="text-xs text-arcane-muted"${_scopeId}>${ssrInterpolate((batch.type ?? "").toUpperCase())} · ${ssrInterpolate(batch.pack_count)} packs </div></div></div><div class="text-[11px] text-arcane-muted"${_scopeId}> View card list → </div>`);
							} else return [createVNode("div", { class: "space-y-2" }, [createVNode("div", { class: "text-sm font-semibold" }, toDisplayString(batch.reference), 1), createVNode("div", { class: "flex items-center gap-2" }, [batch.game_label ? (openBlock(), createBlock("div", { key: 0 }, [createVNode("span", { class: "px-2 py-[2px] rounded-full text-[10px] bg-arcane-elevated text-arcane-muted border border-arcane-border/70" }, toDisplayString(batch.game_label), 1)])) : createCommentVNode("", true), createVNode("div", { class: "text-xs text-arcane-muted" }, toDisplayString((batch.type ?? "").toUpperCase()) + " · " + toDisplayString(batch.pack_count) + " packs ", 1)])]), createVNode("div", { class: "text-[11px] text-arcane-muted" }, " View card list → ")];
						}),
						_: 2
					}, _parent));
				});
				_push(`<!--]--></div>`);
			}
			_push(`</section></div></main></div>`);
		};
	}
});
var StoreShow_exports = /* @__PURE__ */ __exportAll({ default: () => StoreShow_default });
var _sfc_setup$1 = StoreShow_vue_vue_type_script_setup_true_lang_default.setup;
StoreShow_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Storefront/StoreShow.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var StoreShow_default = StoreShow_vue_vue_type_script_setup_true_lang_default;
var Welcome_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Welcome",
	__ssrInlineRender: true,
	props: {
		recentPulls: {},
		newStores: {}
	},
	setup(__props) {
		const products = [
			{
				key: "sapphire",
				name: "Sapphire",
				packs: 100,
				pricePerPack: "£9.00"
			},
			{
				key: "ruby",
				name: "Ruby",
				packs: 250,
				pricePerPack: "£8.50"
			},
			{
				key: "diamond",
				name: "Diamond",
				packs: 500,
				pricePerPack: "£8.00"
			}
		];
		const bandPillClass = (band) => {
			if (!band) return "bg-arcane-border text-arcane-muted";
			return {
				common: "bg-arcane-common/20 text-arcane-common",
				rare: "bg-arcane-rare/20 text-arcane-rare",
				super: "bg-arcane-super/20 text-arcane-super",
				legendary: "bg-arcane-legendary/20 text-arcane-legendary",
				mythic: "bg-arcane-mythic/20 text-arcane-mythic"
			}[band];
		};
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen flex flex-col" }, _attrs))}>`);
			_push(ssrRenderComponent(Header_default, null, null, _parent));
			_push(`<main class="flex-1"><div class="futuristic-grid"><section class="border-b border-arcane-border/60 bg-gradient-to-b from-arcane-bg/90 to-arcane-surface/80"><div class="max-w-6xl mx-auto px-6 py-16 md:py-20 grid md:grid-cols-2 gap-10 items-center"><div><div class="outline-root mb-3"><p class="text-arcane-accent2 uppercase tracking-[0.3em] text-xs outline-inner secondary"> Mystery singles packs for card shops </p></div><h1 class="font-display text-4xl md:text-5xl leading-tight mb-4"><span>One card.</span><br><span>Infinite chase.</span></h1><p class="text-arcane-muted text-base md:text-lg mb-6 max-w-md"> Arcane turns authenticated, near-mint Pokémon singles into sealed mystery packs—each with a single toploaded hit. Live card lists show what’s still in the pool at your local shop. </p><div class="flex flex-wrap gap-3"><span class="outline-root">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/stores",
				class: "btn-primary outline-inner"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<span${_scopeId}>Browse participating stores</span>`);
					else return [createVNode("span", null, "Browse participating stores")];
				}),
				_: 1
			}, _parent));
			_push(`</span>`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/apply",
				class: "btn-ghost"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(` Bring Arcane to your shop `);
					else return [createTextVNode(" Bring Arcane to your shop ")];
				}),
				_: 1
			}, _parent));
			_push(`</div></div><div class="outline-root"><div class="card-panel outline-inner"><span></span><div class="p-5 md:p-6"><div class="outline-root absolute left-1/2 transform -translate-x-1/2 top-20"><h2 class="text-arcane-accent2 uppercase tracking-[0.3em] text-sm outline-inner secondary text-nowrap"> The Arcane Mystery Singles Pack! </h2></div><img src="/images/pack.png" alt="Arcane pack" class="w-full h-auto"><div class="relative"><div class="grid grid-cols-1 lg:grid-cols-3 gap-3 -mt-12"><!--[-->`);
			ssrRenderList(products, (product) => {
				_push(`<div class="flex items-center justify-between gap-3 border border-arcane-border/60 rounded-lg px-3 py-2.5 bg-arcane-surface/80"><div><div class="${ssrRenderClass(["text-sm font-semibold", "text-" + product.name.toLowerCase()])}">${ssrInterpolate(product.name)} (x${ssrInterpolate(product.packs)}) </div></div><div class="text-right text-xs"><div class="font-semibold">${ssrInterpolate(product.pricePerPack)} <span class="text-arcane-muted">/ pack</span></div></div></div>`);
			});
			_push(`<!--]--></div><p class="text-arcane-muted text-[11px] mt-6"> Packs are built using live market data and a defined rarity structure (Common, Rare, Super, Legendary, Mythic) so batches stay consistent and fair. </p></div></div></div></div></div></section></div><section class="max-w-6xl mx-auto px-6 py-12 md:py-16 space-y-8"><div class="grid md:grid-cols-3 gap-6"><div class="card-panel p-4"><h3 class="text-sm font-semibold mb-2">1. We source &amp; authenticate</h3><p class="text-xs text-arcane-muted"> We buy Pokémon singles from trusted sellers and shows, scan them into our library, and assign live market values. </p></div><div class="card-panel p-4"><h3 class="text-sm font-semibold mb-2">2. Batches are generated</h3><p class="text-xs text-arcane-muted"> Our engine builds Sapphire, Ruby, or Diamond batches with a defined mix of commons through mythics with awesome chase cards. </p></div><div class="card-panel p-4"><h3 class="text-sm font-semibold mb-2">3. Live card lists</h3><p class="text-xs text-arcane-muted"> Each store has a live card list showing which hits are still in unopened Arcane packs. QR codes delist cards the moment a pack is sold. </p></div></div></section><div class="futuristic-grid"><section class="border-b border-arcane-border/60 bg-gradient-to-b from-arcane-bg/90 to-arcane-surface/80"><div class="max-w-6xl mx-auto px-6 py-16 md:py-20"><h2 class="font-display text-4xl md:text-5xl leading-tight mb-4"> Recent pulls </h2><div class="grid grid-cols-1 md:grid-cols-5 gap-5 mt-10"><!--[-->`);
			ssrRenderList(__props.recentPulls, (pull) => {
				_push(`<div class="flex flex-col items-center text-center gap-1"><div class="w-full max-w-[130px] aspect-[245/342] rounded-lg overflow-hidden border border-arcane-border/60 bg-arcane-surface/80 flex items-center justify-center opacity-60">`);
				if (pull.card?.image) _push(`<img${ssrRenderAttr("src", pull.card.image)} alt="" class="w-full h-full object-cover" loading="lazy">`);
				else _push(`<div class="text-[10px] text-arcane-muted px-2"> Image not available </div>`);
				_push(`</div><div class="w-full max-w-[130px]"><div class="text-[11px] font-semibold truncate">${ssrInterpolate(pull.card?.name ?? "Unknown")}</div><div class="text-[10px] text-arcane-muted truncate">${ssrInterpolate(pull.card?.set)} · ${ssrInterpolate(pull.card?.number)}</div><div class="mt-1 flex items-center justify-center gap-1 text-[10px] text-arcane-muted"><span class="${ssrRenderClass([bandPillClass(pull.card?.band ?? null), "rarity-pill"])}">${ssrInterpolate(pull.card?.band ?? "")}</span></div></div></div>`);
			});
			_push(`<!--]--></div></div></section></div><section class="max-w-6xl mx-auto px-6 py-12 md:py-16 space-y-8"><h2 class="font-display text-4xl md:text-5xl leading-tight mb-4"> New stores </h2><div class="mt-10 grid grid-cols-1 lg:grid-cols-3 gap-5"><!--[-->`);
			ssrRenderList(__props.newStores, (store) => {
				_push(ssrRenderComponent(Store_default, {
					key: store.id,
					store
				}, null, _parent));
			});
			_push(`<!--]--></div></section></main></div>`);
		};
	}
});
var Welcome_exports = /* @__PURE__ */ __exportAll({ default: () => Welcome_default });
var _sfc_setup = Welcome_vue_vue_type_script_setup_true_lang_default.setup;
Welcome_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Welcome.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Welcome_default = Welcome_vue_vue_type_script_setup_true_lang_default;
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.Pusher = Pusher;
window.Echo = new Echo({
	broadcaster: "reverb",
	key: "q0pfwf9neqqudsyexwqy",
	wsHost: "localhost",
	wsPort: 8080,
	wssPort: 8080,
	forceTLS: false,
	enabledTransports: ["ws", "wss"]
});
var render = await createInertiaApp({
	title: (title) => title ? `${title} · Arcane` : "Arcane",
	resolve: (name) => {
		const page = (/* @__PURE__ */ Object.assign({
			"./Pages/Auth/Login.vue": Login_exports,
			"./Pages/Sell/Create.vue": Create_exports,
			"./Pages/Sell/ThankYou.vue": ThankYou_exports,
			"./Pages/Seller/BatchRequest.vue": BatchRequest_exports,
			"./Pages/Seller/BatchShow.vue": BatchShow_exports,
			"./Pages/Seller/BatchesIndex.vue": BatchesIndex_exports,
			"./Pages/Seller/Dashboard.vue": Dashboard_exports,
			"./Pages/Storefront/BatchListShow.vue": BatchListShow_exports,
			"./Pages/Storefront/StoreIndex.vue": StoreIndex_exports,
			"./Pages/Storefront/StoreShow.vue": StoreShow_exports,
			"./Pages/Welcome.vue": Welcome_exports
		}))[`./Pages/${name}.vue`];
		if (!page) throw new Error(`Inertia page not found: ${name}`);
		return page;
	},
	setup({ el, App, props, plugin }) {
		createApp({ render: () => h(App, props) }).use(plugin).use(ZiggyVue).component("Link", Link).mount(el);
	},
	progress: { color: "#a78bfa" }
});
var renderPage = (page) => render(page, renderToString);
createServer(renderPage, {
	"port": 13714,
	"host": "127.0.0.1"
});
//#endregion
export { renderPage as default };

//# sourceMappingURL=app2.js.map