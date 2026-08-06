import { t as SellerLayout_default } from "./SellerLayout-Brsy-I1H.js";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderClass, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Download } from "lucide-vue-next";
//#region resources/ts/Pages/Seller/InvoicesIndex.vue?vue&type=script&setup=true&lang.ts
var InvoicesIndex_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "InvoicesIndex",
	__ssrInlineRender: true,
	props: { invoices: {} },
	setup(__props) {
		const formatMoney = (pence) => {
			if (!pence) return "£0.00";
			return "£" + (pence / 100).toFixed(2);
		};
		const statusMeta = (status) => {
			switch (status) {
				case "draft": return {
					label: "Draft",
					color: "text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]"
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
				case "cancelled": return {
					label: "Cancelled",
					color: "text-[#71717a] bg-[rgba(163,163,163,0.1)]"
				};
				default: return {
					label: status,
					color: "text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]"
				};
			}
		};
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Invoices" }, null, _parent));
			_push(ssrRenderComponent(SellerLayout_default, {
				title: "Invoices",
				subtitle: "Every invoice raised for your store(s) — download a PDF copy any time."
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden"${_scopeId}>`);
						if (__props.invoices.data.length === 0) _push(`<div class="text-[#a3a3a3] text-sm font-[&#39;Jost&#39;,sans-serif] py-12 text-center"${_scopeId}> No invoices yet. </div>`);
						else {
							_push(`<table class="min-w-full text-sm"${_scopeId}><thead class="text-[rgba(255,255,255,0.35)] border-b border-[rgba(220,193,117,0.08)] font-[&#39;Jost&#39;,sans-serif] text-xs uppercase tracking-wide"${_scopeId}><tr class="text-left"${_scopeId}><th class="py-3 px-5"${_scopeId}>Number</th><th class="py-3 px-5"${_scopeId}>Batch</th><th class="py-3 px-5"${_scopeId}>Issued</th><th class="py-3 px-5"${_scopeId}>Due</th><th class="py-3 px-5 text-right"${_scopeId}>Credit applied</th><th class="py-3 px-5 text-right"${_scopeId}>Amount due</th><th class="py-3 px-5"${_scopeId}>Status</th><th class="py-3 px-5"${_scopeId}></th></tr></thead><tbody class="font-[&#39;Jost&#39;,sans-serif]"${_scopeId}><!--[-->`);
							ssrRenderList(__props.invoices.data, (invoice) => {
								_push(`<tr class="border-b border-[rgba(220,193,117,0.06)] last:border-0"${_scopeId}><td class="py-3 px-5 text-white font-medium"${_scopeId}>${ssrInterpolate(invoice.number)}</td><td class="py-3 px-5 text-[#a3a3a3]"${_scopeId}>${ssrInterpolate(invoice.batch_reference ?? "—")}</td><td class="py-3 px-5 text-[#a3a3a3]"${_scopeId}>${ssrInterpolate(invoice.issued_on)}</td><td class="py-3 px-5 text-[#a3a3a3]"${_scopeId}>${ssrInterpolate(invoice.due_on)}</td><td class="py-3 px-5 text-right text-[#2dd4bf]"${_scopeId}>${ssrInterpolate(invoice.credit_applied_pence > 0 ? formatMoney(invoice.credit_applied_pence) : "—")}</td><td class="py-3 px-5 text-right text-[#c9a84c] font-semibold"${_scopeId}>${ssrInterpolate(formatMoney(invoice.amount_due_pence))}</td><td class="py-3 px-5"${_scopeId}><span class="${ssrRenderClass(["text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]", statusMeta(invoice.status).color])}"${_scopeId}>${ssrInterpolate(statusMeta(invoice.status).label)}</span></td><td class="py-3 px-5 text-right"${_scopeId}><a${ssrRenderAttr("href", `/admin/invoices/${invoice.id}/pdf`)} target="_blank" class="inline-flex items-center gap-1 text-[#c9a84c] hover:underline text-xs font-semibold"${_scopeId}>`);
								_push(ssrRenderComponent(unref(Download), { class: "size-3.5" }, null, _parent, _scopeId));
								_push(` PDF </a></td></tr>`);
							});
							_push(`<!--]--></tbody></table>`);
						}
						if (__props.invoices.links.length > 3) {
							_push(`<div class="flex justify-end gap-1 text-xs p-4 border-t border-[rgba(220,193,117,0.08)]"${_scopeId}><!--[-->`);
							ssrRenderList(__props.invoices.links, (link) => {
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
					} else return [createVNode("div", { class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden" }, [__props.invoices.data.length === 0 ? (openBlock(), createBlock("div", {
						key: 0,
						class: "text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-12 text-center"
					}, " No invoices yet. ")) : (openBlock(), createBlock("table", {
						key: 1,
						class: "min-w-full text-sm"
					}, [createVNode("thead", { class: "text-[rgba(255,255,255,0.35)] border-b border-[rgba(220,193,117,0.08)] font-['Jost',sans-serif] text-xs uppercase tracking-wide" }, [createVNode("tr", { class: "text-left" }, [
						createVNode("th", { class: "py-3 px-5" }, "Number"),
						createVNode("th", { class: "py-3 px-5" }, "Batch"),
						createVNode("th", { class: "py-3 px-5" }, "Issued"),
						createVNode("th", { class: "py-3 px-5" }, "Due"),
						createVNode("th", { class: "py-3 px-5 text-right" }, "Credit applied"),
						createVNode("th", { class: "py-3 px-5 text-right" }, "Amount due"),
						createVNode("th", { class: "py-3 px-5" }, "Status"),
						createVNode("th", { class: "py-3 px-5" })
					])]), createVNode("tbody", { class: "font-['Jost',sans-serif]" }, [(openBlock(true), createBlock(Fragment, null, renderList(__props.invoices.data, (invoice) => {
						return openBlock(), createBlock("tr", {
							key: invoice.id,
							class: "border-b border-[rgba(220,193,117,0.06)] last:border-0"
						}, [
							createVNode("td", { class: "py-3 px-5 text-white font-medium" }, toDisplayString(invoice.number), 1),
							createVNode("td", { class: "py-3 px-5 text-[#a3a3a3]" }, toDisplayString(invoice.batch_reference ?? "—"), 1),
							createVNode("td", { class: "py-3 px-5 text-[#a3a3a3]" }, toDisplayString(invoice.issued_on), 1),
							createVNode("td", { class: "py-3 px-5 text-[#a3a3a3]" }, toDisplayString(invoice.due_on), 1),
							createVNode("td", { class: "py-3 px-5 text-right text-[#2dd4bf]" }, toDisplayString(invoice.credit_applied_pence > 0 ? formatMoney(invoice.credit_applied_pence) : "—"), 1),
							createVNode("td", { class: "py-3 px-5 text-right text-[#c9a84c] font-semibold" }, toDisplayString(formatMoney(invoice.amount_due_pence)), 1),
							createVNode("td", { class: "py-3 px-5" }, [createVNode("span", { class: ["text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]", statusMeta(invoice.status).color] }, toDisplayString(statusMeta(invoice.status).label), 3)]),
							createVNode("td", { class: "py-3 px-5 text-right" }, [createVNode("a", {
								href: `/admin/invoices/${invoice.id}/pdf`,
								target: "_blank",
								class: "inline-flex items-center gap-1 text-[#c9a84c] hover:underline text-xs font-semibold"
							}, [createVNode(unref(Download), { class: "size-3.5" }), createTextVNode(" PDF ")], 8, ["href"])])
						]);
					}), 128))])])), __props.invoices.links.length > 3 ? (openBlock(), createBlock("div", {
						key: 2,
						class: "flex justify-end gap-1 text-xs p-4 border-t border-[rgba(220,193,117,0.08)]"
					}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.invoices.links, (link) => {
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
//#region resources/ts/Pages/Seller/InvoicesIndex.vue
var _sfc_setup = InvoicesIndex_vue_vue_type_script_setup_true_lang_default.setup;
InvoicesIndex_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/InvoicesIndex.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var InvoicesIndex_default = InvoicesIndex_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { InvoicesIndex_default as default };

//# sourceMappingURL=InvoicesIndex-D_tZyadH.js.map