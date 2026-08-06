import { n as Footer_default, t as Nav_default } from "./Nav-xTq28nz2.js";
import { Head } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderComponent } from "vue/server-renderer";
import { computed, defineComponent, ref, unref, useSSRContext } from "vue";
//#region resources/ts/Pages/Seller/Pending.vue?vue&type=script&setup=true&lang.ts
var Pending_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Pending",
	__ssrInlineRender: true,
	props: {
		affiliateCode: {},
		bonusPercentage: {}
	},
	setup(__props) {
		const props = __props;
		const bonusLabel = computed(() => `${Math.round(props.bonusPercentage * 100)}%`);
		const affiliateCopied = ref(false);
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Store setup in progress" }, null, _parent));
			_push(`<main class="bg-[#0d0b14] overflow-x-hidden"><div class="relative shrink-0"><div class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative size-full"><div class="h-[49px] relative shrink-0">`);
			_push(ssrRenderComponent(Nav_default, null, null, _parent));
			_push(`</div></div></div><div class="relative shrink-0 w-full"><div class="content-stretch flex flex-col gap-[40px] items-center justify-center pb-[120px] pt-[80px] px-8 lg:px-[64px] relative size-full"><div class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center relative shrink-0 text-center mx-auto max-w-[560px]"><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white"><span class="leading-[normal]">Almost</span><span class="leading-[normal] text-[#c9a84c]"> there</span></p><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px]"> Your dashboard unlocks as soon as our team finishes setting up your store. If you haven&#39;t already, check your email for the onboarding form we sent — completing it helps us get you live faster. Questions in the meantime? Reach us at <a href="mailto:support@arcanepacks.com" class="text-[#c9a84c] underline">support@arcanepacks.com</a>. </p></div>`);
			if (__props.affiliateCode) _push(`<div class="mx-auto max-w-[560px] w-full flex flex-col items-center gap-[12px]"><div class="bg-[#13101e] border border-[rgba(124,58,237,0.35)] rounded-[10px] p-[16px] flex items-center gap-[16px] w-full"><div class="flex-1 min-w-0"><p class="font-[&#39;Jost&#39;,sans-serif] font-semibold text-[11px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide"> Your affiliate code </p><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[20px] text-[#c9a84c] tracking-wide">${ssrInterpolate(__props.affiliateCode)}</p></div><button type="button" class="shrink-0 text-xs font-[&#39;Jost&#39;,sans-serif] font-semibold uppercase tracking-wide px-4 py-2 rounded-[4px] border border-[#3d2f6e] text-white hover:border-[#c9a84c] transition-colors">${ssrInterpolate(affiliateCopied.value ? "Copied!" : "Copy")}</button></div><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-relaxed text-[#a3a3a3] text-[14px] text-center"> You don&#39;t have to wait to start using it. Share this code with your customers — when they quote it on our <span class="text-white">Sell to Us</span> flow, they get <span class="text-[#c9a84c] font-semibold">${ssrInterpolate(bonusLabel.value)} more</span> on their offer, and your store earns that same ${ssrInterpolate(bonusLabel.value)} back as store credit — automatically applied to your invoice(s) once you&#39;re live. </p></div>`);
			else _push(`<!---->`);
			_push(`</div></div></main>`);
			_push(ssrRenderComponent(Footer_default, null, null, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/ts/Pages/Seller/Pending.vue
var _sfc_setup = Pending_vue_vue_type_script_setup_true_lang_default.setup;
Pending_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/Pending.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Pending_default = Pending_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Pending_default as default };

//# sourceMappingURL=Pending-Djg2XsJ9.js.map