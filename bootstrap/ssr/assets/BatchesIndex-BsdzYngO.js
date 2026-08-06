import { t as SellerLayout_default } from "./SellerLayout-Oli8Lv5k.js";
import { Head, Link, router } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderClass, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Pages/Seller/BatchesIndex.vue?vue&type=script&setup=true&lang.ts
var BatchesIndex_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "BatchesIndex",
	__ssrInlineRender: true,
	props: {
		batches: {},
		storesById: {},
		filters: {}
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
		const STATUS_TABS = [
			{
				value: null,
				label: "All"
			},
			{
				value: "draft",
				label: "Requested"
			},
			{
				value: "committed",
				label: "Live"
			},
			{
				value: "dispatched",
				label: "Dispatched"
			},
			{
				value: "completed",
				label: "Completed"
			},
			{
				value: "cancelled",
				label: "Cancelled"
			}
		];
		function filterBy(status) {
			router.get("/seller/batches", status ? { status } : {}, {
				preserveState: true,
				preserveScroll: true
			});
		}
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Batches" }, null, _parent));
			_push(ssrRenderComponent(SellerLayout_default, {
				title: "Batches",
				subtitle: "Every Arcane mystery pack batch allocated to your store(s)."
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="flex gap-2 mb-6 overflow-x-auto pb-1"${_scopeId}><!--[-->`);
						ssrRenderList(STATUS_TABS, (tab) => {
							_push(`<button class="${ssrRenderClass(["shrink-0 px-4 py-2 rounded-[6px] text-xs font-['Jost',sans-serif] font-semibold uppercase tracking-wide transition-colors", (__props.filters.status ?? null) === tab.value ? "bg-[rgba(124,58,237,0.2)] text-white border border-[rgba(124,58,237,0.4)]" : "text-[#a3a3a3] border border-[#3d2f6e] hover:border-[#c9a84c]"])}"${_scopeId}>${ssrInterpolate(tab.label)}</button>`);
						});
						_push(`<!--]--></div><div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden"${_scopeId}>`);
						if (__props.batches.data.length === 0) _push(`<div class="text-[#a3a3a3] text-sm font-[&#39;Jost&#39;,sans-serif] py-12 text-center"${_scopeId}> No batches here yet. </div>`);
						else {
							_push(`<table class="min-w-full text-sm"${_scopeId}><thead class="text-[rgba(255,255,255,0.35)] border-b border-[rgba(220,193,117,0.08)] font-[&#39;Jost&#39;,sans-serif] text-xs uppercase tracking-wide"${_scopeId}><tr class="text-left"${_scopeId}><th class="py-3 px-5"${_scopeId}>Reference</th><th class="py-3 px-5"${_scopeId}>Store</th><th class="py-3 px-5"${_scopeId}>Product</th><th class="py-3 px-5"${_scopeId}>Sold</th><th class="py-3 px-5"${_scopeId}>Status</th><th class="py-3 px-5"${_scopeId}></th></tr></thead><tbody class="font-[&#39;Jost&#39;,sans-serif]"${_scopeId}><!--[-->`);
							ssrRenderList(__props.batches.data, (batch) => {
								_push(`<tr class="border-b border-[rgba(220,193,117,0.06)] last:border-0"${_scopeId}><td class="py-3 px-5 text-white font-medium"${_scopeId}>${ssrInterpolate(batch.reference)} `);
								if (batch.is_merged) _push(`<span class="ml-1 text-[10px] text-[#71717a]"${_scopeId}>(merged)</span>`);
								else _push(`<!---->`);
								_push(`</td><td class="py-3 px-5 text-[#a3a3a3]"${_scopeId}>${ssrInterpolate(__props.storesById[batch.store_id]?.name ?? "Store")}</td><td class="py-3 px-5 text-[#a3a3a3] text-xs uppercase"${_scopeId}>${ssrInterpolate(batch.type ?? "")}</td><td class="py-3 px-5 text-[#a3a3a3]"${_scopeId}>`);
								if (!batch.is_merged) _push(`<!--[-->${ssrInterpolate(batch.sold)} / ${ssrInterpolate(batch.pack_count)}<!--]-->`);
								else _push(`<!---->`);
								_push(`</td><td class="py-3 px-5"${_scopeId}>`);
								if (!batch.is_merged) _push(`<span class="${ssrRenderClass(["text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]", statusMeta(batch.status).color])}"${_scopeId}>${ssrInterpolate(statusMeta(batch.status).label)}</span>`);
								else _push(`<!---->`);
								_push(`</td><td class="py-3 px-5 text-right"${_scopeId}>`);
								if (!batch.is_merged) _push(ssrRenderComponent(unref(Link), {
									href: `/seller/batches/${batch.id}`,
									class: "text-[#c9a84c] hover:underline text-xs font-semibold"
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) _push(` View `);
										else return [createTextVNode(" View ")];
									}),
									_: 2
								}, _parent, _scopeId));
								else _push(`<!---->`);
								_push(`</td></tr>`);
							});
							_push(`<!--]--></tbody></table>`);
						}
						if (__props.batches.links.length > 3) {
							_push(`<div class="flex justify-end gap-1 text-xs p-4 border-t border-[rgba(220,193,117,0.08)]"${_scopeId}><!--[-->`);
							ssrRenderList(__props.batches.links, (link) => {
								_push(`<!--[-->`);
								if (link?.url) _push(ssrRenderComponent(unref(Link), {
									href: link.url,
									"preserve-state": "",
									"preserve-scroll": "",
									class: ["px-2.5 py-1 rounded border font-['Jost',sans-serif]", link.active ? "bg-[#c9a84c] text-[#0d0b14] border-[#c9a84c]" : "text-[#a3a3a3] border-[#3d2f6e] hover:border-[#c9a84c]"]
								}, null, _parent, _scopeId));
								else _push(`<!---->`);
								_push(`<!--]-->`);
							});
							_push(`<!--]--></div>`);
						} else _push(`<!---->`);
						_push(`</div>`);
					} else return [createVNode("div", { class: "flex gap-2 mb-6 overflow-x-auto pb-1" }, [(openBlock(), createBlock(Fragment, null, renderList(STATUS_TABS, (tab) => {
						return createVNode("button", {
							key: tab.label,
							onClick: ($event) => filterBy(tab.value),
							class: ["shrink-0 px-4 py-2 rounded-[6px] text-xs font-['Jost',sans-serif] font-semibold uppercase tracking-wide transition-colors", (__props.filters.status ?? null) === tab.value ? "bg-[rgba(124,58,237,0.2)] text-white border border-[rgba(124,58,237,0.4)]" : "text-[#a3a3a3] border border-[#3d2f6e] hover:border-[#c9a84c]"]
						}, toDisplayString(tab.label), 11, ["onClick"]);
					}), 64))]), createVNode("div", { class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden" }, [__props.batches.data.length === 0 ? (openBlock(), createBlock("div", {
						key: 0,
						class: "text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-12 text-center"
					}, " No batches here yet. ")) : (openBlock(), createBlock("table", {
						key: 1,
						class: "min-w-full text-sm"
					}, [createVNode("thead", { class: "text-[rgba(255,255,255,0.35)] border-b border-[rgba(220,193,117,0.08)] font-['Jost',sans-serif] text-xs uppercase tracking-wide" }, [createVNode("tr", { class: "text-left" }, [
						createVNode("th", { class: "py-3 px-5" }, "Reference"),
						createVNode("th", { class: "py-3 px-5" }, "Store"),
						createVNode("th", { class: "py-3 px-5" }, "Product"),
						createVNode("th", { class: "py-3 px-5" }, "Sold"),
						createVNode("th", { class: "py-3 px-5" }, "Status"),
						createVNode("th", { class: "py-3 px-5" })
					])]), createVNode("tbody", { class: "font-['Jost',sans-serif]" }, [(openBlock(true), createBlock(Fragment, null, renderList(__props.batches.data, (batch) => {
						return openBlock(), createBlock("tr", {
							key: batch.id,
							class: "border-b border-[rgba(220,193,117,0.06)] last:border-0"
						}, [
							createVNode("td", { class: "py-3 px-5 text-white font-medium" }, [createTextVNode(toDisplayString(batch.reference) + " ", 1), batch.is_merged ? (openBlock(), createBlock("span", {
								key: 0,
								class: "ml-1 text-[10px] text-[#71717a]"
							}, "(merged)")) : createCommentVNode("", true)]),
							createVNode("td", { class: "py-3 px-5 text-[#a3a3a3]" }, toDisplayString(__props.storesById[batch.store_id]?.name ?? "Store"), 1),
							createVNode("td", { class: "py-3 px-5 text-[#a3a3a3] text-xs uppercase" }, toDisplayString(batch.type ?? ""), 1),
							createVNode("td", { class: "py-3 px-5 text-[#a3a3a3]" }, [!batch.is_merged ? (openBlock(), createBlock(Fragment, { key: 0 }, [createTextVNode(toDisplayString(batch.sold) + " / " + toDisplayString(batch.pack_count), 1)], 64)) : createCommentVNode("", true)]),
							createVNode("td", { class: "py-3 px-5" }, [!batch.is_merged ? (openBlock(), createBlock("span", {
								key: 0,
								class: ["text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]", statusMeta(batch.status).color]
							}, toDisplayString(statusMeta(batch.status).label), 3)) : createCommentVNode("", true)]),
							createVNode("td", { class: "py-3 px-5 text-right" }, [!batch.is_merged ? (openBlock(), createBlock(unref(Link), {
								key: 0,
								href: `/seller/batches/${batch.id}`,
								class: "text-[#c9a84c] hover:underline text-xs font-semibold"
							}, {
								default: withCtx(() => [createTextVNode(" View ")]),
								_: 1
							}, 8, ["href"])) : createCommentVNode("", true)])
						]);
					}), 128))])])), __props.batches.links.length > 3 ? (openBlock(), createBlock("div", {
						key: 2,
						class: "flex justify-end gap-1 text-xs p-4 border-t border-[rgba(220,193,117,0.08)]"
					}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.batches.links, (link) => {
						return openBlock(), createBlock(Fragment, { key: link.label }, [link?.url ? (openBlock(), createBlock(unref(Link), {
							key: 0,
							href: link.url,
							"preserve-state": "",
							"preserve-scroll": "",
							class: ["px-2.5 py-1 rounded border font-['Jost',sans-serif]", link.active ? "bg-[#c9a84c] text-[#0d0b14] border-[#c9a84c]" : "text-[#a3a3a3] border-[#3d2f6e] hover:border-[#c9a84c]"],
							innerHTML: link.label
						}, null, 8, [
							"href",
							"class",
							"innerHTML"
						])) : createCommentVNode("", true)], 64);
					}), 128))])) : createCommentVNode("", true)])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
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

//# sourceMappingURL=BatchesIndex-BsdzYngO.js.map