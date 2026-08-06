import { t as SellerLayout_default } from "./SellerLayout-Brsy-I1H.js";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrLooseContain, ssrLooseEqual, ssrRenderAttr, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { Fragment, computed, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, vModelCheckbox, vModelSelect, vModelText, withCtx, withDirectives, withModifiers } from "vue";
//#region resources/ts/Pages/Seller/BatchRequest.vue?vue&type=script&setup=true&lang.ts
var BatchRequest_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "BatchRequest",
	__ssrInlineRender: true,
	props: {
		stores: {},
		products: {},
		mergeableBatches: {}
	},
	setup(__props) {
		const page = usePage();
		const vatRegistered = computed(() => page.props.vatRegistered ?? false);
		function formatPrice(pounds) {
			return "£" + pounds.toLocaleString("en-GB", {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2
			});
		}
		const props = __props;
		const form = useForm({
			store_id: props.stores[0]?.id ?? null,
			game: "pokemon",
			type: "ruby",
			notes: "",
			wants_merge: false,
			merge_request_batch_id: null
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
		const mergeableForStore = computed(() => props.mergeableBatches.filter((b) => b.store_id === form.store_id));
		const submit = () => {
			form.transform((data) => ({
				...data,
				merge_request_batch_id: data.wants_merge ? data.merge_request_batch_id : null
			})).post("/seller/request-batch");
		};
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Request a batch" }, null, _parent));
			_push(ssrRenderComponent(SellerLayout_default, {
				title: "Request a batch",
				subtitle: "Choose your store, game, and product — we'll review and generate it shortly."
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="max-w-2xl bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-8"${_scopeId}><form class="flex flex-col gap-5"${_scopeId}>`);
						if (__props.stores.length > 1) {
							_push(`<div${_scopeId}><label class="block font-[&#39;Jost&#39;,sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2"${_scopeId}>Store</label><select class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-[&#39;Jost&#39;,sans-serif] focus:outline-none focus:border-[#c9a84c]" required${_scopeId}><!--[-->`);
							ssrRenderList(__props.stores, (store) => {
								_push(`<option${ssrRenderAttr("value", store.id)}${ssrIncludeBooleanAttr(Array.isArray(unref(form).store_id) ? ssrLooseContain(unref(form).store_id, store.id) : ssrLooseEqual(unref(form).store_id, store.id)) ? " selected" : ""}${_scopeId}>${ssrInterpolate(store.name)}</option>`);
							});
							_push(`<!--]--></select></div>`);
						} else _push(`<!---->`);
						_push(`<div class="grid grid-cols-2 gap-4"${_scopeId}><div${_scopeId}><label class="block font-[&#39;Jost&#39;,sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2"${_scopeId}>Game</label><select class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-[&#39;Jost&#39;,sans-serif] focus:outline-none focus:border-[#c9a84c]" required${_scopeId}><!--[-->`);
						ssrRenderList(games.value, (g) => {
							_push(`<option${ssrRenderAttr("value", g.value)}${ssrIncludeBooleanAttr(Array.isArray(unref(form).game) ? ssrLooseContain(unref(form).game, g.value) : ssrLooseEqual(unref(form).game, g.value)) ? " selected" : ""}${_scopeId}>${ssrInterpolate(g.label)}</option>`);
						});
						_push(`<!--]--></select></div><div${_scopeId}><label class="block font-[&#39;Jost&#39;,sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2"${_scopeId}>Product</label><select class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-[&#39;Jost&#39;,sans-serif] focus:outline-none focus:border-[#c9a84c]" required${_scopeId}><!--[-->`);
						ssrRenderList(typesForGame.value, (p) => {
							_push(`<option${ssrRenderAttr("value", p.type)}${ssrIncludeBooleanAttr(Array.isArray(unref(form).type) ? ssrLooseContain(unref(form).type, p.type) : ssrLooseEqual(unref(form).type, p.type)) ? " selected" : ""}${_scopeId}>${ssrInterpolate(p.type_label)} — ${ssrInterpolate(p.packs)} packs, ${ssrInterpolate(formatPrice(p.price_pounds))}</option>`);
						});
						_push(`<!--]--></select></div></div>`);
						if (selectedProduct.value) {
							_push(`<div class="bg-[#1a1628] border border-[rgba(124,58,237,0.25)] rounded-[8px] p-4 text-xs text-[#a3a3a3] font-[&#39;Jost&#39;,sans-serif]"${_scopeId}><strong class="text-white"${_scopeId}>${ssrInterpolate(selectedProduct.value.type_label)}</strong> — ${ssrInterpolate(selectedProduct.value.packs)} sealed mystery packs, invoiced at <strong class="text-[#c9a84c]"${_scopeId}>${ssrInterpolate(formatPrice(selectedProduct.value.price_pounds))}</strong>`);
							if (vatRegistered.value) _push(`<!--[--> ex VAT<!--]-->`);
							else _push(`<!---->`);
							_push(`, due 48 hours after generation. </div>`);
						} else _push(`<!---->`);
						_push(`<div class="border border-[#3d2f6e] rounded-[8px] p-4"${_scopeId}><label class="flex items-start gap-3 cursor-pointer"${_scopeId}><input type="checkbox"${ssrIncludeBooleanAttr(Array.isArray(unref(form).wants_merge) ? ssrLooseContain(unref(form).wants_merge, null) : unref(form).wants_merge) ? " checked" : ""} class="mt-1 accent-[#c9a84c]"${_scopeId}><span class="font-[&#39;Jost&#39;,sans-serif] text-sm text-white"${_scopeId}> Merge one of my existing batches into this new one <span class="block text-xs text-[#71717a] mt-0.5 font-normal"${_scopeId}> If one of your batches is running low, we can move its remaining packs into this fresh one once it&#39;s generated — restores balanced pull odds instead of running two thin pools. </span></span></label>`);
						if (unref(form).wants_merge) {
							_push(`<div class="mt-4"${_scopeId}><label class="block font-[&#39;Jost&#39;,sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2"${_scopeId}> Batch to merge in </label><select class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-[&#39;Jost&#39;,sans-serif] focus:outline-none focus:border-[#c9a84c]"${_scopeId}><option${ssrRenderAttr("value", null)}${ssrIncludeBooleanAttr(Array.isArray(unref(form).merge_request_batch_id) ? ssrLooseContain(unref(form).merge_request_batch_id, null) : ssrLooseEqual(unref(form).merge_request_batch_id, null)) ? " selected" : ""}${_scopeId}>Select a batch…</option><!--[-->`);
							ssrRenderList(mergeableForStore.value, (b) => {
								_push(`<option${ssrRenderAttr("value", b.id)}${ssrIncludeBooleanAttr(Array.isArray(unref(form).merge_request_batch_id) ? ssrLooseContain(unref(form).merge_request_batch_id, b.id) : ssrLooseEqual(unref(form).merge_request_batch_id, b.id)) ? " selected" : ""}${_scopeId}>${ssrInterpolate(b.reference)} — ${ssrInterpolate((b.type ?? "").toUpperCase())}, ${ssrInterpolate(b.pack_count)} packs </option>`);
							});
							_push(`<!--]--></select>`);
							if (mergeableForStore.value.length === 0) _push(`<p class="text-xs text-[#71717a] mt-2"${_scopeId}> No eligible batches for this store yet. </p>`);
							else _push(`<!---->`);
							_push(`</div>`);
						} else _push(`<!---->`);
						_push(`</div><div${_scopeId}><label class="block font-[&#39;Jost&#39;,sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2"${_scopeId}> Notes <span class="normal-case font-normal text-[#71717a]"${_scopeId}>(optional)</span></label><textarea rows="3" placeholder="Anything we should know about delivery, timing, etc." class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-[&#39;Jost&#39;,sans-serif] focus:outline-none focus:border-[#c9a84c]"${_scopeId}>${ssrInterpolate(unref(form).notes)}</textarea></div><button type="submit"${ssrIncludeBooleanAttr(unref(form).processing) ? " disabled" : ""} class="w-full px-6 py-3.5 rounded-[4px] text-sm font-[&#39;Jost&#39;,sans-serif] font-bold uppercase tracking-wide text-[#0d0b14] disabled:opacity-50" style="${ssrRenderStyle({ "background-image": "linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%)" })}"${_scopeId}>${ssrInterpolate(unref(form).processing ? "Submitting…" : "Submit request")}</button></form></div>`);
					} else return [createVNode("div", { class: "max-w-2xl bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-8" }, [createVNode("form", {
						onSubmit: withModifiers(submit, ["prevent"]),
						class: "flex flex-col gap-5"
					}, [
						__props.stores.length > 1 ? (openBlock(), createBlock("div", { key: 0 }, [createVNode("label", { class: "block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2" }, "Store"), withDirectives(createVNode("select", {
							"onUpdate:modelValue": ($event) => unref(form).store_id = $event,
							class: "w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]",
							required: ""
						}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.stores, (store) => {
							return openBlock(), createBlock("option", {
								key: store.id,
								value: store.id
							}, toDisplayString(store.name), 9, ["value"]);
						}), 128))], 8, ["onUpdate:modelValue"]), [[vModelSelect, unref(form).store_id]])])) : createCommentVNode("", true),
						createVNode("div", { class: "grid grid-cols-2 gap-4" }, [createVNode("div", null, [createVNode("label", { class: "block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2" }, "Game"), withDirectives(createVNode("select", {
							"onUpdate:modelValue": ($event) => unref(form).game = $event,
							class: "w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]",
							required: ""
						}, [(openBlock(true), createBlock(Fragment, null, renderList(games.value, (g) => {
							return openBlock(), createBlock("option", {
								key: g.value,
								value: g.value
							}, toDisplayString(g.label), 9, ["value"]);
						}), 128))], 8, ["onUpdate:modelValue"]), [[vModelSelect, unref(form).game]])]), createVNode("div", null, [createVNode("label", { class: "block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2" }, "Product"), withDirectives(createVNode("select", {
							"onUpdate:modelValue": ($event) => unref(form).type = $event,
							class: "w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]",
							required: ""
						}, [(openBlock(true), createBlock(Fragment, null, renderList(typesForGame.value, (p) => {
							return openBlock(), createBlock("option", {
								key: p.type,
								value: p.type
							}, toDisplayString(p.type_label) + " — " + toDisplayString(p.packs) + " packs, " + toDisplayString(formatPrice(p.price_pounds)), 9, ["value"]);
						}), 128))], 8, ["onUpdate:modelValue"]), [[vModelSelect, unref(form).type]])])]),
						selectedProduct.value ? (openBlock(), createBlock("div", {
							key: 1,
							class: "bg-[#1a1628] border border-[rgba(124,58,237,0.25)] rounded-[8px] p-4 text-xs text-[#a3a3a3] font-['Jost',sans-serif]"
						}, [
							createVNode("strong", { class: "text-white" }, toDisplayString(selectedProduct.value.type_label), 1),
							createTextVNode(" — " + toDisplayString(selectedProduct.value.packs) + " sealed mystery packs, invoiced at ", 1),
							createVNode("strong", { class: "text-[#c9a84c]" }, toDisplayString(formatPrice(selectedProduct.value.price_pounds)), 1),
							vatRegistered.value ? (openBlock(), createBlock(Fragment, { key: 0 }, [createTextVNode(" ex VAT")], 64)) : createCommentVNode("", true),
							createTextVNode(", due 48 hours after generation. ")
						])) : createCommentVNode("", true),
						createVNode("div", { class: "border border-[#3d2f6e] rounded-[8px] p-4" }, [createVNode("label", { class: "flex items-start gap-3 cursor-pointer" }, [withDirectives(createVNode("input", {
							type: "checkbox",
							"onUpdate:modelValue": ($event) => unref(form).wants_merge = $event,
							class: "mt-1 accent-[#c9a84c]"
						}, null, 8, ["onUpdate:modelValue"]), [[vModelCheckbox, unref(form).wants_merge]]), createVNode("span", { class: "font-['Jost',sans-serif] text-sm text-white" }, [createTextVNode(" Merge one of my existing batches into this new one "), createVNode("span", { class: "block text-xs text-[#71717a] mt-0.5 font-normal" }, " If one of your batches is running low, we can move its remaining packs into this fresh one once it's generated — restores balanced pull odds instead of running two thin pools. ")])]), unref(form).wants_merge ? (openBlock(), createBlock("div", {
							key: 0,
							class: "mt-4"
						}, [
							createVNode("label", { class: "block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2" }, " Batch to merge in "),
							withDirectives(createVNode("select", {
								"onUpdate:modelValue": ($event) => unref(form).merge_request_batch_id = $event,
								class: "w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]"
							}, [createVNode("option", { value: null }, "Select a batch…"), (openBlock(true), createBlock(Fragment, null, renderList(mergeableForStore.value, (b) => {
								return openBlock(), createBlock("option", {
									key: b.id,
									value: b.id
								}, toDisplayString(b.reference) + " — " + toDisplayString((b.type ?? "").toUpperCase()) + ", " + toDisplayString(b.pack_count) + " packs ", 9, ["value"]);
							}), 128))], 8, ["onUpdate:modelValue"]), [[vModelSelect, unref(form).merge_request_batch_id]]),
							mergeableForStore.value.length === 0 ? (openBlock(), createBlock("p", {
								key: 0,
								class: "text-xs text-[#71717a] mt-2"
							}, " No eligible batches for this store yet. ")) : createCommentVNode("", true)
						])) : createCommentVNode("", true)]),
						createVNode("div", null, [createVNode("label", { class: "block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2" }, [createTextVNode(" Notes "), createVNode("span", { class: "normal-case font-normal text-[#71717a]" }, "(optional)")]), withDirectives(createVNode("textarea", {
							"onUpdate:modelValue": ($event) => unref(form).notes = $event,
							rows: "3",
							placeholder: "Anything we should know about delivery, timing, etc.",
							class: "w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]"
						}, null, 8, ["onUpdate:modelValue"]), [[vModelText, unref(form).notes]])]),
						createVNode("button", {
							type: "submit",
							disabled: unref(form).processing,
							class: "w-full px-6 py-3.5 rounded-[4px] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide text-[#0d0b14] disabled:opacity-50",
							style: { "background-image": "linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%)" }
						}, toDisplayString(unref(form).processing ? "Submitting…" : "Submit request"), 9, ["disabled"])
					], 32)])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
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

//# sourceMappingURL=BatchRequest-7KoArm1g.js.map