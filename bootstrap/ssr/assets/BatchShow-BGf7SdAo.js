import { t as SellerHeader_default } from "./SellerHeader-BH0x5cAj.js";
import { ssrInterpolate, ssrRenderAttrs, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
import { defineComponent, mergeProps, useSSRContext } from "vue";
//#region resources/ts/Pages/Seller/BatchShow.vue?vue&type=script&setup=true&lang.ts
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
//#endregion
//#region resources/ts/Pages/Seller/BatchShow.vue
var _sfc_setup = BatchShow_vue_vue_type_script_setup_true_lang_default.setup;
BatchShow_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/BatchShow.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var BatchShow_default = BatchShow_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { BatchShow_default as default };

//# sourceMappingURL=BatchShow-BGf7SdAo.js.map