import { t as SellerLayout_default } from "./SellerLayout-Oli8Lv5k.js";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderClass, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Pages/Seller/Wallet.vue?vue&type=script&setup=true&lang.ts
var Wallet_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Wallet",
	__ssrInlineRender: true,
	props: {
		stores: {},
		totalBalance: {},
		transactions: {}
	},
	setup(__props) {
		const formatMoney = (pence) => "£" + (pence / 100).toFixed(2);
		const formatDate = (iso) => new Date(iso).toLocaleDateString("en-GB", {
			day: "numeric",
			month: "short",
			year: "numeric",
			hour: "2-digit",
			minute: "2-digit"
		});
		const typeMeta = (type) => {
			switch (type) {
				case "credit": return {
					label: "Credit",
					color: "text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]"
				};
				case "redemption": return {
					label: "Applied to invoice",
					color: "text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]"
				};
				default: return {
					label: "Adjustment",
					color: "text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]"
				};
			}
		};
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Wallet" }, null, _parent));
			_push(ssrRenderComponent(SellerLayout_default, {
				title: "Wallet",
				subtitle: "Credit earned from appraised affiliate sell submissions — automatically applied to your next invoice(s)."
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="grid sm:grid-cols-3 gap-4 mb-8"${_scopeId}><div class="bg-gradient-to-br from-[rgba(201,168,76,0.15)] to-[rgba(201,168,76,0.03)] border border-[rgba(201,168,76,0.3)] rounded-[12px] p-6 sm:col-span-1"${_scopeId}><p class="font-[&#39;Jost&#39;,sans-serif] text-[11px] uppercase tracking-wide text-[#c9a84c]/80 mb-2"${_scopeId}>Total balance</p><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-3xl text-[#c9a84c]"${_scopeId}>${ssrInterpolate(formatMoney(__props.totalBalance))}</p></div>`);
						if (__props.stores.length > 1) {
							_push(`<div class="sm:col-span-2 grid grid-cols-2 gap-4"${_scopeId}><!--[-->`);
							ssrRenderList(__props.stores, (store) => {
								_push(`<div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5"${_scopeId}><p class="font-[&#39;Jost&#39;,sans-serif] text-xs text-[#a3a3a3] mb-1 truncate"${_scopeId}>${ssrInterpolate(store.name)}</p><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-xl text-white"${_scopeId}>${ssrInterpolate(formatMoney(store.credit_balance_pence))}</p></div>`);
							});
							_push(`<!--]--></div>`);
						} else _push(`<!---->`);
						_push(`</div><div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden"${_scopeId}><div class="px-6 py-4 border-b border-[rgba(220,193,117,0.08)]"${_scopeId}><h2 class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-lg text-white"${_scopeId}>Credit ledger</h2></div>`);
						if (__props.transactions.data.length === 0) _push(`<div class="text-[#a3a3a3] text-sm font-[&#39;Jost&#39;,sans-serif] py-12 text-center"${_scopeId}> No credit activity yet. </div>`);
						else {
							_push(`<table class="min-w-full text-sm"${_scopeId}><thead class="text-[rgba(255,255,255,0.35)] border-b border-[rgba(220,193,117,0.08)] font-[&#39;Jost&#39;,sans-serif] text-xs uppercase tracking-wide"${_scopeId}><tr class="text-left"${_scopeId}><th class="py-3 px-5"${_scopeId}>Date</th>`);
							if (__props.stores.length > 1) _push(`<th class="py-3 px-5"${_scopeId}>Store</th>`);
							else _push(`<!---->`);
							_push(`<th class="py-3 px-5"${_scopeId}>Type</th><th class="py-3 px-5"${_scopeId}>Reason</th><th class="py-3 px-5 text-right"${_scopeId}>Amount</th><th class="py-3 px-5 text-right"${_scopeId}>Balance after</th></tr></thead><tbody class="font-[&#39;Jost&#39;,sans-serif]"${_scopeId}><!--[-->`);
							ssrRenderList(__props.transactions.data, (tx) => {
								_push(`<tr class="border-b border-[rgba(220,193,117,0.06)] last:border-0"${_scopeId}><td class="py-3 px-5 text-[#a3a3a3] whitespace-nowrap"${_scopeId}>${ssrInterpolate(formatDate(tx.created_at))}</td>`);
								if (__props.stores.length > 1) _push(`<td class="py-3 px-5 text-[#a3a3a3]"${_scopeId}>${ssrInterpolate(tx.store_name)}</td>`);
								else _push(`<!---->`);
								_push(`<td class="py-3 px-5"${_scopeId}><span class="${ssrRenderClass(["text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]", typeMeta(tx.type).color])}"${_scopeId}>${ssrInterpolate(typeMeta(tx.type).label)}</span></td><td class="py-3 px-5 text-[#d8d3e0] max-w-xs"${_scopeId}>${ssrInterpolate(tx.reason ?? "—")} `);
								if (tx.invoice_number) _push(`<span class="text-[#71717a]"${_scopeId}>· ${ssrInterpolate(tx.invoice_number)}</span>`);
								else _push(`<!---->`);
								if (tx.submission_reference) _push(`<span class="text-[#71717a]"${_scopeId}>· ${ssrInterpolate(tx.submission_reference)}</span>`);
								else _push(`<!---->`);
								_push(`</td><td class="${ssrRenderClass(["py-3 px-5 text-right font-semibold", tx.amount_pence > 0 ? "text-[#2dd4bf]" : "text-red-400"])}"${_scopeId}>${ssrInterpolate(tx.amount_pence > 0 ? "+" : "-")}${ssrInterpolate(formatMoney(Math.abs(tx.amount_pence)))}</td><td class="py-3 px-5 text-right text-white"${_scopeId}>${ssrInterpolate(formatMoney(tx.balance_after_pence))}</td></tr>`);
							});
							_push(`<!--]--></tbody></table>`);
						}
						if (__props.transactions.links.length > 3) {
							_push(`<div class="flex justify-end gap-1 text-xs p-4 border-t border-[rgba(220,193,117,0.08)]"${_scopeId}><!--[-->`);
							ssrRenderList(__props.transactions.links, (link) => {
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
					} else return [createVNode("div", { class: "grid sm:grid-cols-3 gap-4 mb-8" }, [createVNode("div", { class: "bg-gradient-to-br from-[rgba(201,168,76,0.15)] to-[rgba(201,168,76,0.03)] border border-[rgba(201,168,76,0.3)] rounded-[12px] p-6 sm:col-span-1" }, [createVNode("p", { class: "font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[#c9a84c]/80 mb-2" }, "Total balance"), createVNode("p", { class: "font-['Cinzel',sans-serif] font-bold text-3xl text-[#c9a84c]" }, toDisplayString(formatMoney(__props.totalBalance)), 1)]), __props.stores.length > 1 ? (openBlock(), createBlock("div", {
						key: 0,
						class: "sm:col-span-2 grid grid-cols-2 gap-4"
					}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.stores, (store) => {
						return openBlock(), createBlock("div", {
							key: store.id,
							class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5"
						}, [createVNode("p", { class: "font-['Jost',sans-serif] text-xs text-[#a3a3a3] mb-1 truncate" }, toDisplayString(store.name), 1), createVNode("p", { class: "font-['Cinzel',sans-serif] font-bold text-xl text-white" }, toDisplayString(formatMoney(store.credit_balance_pence)), 1)]);
					}), 128))])) : createCommentVNode("", true)]), createVNode("div", { class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden" }, [
						createVNode("div", { class: "px-6 py-4 border-b border-[rgba(220,193,117,0.08)]" }, [createVNode("h2", { class: "font-['Cinzel',sans-serif] font-bold text-lg text-white" }, "Credit ledger")]),
						__props.transactions.data.length === 0 ? (openBlock(), createBlock("div", {
							key: 0,
							class: "text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-12 text-center"
						}, " No credit activity yet. ")) : (openBlock(), createBlock("table", {
							key: 1,
							class: "min-w-full text-sm"
						}, [createVNode("thead", { class: "text-[rgba(255,255,255,0.35)] border-b border-[rgba(220,193,117,0.08)] font-['Jost',sans-serif] text-xs uppercase tracking-wide" }, [createVNode("tr", { class: "text-left" }, [
							createVNode("th", { class: "py-3 px-5" }, "Date"),
							__props.stores.length > 1 ? (openBlock(), createBlock("th", {
								key: 0,
								class: "py-3 px-5"
							}, "Store")) : createCommentVNode("", true),
							createVNode("th", { class: "py-3 px-5" }, "Type"),
							createVNode("th", { class: "py-3 px-5" }, "Reason"),
							createVNode("th", { class: "py-3 px-5 text-right" }, "Amount"),
							createVNode("th", { class: "py-3 px-5 text-right" }, "Balance after")
						])]), createVNode("tbody", { class: "font-['Jost',sans-serif]" }, [(openBlock(true), createBlock(Fragment, null, renderList(__props.transactions.data, (tx) => {
							return openBlock(), createBlock("tr", {
								key: tx.id,
								class: "border-b border-[rgba(220,193,117,0.06)] last:border-0"
							}, [
								createVNode("td", { class: "py-3 px-5 text-[#a3a3a3] whitespace-nowrap" }, toDisplayString(formatDate(tx.created_at)), 1),
								__props.stores.length > 1 ? (openBlock(), createBlock("td", {
									key: 0,
									class: "py-3 px-5 text-[#a3a3a3]"
								}, toDisplayString(tx.store_name), 1)) : createCommentVNode("", true),
								createVNode("td", { class: "py-3 px-5" }, [createVNode("span", { class: ["text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]", typeMeta(tx.type).color] }, toDisplayString(typeMeta(tx.type).label), 3)]),
								createVNode("td", { class: "py-3 px-5 text-[#d8d3e0] max-w-xs" }, [
									createTextVNode(toDisplayString(tx.reason ?? "—") + " ", 1),
									tx.invoice_number ? (openBlock(), createBlock("span", {
										key: 0,
										class: "text-[#71717a]"
									}, "· " + toDisplayString(tx.invoice_number), 1)) : createCommentVNode("", true),
									tx.submission_reference ? (openBlock(), createBlock("span", {
										key: 1,
										class: "text-[#71717a]"
									}, "· " + toDisplayString(tx.submission_reference), 1)) : createCommentVNode("", true)
								]),
								createVNode("td", { class: ["py-3 px-5 text-right font-semibold", tx.amount_pence > 0 ? "text-[#2dd4bf]" : "text-red-400"] }, toDisplayString(tx.amount_pence > 0 ? "+" : "-") + toDisplayString(formatMoney(Math.abs(tx.amount_pence))), 3),
								createVNode("td", { class: "py-3 px-5 text-right text-white" }, toDisplayString(formatMoney(tx.balance_after_pence)), 1)
							]);
						}), 128))])])),
						__props.transactions.links.length > 3 ? (openBlock(), createBlock("div", {
							key: 2,
							class: "flex justify-end gap-1 text-xs p-4 border-t border-[rgba(220,193,117,0.08)]"
						}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.transactions.links, (link) => {
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
						}), 128))])) : createCommentVNode("", true)
					])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/ts/Pages/Seller/Wallet.vue
var _sfc_setup = Wallet_vue_vue_type_script_setup_true_lang_default.setup;
Wallet_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/Wallet.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Wallet_default = Wallet_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Wallet_default as default };

//# sourceMappingURL=Wallet-BQg9ArnV.js.map