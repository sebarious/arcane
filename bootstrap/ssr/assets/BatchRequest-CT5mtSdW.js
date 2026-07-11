import { t as SellerHeader_default } from "./SellerHeader-BH0x5cAj.js";
import { useForm } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrLooseContain, ssrLooseEqual, ssrRenderAttr, ssrRenderAttrs, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
import { computed, defineComponent, mergeProps, unref, useSSRContext } from "vue";
//#region resources/ts/Pages/Seller/BatchRequest.vue?vue&type=script&setup=true&lang.ts
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
//#endregion
//#region resources/ts/Pages/Seller/BatchRequest.vue
var _sfc_setup = BatchRequest_vue_vue_type_script_setup_true_lang_default.setup;
BatchRequest_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/BatchRequest.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var BatchRequest_default = BatchRequest_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { BatchRequest_default as default };

//# sourceMappingURL=BatchRequest-CT5mtSdW.js.map