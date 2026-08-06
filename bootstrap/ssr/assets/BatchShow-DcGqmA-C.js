import { t as SellerLayout_default } from "./SellerLayout-Oli8Lv5k.js";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Pages/Seller/BatchShow.vue?vue&type=script&setup=true&lang.ts
var BatchShow_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "BatchShow",
	__ssrInlineRender: true,
	props: {
		batch: {},
		bands: {}
	},
	setup(__props) {
		const statusMeta = (status) => {
			switch (status) {
				case "draft": return {
					label: "Requested",
					color: "text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]"
				};
				case "committed": return {
					label: "Live",
					color: "text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]"
				};
				case "dispatched": return {
					label: "Dispatched",
					color: "text-[#3b82f6] bg-[rgba(59,130,246,0.1)]"
				};
				case "completed": return {
					label: "Completed",
					color: "text-[#c9a84c] bg-[rgba(201,168,76,0.1)]"
				};
				case "cancelled": return {
					label: "Cancelled",
					color: "text-red-400 bg-red-400/10"
				};
				default: return {
					label: status,
					color: "text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]"
				};
			}
		};
		const bandOrder = [
			{
				key: "mythic",
				label: "Mythic",
				text: "#c9a84c"
			},
			{
				key: "legendary",
				label: "Legendary",
				text: "#7b4fe9"
			},
			{
				key: "super",
				label: "Super",
				text: "#2dd4bf"
			},
			{
				key: "rare",
				label: "Rare",
				text: "#3b82f6"
			},
			{
				key: "common",
				label: "Common",
				text: "#a3a3a3"
			}
		];
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: __props.batch.reference }, null, _parent));
			_push(ssrRenderComponent(SellerLayout_default, {
				title: __props.batch.reference,
				subtitle: `${__props.batch.store.name} · ${(__props.batch.type ?? "").toUpperCase()} · ${__props.batch.pack_count} packs`
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="flex items-center gap-3 mb-6"${_scopeId}><span class="${ssrRenderClass(["text-xs font-['Jost',sans-serif] font-semibold uppercase px-3 py-1.5 rounded-[4px]", statusMeta(__props.batch.status).color])}"${_scopeId}>${ssrInterpolate(statusMeta(__props.batch.status).label)}</span>`);
						if (__props.batch.invoice) _push(`<a${ssrRenderAttr("href", `/admin/invoices/${__props.batch.invoice.id}/pdf`)} target="_blank" class="text-xs font-[&#39;Jost&#39;,sans-serif] text-[#c9a84c] hover:underline"${_scopeId}> Invoice ${ssrInterpolate(__props.batch.invoice.number)}</a>`);
						else _push(`<!---->`);
						_push(`</div>`);
						if (__props.batch.merged_into || __props.batch.merge_request_batch) {
							_push(`<div class="mb-6 bg-[rgba(124,58,237,0.08)] border border-[rgba(124,58,237,0.3)] rounded-[10px] p-4 text-sm font-[&#39;Jost&#39;,sans-serif] text-[#d8d3e0]"${_scopeId}>`);
							if (__props.batch.merged_into) {
								_push(`<p${_scopeId}> This batch&#39;s remaining packs were merged into `);
								_push(ssrRenderComponent(unref(Link), {
									href: `/seller/batches/${__props.batch.merged_into.id}`,
									class: "text-[#c9a84c] hover:underline"
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) _push(`${ssrInterpolate(__props.batch.merged_into.reference)}`);
										else return [createTextVNode(toDisplayString(__props.batch.merged_into.reference), 1)];
									}),
									_: 1
								}, _parent, _scopeId));
								_push(`. </p>`);
							} else _push(`<!---->`);
							if (__props.batch.merge_request_batch) _push(`<p${_scopeId}> You requested batch ${ssrInterpolate(__props.batch.merge_request_batch.reference)} be merged into this one once generated. </p>`);
							else _push(`<!---->`);
							_push(`</div>`);
						} else _push(`<!---->`);
						_push(`<div class="grid grid-cols-2 gap-4 mb-8 max-w-xl"${_scopeId}><div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5"${_scopeId}><p class="font-[&#39;Jost&#39;,sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2"${_scopeId}>Packs sold</p><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-2xl text-white"${_scopeId}>${ssrInterpolate(__props.batch.sold)} / ${ssrInterpolate(__props.batch.pack_count)}</p></div><div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5"${_scopeId}><p class="font-[&#39;Jost&#39;,sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2"${_scopeId}>Created</p><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-2xl text-white"${_scopeId}>${ssrInterpolate(__props.batch.created_at ?? "—")}</p></div></div><div class="flex flex-col gap-10"${_scopeId}><!--[-->`);
						ssrRenderList(bandOrder, (band) => {
							_push(`<div${_scopeId}><div class="flex items-center gap-3 mb-4"${_scopeId}><h2 class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-lg" style="${ssrRenderStyle({ color: band.text })}"${_scopeId}>${ssrInterpolate(band.label)}</h2><span class="font-[&#39;Jost&#39;,sans-serif] text-xs text-[#71717a]"${_scopeId}>${ssrInterpolate((__props.bands[band.key] ?? []).length)} cards</span></div>`);
							if ((__props.bands[band.key] ?? []).length) {
								_push(`<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4"${_scopeId}><!--[-->`);
								ssrRenderList(__props.bands[band.key], (card) => {
									_push(`<div class="${ssrRenderClass([{ "opacity-40": card.status === "sold" }, "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[8px] p-3 relative"])}"${_scopeId}><div class="aspect-[2.5/3.5] rounded-[6px] overflow-hidden bg-[#0d0b14] mb-2"${_scopeId}>`);
									if (card.image) _push(`<img${ssrRenderAttr("src", card.image)}${ssrRenderAttr("alt", card.name)} class="w-full h-full object-cover" loading="lazy"${_scopeId}>`);
									else _push(`<!---->`);
									_push(`</div><p class="font-[&#39;Jost&#39;,sans-serif] text-xs text-white truncate"${_scopeId}>${ssrInterpolate(card.name)}</p><p class="font-[&#39;Jost&#39;,sans-serif] text-[10px] text-[#71717a] truncate"${_scopeId}>${ssrInterpolate(card.set)} · #${ssrInterpolate(card.number)}</p>`);
									if (card.status === "sold") _push(`<span class="absolute top-2 right-2 text-[9px] font-[&#39;Jost&#39;,sans-serif] font-semibold uppercase bg-[#0d0b14]/90 text-[#71717a] px-1.5 py-0.5 rounded"${_scopeId}> Sold </span>`);
									else _push(`<!---->`);
									_push(`</div>`);
								});
								_push(`<!--]--></div>`);
							} else _push(`<!---->`);
							_push(`</div>`);
						});
						_push(`<!--]--></div>`);
					} else return [
						createVNode("div", { class: "flex items-center gap-3 mb-6" }, [createVNode("span", { class: ["text-xs font-['Jost',sans-serif] font-semibold uppercase px-3 py-1.5 rounded-[4px]", statusMeta(__props.batch.status).color] }, toDisplayString(statusMeta(__props.batch.status).label), 3), __props.batch.invoice ? (openBlock(), createBlock("a", {
							key: 0,
							href: `/admin/invoices/${__props.batch.invoice.id}/pdf`,
							target: "_blank",
							class: "text-xs font-['Jost',sans-serif] text-[#c9a84c] hover:underline"
						}, " Invoice " + toDisplayString(__props.batch.invoice.number), 9, ["href"])) : createCommentVNode("", true)]),
						__props.batch.merged_into || __props.batch.merge_request_batch ? (openBlock(), createBlock("div", {
							key: 0,
							class: "mb-6 bg-[rgba(124,58,237,0.08)] border border-[rgba(124,58,237,0.3)] rounded-[10px] p-4 text-sm font-['Jost',sans-serif] text-[#d8d3e0]"
						}, [__props.batch.merged_into ? (openBlock(), createBlock("p", { key: 0 }, [
							createTextVNode(" This batch's remaining packs were merged into "),
							createVNode(unref(Link), {
								href: `/seller/batches/${__props.batch.merged_into.id}`,
								class: "text-[#c9a84c] hover:underline"
							}, {
								default: withCtx(() => [createTextVNode(toDisplayString(__props.batch.merged_into.reference), 1)]),
								_: 1
							}, 8, ["href"]),
							createTextVNode(". ")
						])) : createCommentVNode("", true), __props.batch.merge_request_batch ? (openBlock(), createBlock("p", { key: 1 }, " You requested batch " + toDisplayString(__props.batch.merge_request_batch.reference) + " be merged into this one once generated. ", 1)) : createCommentVNode("", true)])) : createCommentVNode("", true),
						createVNode("div", { class: "grid grid-cols-2 gap-4 mb-8 max-w-xl" }, [createVNode("div", { class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5" }, [createVNode("p", { class: "font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2" }, "Packs sold"), createVNode("p", { class: "font-['Cinzel',sans-serif] font-bold text-2xl text-white" }, toDisplayString(__props.batch.sold) + " / " + toDisplayString(__props.batch.pack_count), 1)]), createVNode("div", { class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5" }, [createVNode("p", { class: "font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2" }, "Created"), createVNode("p", { class: "font-['Cinzel',sans-serif] font-bold text-2xl text-white" }, toDisplayString(__props.batch.created_at ?? "—"), 1)])]),
						createVNode("div", { class: "flex flex-col gap-10" }, [(openBlock(), createBlock(Fragment, null, renderList(bandOrder, (band) => {
							return createVNode("div", { key: band.key }, [createVNode("div", { class: "flex items-center gap-3 mb-4" }, [createVNode("h2", {
								class: "font-['Cinzel',sans-serif] font-bold text-lg",
								style: { color: band.text }
							}, toDisplayString(band.label), 5), createVNode("span", { class: "font-['Jost',sans-serif] text-xs text-[#71717a]" }, toDisplayString((__props.bands[band.key] ?? []).length) + " cards", 1)]), (__props.bands[band.key] ?? []).length ? (openBlock(), createBlock("div", {
								key: 0,
								class: "grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4"
							}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.bands[band.key], (card) => {
								return openBlock(), createBlock("div", {
									key: card.sequence,
									class: ["bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[8px] p-3 relative", { "opacity-40": card.status === "sold" }]
								}, [
									createVNode("div", { class: "aspect-[2.5/3.5] rounded-[6px] overflow-hidden bg-[#0d0b14] mb-2" }, [card.image ? (openBlock(), createBlock("img", {
										key: 0,
										src: card.image,
										alt: card.name,
										class: "w-full h-full object-cover",
										loading: "lazy"
									}, null, 8, ["src", "alt"])) : createCommentVNode("", true)]),
									createVNode("p", { class: "font-['Jost',sans-serif] text-xs text-white truncate" }, toDisplayString(card.name), 1),
									createVNode("p", { class: "font-['Jost',sans-serif] text-[10px] text-[#71717a] truncate" }, toDisplayString(card.set) + " · #" + toDisplayString(card.number), 1),
									card.status === "sold" ? (openBlock(), createBlock("span", {
										key: 0,
										class: "absolute top-2 right-2 text-[9px] font-['Jost',sans-serif] font-semibold uppercase bg-[#0d0b14]/90 text-[#71717a] px-1.5 py-0.5 rounded"
									}, " Sold ")) : createCommentVNode("", true)
								], 2);
							}), 128))])) : createCommentVNode("", true)]);
						}), 64))])
					];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
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

//# sourceMappingURL=BatchShow-DcGqmA-C.js.map