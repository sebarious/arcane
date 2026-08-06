import { n as Footer_default, t as Nav_default } from "./Nav-CpsP5fGk.js";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
import { computed, createTextVNode, defineComponent, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Pages/Marketing/AffiliateProgram.vue?vue&type=script&setup=true&lang.ts
var AffiliateProgram_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "AffiliateProgram",
	__ssrInlineRender: true,
	props: { bonusPercentage: {} },
	setup(__props) {
		const props = __props;
		const bonusLabel = computed(() => `${Math.round(props.bonusPercentage * 100)}%`);
		const steps = [
			{
				title: "Find a store's code",
				body: "Every store on Arcane has its own affiliate code, shown right on their public profile page. Ask your favourite store for theirs, or grab it yourself if you run a store."
			},
			{
				title: "Quote it on Sell to Us",
				body: `Head to the Sell to Us page, pop the code into the affiliate box at the top, and hit apply. We’ll confirm it's valid straight away.`
			},
			{
				title: "Get a better offer",
				body: `Every card you add gets a ${bonusLabel.value} uplift on its offer automatically — you'll see the boosted price right alongside the original.`
			}
		];
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Affiliate Program" }, null, _parent));
			_push(`<main class="bg-[#0d0b14] overflow-x-hidden min-h-screen"><div class="relative shrink-0"><div class="flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative w-full"><div class="h-[49px] relative shrink-0 w-full">`);
			_push(ssrRenderComponent(Nav_default, null, null, _parent));
			_push(`</div></div></div><div class="px-8 lg:px-[64px] pt-[60px] pb-[40px] max-w-4xl mx-auto"><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[40px] lg:text-[56px] text-white leading-tight"> Affiliate <span class="text-[#c9a84c]">Program</span></p><p class="font-[&#39;Jost&#39;,sans-serif] font-normal text-[#a3a3a3] text-[18px] mt-[16px] max-w-2xl leading-relaxed"> Every store on Arcane has its own shareable affiliate code. Quote it when you sell cards to us — whether you&#39;re one of their customers or the store itself — and get <span class="text-[#c9a84c] font-semibold">${ssrInterpolate(bonusLabel.value)} more</span> on your offer, automatically. </p></div><div class="px-8 lg:px-[64px] pb-[60px] max-w-4xl mx-auto"><div class="grid sm:grid-cols-3 gap-[20px]"><!--[-->`);
			ssrRenderList(steps, (step, i) => {
				_push(`<div class="bg-[#13101e] border border-[rgba(201,168,76,0.2)] rounded-[12px] p-[24px] relative"><span class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[#c9a84c]/40 text-[32px] leading-none"> 0${ssrInterpolate(i + 1)}</span><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[17px] text-white mt-[12px] mb-[8px]">${ssrInterpolate(step.title)}</p><p class="font-[&#39;Jost&#39;,sans-serif] text-[14px] text-[#a3a3a3] leading-relaxed">${ssrInterpolate(step.body)}</p></div>`);
			});
			_push(`<!--]--></div></div><div class="px-8 lg:px-[64px] pb-[120px] max-w-4xl mx-auto"><div class="bg-[#1a1628] border border-[rgba(124,58,237,0.35)] rounded-[16px] p-[32px] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-[20px]"><div><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[20px] text-white"> Got a code? Put it to work. </p><p class="font-[&#39;Jost&#39;,sans-serif] text-[14px] text-[#a3a3a3] mt-[6px]"> Or browse our stores to find one first. </p></div><div class="flex gap-[12px] shrink-0">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/stores",
				class: "px-6 py-3 rounded-[4px] border border-[#3d2f6e] text-white text-sm font-['Jost',sans-serif] font-semibold uppercase tracking-wide hover:border-[#c9a84c] transition-colors"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(` Find a store `);
					else return [createTextVNode(" Find a store ")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), {
				href: "/sell",
				class: "px-6 py-3 rounded-[4px] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide text-[#0d0b14]",
				style: { "background-image": "linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%)" }
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(` Sell to us `);
					else return [createTextVNode(" Sell to us ")];
				}),
				_: 1
			}, _parent));
			_push(`</div></div></div></main>`);
			_push(ssrRenderComponent(Footer_default, null, null, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/ts/Pages/Marketing/AffiliateProgram.vue
var _sfc_setup = AffiliateProgram_vue_vue_type_script_setup_true_lang_default.setup;
AffiliateProgram_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Marketing/AffiliateProgram.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var AffiliateProgram_default = AffiliateProgram_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { AffiliateProgram_default as default };

//# sourceMappingURL=AffiliateProgram-KmyF7a6f.js.map