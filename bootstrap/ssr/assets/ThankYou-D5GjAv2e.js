import { n as Footer_default, t as Nav_default } from "./Nav-Bb-_Zprs.js";
import { ssrInterpolate, ssrRenderComponent } from "vue/server-renderer";
import { defineComponent, useSSRContext } from "vue";
//#region resources/ts/Pages/Sell/ThankYou.vue?vue&type=script&setup=true&lang.ts
var ThankYou_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "ThankYou",
	__ssrInlineRender: true,
	props: { reference: {} },
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[--><main class="bg-[#0d0b14] overflow-x-hidden"><div class="relative shrink-0"><div class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative size-full"><div class="h-[49px] relative shrink-0">`);
			_push(ssrRenderComponent(Nav_default, null, null, _parent));
			_push(`</div></div></div><div class="relative shrink-0 w-full"><div class="content-stretch flex flex-col gap-[56px] items-start justify-center pb-[120px] pt-[80px] px-8 lg:px-[64px] relative size-full"><div class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center relative shrink-0 text-center mx-auto"><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white"><span class="leading-[normal]">Thank</span><span class="leading-[normal] text-[#c9a84c]"> you</span></p><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px] max-w-xl"> We&#39;ve got your submission. Your reference is <strong>${ssrInterpolate(__props.reference)}</strong>. We’ll review your photos and get back to you by email with an offer. You don’t need to send anything yet – we’ll confirm the details first.</p></div></div></div></main>`);
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

//# sourceMappingURL=ThankYou-D5GjAv2e.js.map