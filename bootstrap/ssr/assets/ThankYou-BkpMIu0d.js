import { n as Footer_default, t as Nav_default } from "./Nav-xTq28nz2.js";
import { Head } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
import { defineComponent, unref, useSSRContext } from "vue";
//#region resources/ts/Pages/Sell/ThankYou.vue?vue&type=script&setup=true&lang.ts
var ThankYou_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "ThankYou",
	__ssrInlineRender: true,
	props: {
		reference: {},
		shippingAddress: {},
		estimatedTotal: {},
		items: {}
	},
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Thank You" }, null, _parent));
			_push(`<main class="bg-[#0d0b14] overflow-x-hidden"><div class="relative shrink-0"><div class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative size-full"><div class="h-[49px] relative shrink-0">`);
			_push(ssrRenderComponent(Nav_default, null, null, _parent));
			_push(`</div></div></div><div class="relative shrink-0 w-full"><div class="content-stretch flex flex-col gap-[40px] items-center pb-[120px] pt-[80px] px-8 lg:px-[64px] relative size-full"><div class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center relative shrink-0 text-center mx-auto"><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white"><span class="leading-[normal]">Thank</span><span class="leading-[normal] text-[#c9a84c]"> you</span></p><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px] max-w-xl"> We&#39;ve got your submission. Your reference is <strong class="text-white">${ssrInterpolate(__props.reference)}</strong>. Your indicative offer is <strong class="text-[#c9a84c]">${ssrInterpolate(__props.estimatedTotal)}</strong> — we&#39;ll confirm the final price once your cards arrive and have been checked.</p></div>`);
			if (__props.items.length) {
				_push(`<div class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] p-[32px] relative shrink-0 w-full max-w-xl"><p class="font-[&#39;Jost&#39;,sans-serif] font-semibold text-[13px] text-[rgba(255,255,255,0.35)] uppercase mb-[16px]"> What you submitted</p><div class="flex flex-col gap-[10px]"><!--[-->`);
				ssrRenderList(__props.items, (item) => {
					_push(`<div class="flex items-center justify-between gap-[12px]"><div class="min-w-0"><p class="font-[&#39;Jost&#39;,sans-serif] text-[14px] text-white truncate">${ssrInterpolate(item.quantity)}× ${ssrInterpolate(item.card_name)}</p><p class="font-[&#39;Jost&#39;,sans-serif] text-[12px] text-[#a3a3a3] truncate">${ssrInterpolate(item.set_name)} · ${ssrInterpolate(item.card_number)}</p></div><p class="font-[&#39;Jost&#39;,sans-serif] text-[14px] text-[#c9a84c] shrink-0">${ssrInterpolate(item.total_offer)}</p></div>`);
				});
				_push(`<!--]--></div></div>`);
			} else _push(`<!---->`);
			_push(`<div class="bg-[#1a1628] border border-[#c9a84c]/40 rounded-[16px] p-[32px] relative shrink-0 w-full max-w-xl"><p class="font-[&#39;Jost&#39;,sans-serif] font-semibold text-[16px] text-white mb-[8px]"> Send your cards to:</p><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-relaxed text-[16px] text-[#c9a84c] whitespace-pre-line">${ssrInterpolate(__props.shippingAddress)}</p><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-relaxed text-[14px] text-[#a3a3a3] mt-[16px]"> Please package your cards securely (sleeved and rigid card / toploaders recommended) and include your reference <strong class="text-white">${ssrInterpolate(__props.reference)}</strong> in the parcel. We recommend using a tracked and insured delivery service. Once your cards arrive we&#39;ll check condition, confirm the final price, and arrange fast, secure payment.</p></div></div></div></main>`);
			_push(ssrRenderComponent(Footer_default, null, null, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/ts/Pages/Sell/ThankYou.vue
var _sfc_setup = ThankYou_vue_vue_type_script_setup_true_lang_default.setup;
ThankYou_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Sell/ThankYou.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var ThankYou_default = ThankYou_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { ThankYou_default as default };

//# sourceMappingURL=ThankYou-BkpMIu0d.js.map