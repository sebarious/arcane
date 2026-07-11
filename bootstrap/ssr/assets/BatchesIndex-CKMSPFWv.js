import { t as SellerHeader_default } from "./SellerHeader-BH0x5cAj.js";
import { Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttrs, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
import { createTextVNode, defineComponent, mergeProps, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Pages/Seller/BatchesIndex.vue?vue&type=script&setup=true&lang.ts
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
//#endregion
//#region resources/ts/Pages/Seller/BatchesIndex.vue
var _sfc_setup = BatchesIndex_vue_vue_type_script_setup_true_lang_default.setup;
BatchesIndex_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/BatchesIndex.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var BatchesIndex_default = BatchesIndex_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { BatchesIndex_default as default };

//# sourceMappingURL=BatchesIndex-CKMSPFWv.js.map