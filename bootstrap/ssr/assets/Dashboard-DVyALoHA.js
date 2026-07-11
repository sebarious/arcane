import { t as SellerHeader_default } from "./SellerHeader-BH0x5cAj.js";
import { Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttrs, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
import { createTextVNode, defineComponent, mergeProps, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Pages/Seller/Dashboard.vue?vue&type=script&setup=true&lang.ts
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
//#endregion
//#region resources/ts/Pages/Seller/Dashboard.vue
var _sfc_setup = Dashboard_vue_vue_type_script_setup_true_lang_default.setup;
Dashboard_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/Dashboard.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Dashboard_default = Dashboard_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Dashboard_default as default };

//# sourceMappingURL=Dashboard-DVyALoHA.js.map