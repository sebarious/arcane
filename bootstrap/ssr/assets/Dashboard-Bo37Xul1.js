import { t as SellerLayout_default } from "./SellerLayout-Oli8Lv5k.js";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { ArrowRight, Coins, FileClock, PackageSearch, Wallet } from "lucide-vue-next";
//#region resources/ts/Pages/Seller/Dashboard.vue?vue&type=script&setup=true&lang.ts
var Dashboard_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Dashboard",
	__ssrInlineRender: true,
	props: {
		stores: {},
		batches: {},
		progress: {},
		stats: {},
		invoices: {}
	},
	setup(__props) {
		const props = __props;
		const formatMoney = (pence) => {
			if (!pence) return "£0.00";
			return "£" + (pence / 100).toFixed(2);
		};
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
				case "sent": return {
					label: "Sent",
					color: "text-[#3b82f6] bg-[rgba(59,130,246,0.1)]"
				};
				case "paid": return {
					label: "Paid",
					color: "text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]"
				};
				case "overdue": return {
					label: "Overdue",
					color: "text-red-400 bg-red-400/10"
				};
				default: return {
					label: status,
					color: "text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]"
				};
			}
		};
		const storeName = (storeId) => props.stores.find((s) => s.id === storeId)?.name ?? "Store";
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Seller Dashboard" }, null, _parent));
			_push(ssrRenderComponent(SellerLayout_default, {
				title: "Dashboard",
				subtitle: `Welcome back — here's how ${__props.stores[0]?.name ?? "your store"} is doing.`
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8"${_scopeId}><div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5"${_scopeId}><div class="flex items-center gap-2 text-[#7b4fe9] mb-2"${_scopeId}>`);
						_push(ssrRenderComponent(unref(PackageSearch), { class: "size-4" }, null, _parent, _scopeId));
						_push(`<span class="font-[&#39;Jost&#39;,sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]"${_scopeId}>Active batches</span></div><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[28px] text-white"${_scopeId}>${ssrInterpolate(__props.stats.active_batches)}</p></div><div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5"${_scopeId}><div class="flex items-center gap-2 text-[#2dd4bf] mb-2"${_scopeId}>`);
						_push(ssrRenderComponent(unref(FileClock), { class: "size-4" }, null, _parent, _scopeId));
						_push(`<span class="font-[&#39;Jost&#39;,sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]"${_scopeId}>Requests pending</span></div><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[28px] text-white"${_scopeId}>${ssrInterpolate(__props.stats.draft_requests)}</p></div><div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5"${_scopeId}><div class="flex items-center gap-2 text-[#c9a84c] mb-2"${_scopeId}>`);
						_push(ssrRenderComponent(unref(Coins), { class: "size-4" }, null, _parent, _scopeId));
						_push(`<span class="font-[&#39;Jost&#39;,sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]"${_scopeId}>Lifetime revenue</span></div><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[28px] text-white"${_scopeId}>${ssrInterpolate(formatMoney(__props.stats.lifetime_revenue_pence))}</p></div><div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5"${_scopeId}><div class="flex items-center gap-2 text-[#DCC175] mb-2"${_scopeId}>`);
						_push(ssrRenderComponent(unref(Wallet), { class: "size-4" }, null, _parent, _scopeId));
						_push(`<span class="font-[&#39;Jost&#39;,sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]"${_scopeId}>Wallet balance</span></div><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[28px] text-white"${_scopeId}>${ssrInterpolate(formatMoney(__props.stats.wallet_balance_pence))}</p></div></div><div class="grid lg:grid-cols-3 gap-6"${_scopeId}><div class="lg:col-span-2 bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6"${_scopeId}><div class="flex items-center justify-between mb-4"${_scopeId}><h2 class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-lg text-white"${_scopeId}>Recent batches</h2>`);
						_push(ssrRenderComponent(unref(Link), {
							href: "/seller/batches",
							class: "flex items-center gap-1 text-xs text-[#a3a3a3] hover:text-[#c9a84c] font-['Jost',sans-serif]"
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) {
									_push(` View all `);
									_push(ssrRenderComponent(unref(ArrowRight), { class: "size-3" }, null, _parent, _scopeId));
								} else return [createTextVNode(" View all "), createVNode(unref(ArrowRight), { class: "size-3" })];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(`</div>`);
						if (__props.batches.length === 0) _push(`<div class="text-[#a3a3a3] text-sm font-[&#39;Jost&#39;,sans-serif] py-6 text-center"${_scopeId}> No live batches yet. Once one is generated it&#39;ll show up here. </div>`);
						else {
							_push(`<div class="flex flex-col gap-3"${_scopeId}><!--[-->`);
							ssrRenderList(__props.batches, (batch) => {
								_push(ssrRenderComponent(unref(Link), {
									key: batch.id,
									href: `/seller/batches/${batch.id}`,
									class: "block bg-[#1a1628] border border-[#3d2f6e] rounded-[8px] p-4 hover:border-[#c9a84c] transition-colors"
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) _push(`<div class="flex items-center justify-between gap-4 mb-2"${_scopeId}><div${_scopeId}><p class="font-[&#39;Jost&#39;,sans-serif] font-semibold text-sm text-white"${_scopeId}>${ssrInterpolate(batch.reference)}</p><p class="font-[&#39;Jost&#39;,sans-serif] text-xs text-[#a3a3a3]"${_scopeId}>${ssrInterpolate(storeName(batch.store_id))} · ${ssrInterpolate((batch.type ?? "").toUpperCase())} · ${ssrInterpolate(batch.pack_count)} packs </p></div><span class="${ssrRenderClass(["text-[10px] font-['Jost',sans-serif] font-semibold uppercase px-2 py-1 rounded-[4px]", statusMeta(batch.status).color])}"${_scopeId}>${ssrInterpolate(statusMeta(batch.status).label)}</span></div><div class="w-full h-1.5 bg-[#0d0b14] rounded-full overflow-hidden"${_scopeId}><div class="h-full bg-gradient-to-r from-[#7b4fe9] to-[#c9a84c]" style="${ssrRenderStyle({ width: `${__props.progress[batch.id]?.percent ?? 0}%` })}"${_scopeId}></div></div><p class="font-[&#39;Jost&#39;,sans-serif] text-[11px] text-[#71717a] mt-1.5"${_scopeId}>${ssrInterpolate(__props.progress[batch.id]?.sold ?? 0)} / ${ssrInterpolate(__props.progress[batch.id]?.total ?? batch.pack_count)} packs sold </p>`);
										else return [
											createVNode("div", { class: "flex items-center justify-between gap-4 mb-2" }, [createVNode("div", null, [createVNode("p", { class: "font-['Jost',sans-serif] font-semibold text-sm text-white" }, toDisplayString(batch.reference), 1), createVNode("p", { class: "font-['Jost',sans-serif] text-xs text-[#a3a3a3]" }, toDisplayString(storeName(batch.store_id)) + " · " + toDisplayString((batch.type ?? "").toUpperCase()) + " · " + toDisplayString(batch.pack_count) + " packs ", 1)]), createVNode("span", { class: ["text-[10px] font-['Jost',sans-serif] font-semibold uppercase px-2 py-1 rounded-[4px]", statusMeta(batch.status).color] }, toDisplayString(statusMeta(batch.status).label), 3)]),
											createVNode("div", { class: "w-full h-1.5 bg-[#0d0b14] rounded-full overflow-hidden" }, [createVNode("div", {
												class: "h-full bg-gradient-to-r from-[#7b4fe9] to-[#c9a84c]",
												style: { width: `${__props.progress[batch.id]?.percent ?? 0}%` }
											}, null, 4)]),
											createVNode("p", { class: "font-['Jost',sans-serif] text-[11px] text-[#71717a] mt-1.5" }, toDisplayString(__props.progress[batch.id]?.sold ?? 0) + " / " + toDisplayString(__props.progress[batch.id]?.total ?? batch.pack_count) + " packs sold ", 1)
										];
									}),
									_: 2
								}, _parent, _scopeId));
							});
							_push(`<!--]--></div>`);
						}
						_push(`</div><div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6"${_scopeId}><div class="flex items-center justify-between mb-4"${_scopeId}><h2 class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-lg text-white"${_scopeId}>Invoices</h2>`);
						_push(ssrRenderComponent(unref(Link), {
							href: "/seller/invoices",
							class: "flex items-center gap-1 text-xs text-[#a3a3a3] hover:text-[#c9a84c] font-['Jost',sans-serif]"
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) {
									_push(` View all `);
									_push(ssrRenderComponent(unref(ArrowRight), { class: "size-3" }, null, _parent, _scopeId));
								} else return [createTextVNode(" View all "), createVNode(unref(ArrowRight), { class: "size-3" })];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(`</div>`);
						if (__props.invoices.length === 0) _push(`<div class="text-[#a3a3a3] text-sm font-[&#39;Jost&#39;,sans-serif] py-6 text-center"${_scopeId}> No invoices yet. </div>`);
						else {
							_push(`<div class="flex flex-col gap-3"${_scopeId}><!--[-->`);
							ssrRenderList(__props.invoices, (invoice) => {
								_push(`<div class="flex items-center justify-between gap-3"${_scopeId}><div class="min-w-0"${_scopeId}><p class="font-[&#39;Jost&#39;,sans-serif] font-medium text-sm text-white truncate"${_scopeId}>${ssrInterpolate(invoice.number)}</p><p class="font-[&#39;Jost&#39;,sans-serif] text-[11px] text-[#71717a]"${_scopeId}>Due ${ssrInterpolate(invoice.due_on)}</p></div><div class="text-right shrink-0"${_scopeId}><p class="font-[&#39;Jost&#39;,sans-serif] text-sm text-[#c9a84c] font-semibold"${_scopeId}>${ssrInterpolate(formatMoney(invoice.amount_due_pence))}</p><span class="${ssrRenderClass(["text-[9px] font-['Jost',sans-serif] uppercase px-1.5 py-0.5 rounded", statusMeta(invoice.status).color])}"${_scopeId}>${ssrInterpolate(statusMeta(invoice.status).label)}</span></div></div>`);
							});
							_push(`<!--]--></div>`);
						}
						if (__props.stats.unpaid_invoices_count > 0) _push(`<div class="mt-4 pt-4 border-t border-[rgba(220,193,117,0.08)]"${_scopeId}><p class="font-[&#39;Jost&#39;,sans-serif] text-xs text-[#a3a3a3]"${_scopeId}><span class="text-white font-semibold"${_scopeId}>${ssrInterpolate(formatMoney(__props.stats.unpaid_invoices_pence))}</span> outstanding across ${ssrInterpolate(__props.stats.unpaid_invoices_count)} invoice(s) </p></div>`);
						else _push(`<!---->`);
						_push(`</div></div>`);
					} else return [createVNode("div", { class: "grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8" }, [
						createVNode("div", { class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5" }, [createVNode("div", { class: "flex items-center gap-2 text-[#7b4fe9] mb-2" }, [createVNode(unref(PackageSearch), { class: "size-4" }), createVNode("span", { class: "font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]" }, "Active batches")]), createVNode("p", { class: "font-['Cinzel',sans-serif] font-bold text-[28px] text-white" }, toDisplayString(__props.stats.active_batches), 1)]),
						createVNode("div", { class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5" }, [createVNode("div", { class: "flex items-center gap-2 text-[#2dd4bf] mb-2" }, [createVNode(unref(FileClock), { class: "size-4" }), createVNode("span", { class: "font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]" }, "Requests pending")]), createVNode("p", { class: "font-['Cinzel',sans-serif] font-bold text-[28px] text-white" }, toDisplayString(__props.stats.draft_requests), 1)]),
						createVNode("div", { class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5" }, [createVNode("div", { class: "flex items-center gap-2 text-[#c9a84c] mb-2" }, [createVNode(unref(Coins), { class: "size-4" }), createVNode("span", { class: "font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]" }, "Lifetime revenue")]), createVNode("p", { class: "font-['Cinzel',sans-serif] font-bold text-[28px] text-white" }, toDisplayString(formatMoney(__props.stats.lifetime_revenue_pence)), 1)]),
						createVNode("div", { class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5" }, [createVNode("div", { class: "flex items-center gap-2 text-[#DCC175] mb-2" }, [createVNode(unref(Wallet), { class: "size-4" }), createVNode("span", { class: "font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]" }, "Wallet balance")]), createVNode("p", { class: "font-['Cinzel',sans-serif] font-bold text-[28px] text-white" }, toDisplayString(formatMoney(__props.stats.wallet_balance_pence)), 1)])
					]), createVNode("div", { class: "grid lg:grid-cols-3 gap-6" }, [createVNode("div", { class: "lg:col-span-2 bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6" }, [createVNode("div", { class: "flex items-center justify-between mb-4" }, [createVNode("h2", { class: "font-['Cinzel',sans-serif] font-bold text-lg text-white" }, "Recent batches"), createVNode(unref(Link), {
						href: "/seller/batches",
						class: "flex items-center gap-1 text-xs text-[#a3a3a3] hover:text-[#c9a84c] font-['Jost',sans-serif]"
					}, {
						default: withCtx(() => [createTextVNode(" View all "), createVNode(unref(ArrowRight), { class: "size-3" })]),
						_: 1
					})]), __props.batches.length === 0 ? (openBlock(), createBlock("div", {
						key: 0,
						class: "text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-6 text-center"
					}, " No live batches yet. Once one is generated it'll show up here. ")) : (openBlock(), createBlock("div", {
						key: 1,
						class: "flex flex-col gap-3"
					}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.batches, (batch) => {
						return openBlock(), createBlock(unref(Link), {
							key: batch.id,
							href: `/seller/batches/${batch.id}`,
							class: "block bg-[#1a1628] border border-[#3d2f6e] rounded-[8px] p-4 hover:border-[#c9a84c] transition-colors"
						}, {
							default: withCtx(() => [
								createVNode("div", { class: "flex items-center justify-between gap-4 mb-2" }, [createVNode("div", null, [createVNode("p", { class: "font-['Jost',sans-serif] font-semibold text-sm text-white" }, toDisplayString(batch.reference), 1), createVNode("p", { class: "font-['Jost',sans-serif] text-xs text-[#a3a3a3]" }, toDisplayString(storeName(batch.store_id)) + " · " + toDisplayString((batch.type ?? "").toUpperCase()) + " · " + toDisplayString(batch.pack_count) + " packs ", 1)]), createVNode("span", { class: ["text-[10px] font-['Jost',sans-serif] font-semibold uppercase px-2 py-1 rounded-[4px]", statusMeta(batch.status).color] }, toDisplayString(statusMeta(batch.status).label), 3)]),
								createVNode("div", { class: "w-full h-1.5 bg-[#0d0b14] rounded-full overflow-hidden" }, [createVNode("div", {
									class: "h-full bg-gradient-to-r from-[#7b4fe9] to-[#c9a84c]",
									style: { width: `${__props.progress[batch.id]?.percent ?? 0}%` }
								}, null, 4)]),
								createVNode("p", { class: "font-['Jost',sans-serif] text-[11px] text-[#71717a] mt-1.5" }, toDisplayString(__props.progress[batch.id]?.sold ?? 0) + " / " + toDisplayString(__props.progress[batch.id]?.total ?? batch.pack_count) + " packs sold ", 1)
							]),
							_: 2
						}, 1032, ["href"]);
					}), 128))]))]), createVNode("div", { class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6" }, [
						createVNode("div", { class: "flex items-center justify-between mb-4" }, [createVNode("h2", { class: "font-['Cinzel',sans-serif] font-bold text-lg text-white" }, "Invoices"), createVNode(unref(Link), {
							href: "/seller/invoices",
							class: "flex items-center gap-1 text-xs text-[#a3a3a3] hover:text-[#c9a84c] font-['Jost',sans-serif]"
						}, {
							default: withCtx(() => [createTextVNode(" View all "), createVNode(unref(ArrowRight), { class: "size-3" })]),
							_: 1
						})]),
						__props.invoices.length === 0 ? (openBlock(), createBlock("div", {
							key: 0,
							class: "text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-6 text-center"
						}, " No invoices yet. ")) : (openBlock(), createBlock("div", {
							key: 1,
							class: "flex flex-col gap-3"
						}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.invoices, (invoice) => {
							return openBlock(), createBlock("div", {
								key: invoice.id,
								class: "flex items-center justify-between gap-3"
							}, [createVNode("div", { class: "min-w-0" }, [createVNode("p", { class: "font-['Jost',sans-serif] font-medium text-sm text-white truncate" }, toDisplayString(invoice.number), 1), createVNode("p", { class: "font-['Jost',sans-serif] text-[11px] text-[#71717a]" }, "Due " + toDisplayString(invoice.due_on), 1)]), createVNode("div", { class: "text-right shrink-0" }, [createVNode("p", { class: "font-['Jost',sans-serif] text-sm text-[#c9a84c] font-semibold" }, toDisplayString(formatMoney(invoice.amount_due_pence)), 1), createVNode("span", { class: ["text-[9px] font-['Jost',sans-serif] uppercase px-1.5 py-0.5 rounded", statusMeta(invoice.status).color] }, toDisplayString(statusMeta(invoice.status).label), 3)])]);
						}), 128))])),
						__props.stats.unpaid_invoices_count > 0 ? (openBlock(), createBlock("div", {
							key: 2,
							class: "mt-4 pt-4 border-t border-[rgba(220,193,117,0.08)]"
						}, [createVNode("p", { class: "font-['Jost',sans-serif] text-xs text-[#a3a3a3]" }, [createVNode("span", { class: "text-white font-semibold" }, toDisplayString(formatMoney(__props.stats.unpaid_invoices_pence)), 1), createTextVNode(" outstanding across " + toDisplayString(__props.stats.unpaid_invoices_count) + " invoice(s) ", 1)])])) : createCommentVNode("", true)
					])])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
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

//# sourceMappingURL=Dashboard-Bo37Xul1.js.map