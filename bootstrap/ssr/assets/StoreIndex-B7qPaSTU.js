import { n as Footer_default, t as Nav_default } from "./Nav-xTq28nz2.js";
import { i as Orbs_default, n as HeroSparkles_default, r as HeroBG_default, t as FloatingRings_default } from "./FloatingRings-B4R2VTDJ.js";
import { t as svg_default } from "./svg-CfHhK0Wa.js";
import { t as LivePool_default } from "./LivePool-BFh-Gu-S.js";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { createBlock, createVNode, defineComponent, mergeProps, openBlock, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Components/Stores/Card.vue?vue&type=script&setup=true&lang.ts
var Card_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Card",
	__ssrInlineRender: true,
	props: { store: {} },
	setup(__props) {
		const blurStyle = { backgroundImage: `url("data:image/svg+xml,${encodeURIComponent(`
  <svg viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
    <rect x="0" y="0" height="100%" width="100%" fill="url(#grad)" opacity="1" />
    <defs>
      <radialGradient id="grad" gradientUnits="userSpaceOnUse" cx="0" cy="0" r="10"
        gradientTransform="matrix(0 -4 4 0 40 40)">
        <stop stop-color="rgba(123,79,233,0.25098)" offset="0" />
        <stop stop-color="rgba(62,40,117,0.12549)" offset="0.4" />
        <stop stop-color="rgba(0,0,0,0)" offset="0.8" />
      </radialGradient>
    </defs>
  </svg>
`)}")` };
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "bg-[#13101e] flex-[1_0_0] min-w-px relative rounded-[8px]" }, _attrs))}><div class="content-stretch flex flex-col items-start overflow-clip relative rounded-[inherit] size-full"><div class="content-stretch flex h-[151px] items-center justify-center overflow-clip relative shrink-0 w-full" style="${ssrRenderStyle({ backgroundImage: "linear-gradient(90deg, rgba(123, 79, 233, 0.2) 0%, rgba(0, 0, 0, 0) 100%), linear-gradient(90deg, rgb(6, 6, 11) 0%, rgb(6, 6, 11) 100%)" })}"><div class="h-[151px] relative shrink-0 w-[270px]"><svg class="absolute block inset-0 size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 270 151"><g id="atom" opacity="0.1"><path${ssrRenderAttr("d", unref(svg_default).p14ec8e80)} id="Vector" stroke="var(--stroke-0, #7B4FE9)" strokeLinecap="round" strokeWidth="2"></path></g></svg></div><div class="-translate-x-1/2 -translate-y-1/2 absolute blur-[20px] left-1/2 size-[80px] top-1/2" style="${ssrRenderStyle(blurStyle)}"></div>`);
			if (__props.store.logo) _push(`<img${ssrRenderAttr("src", __props.store.logo)}${ssrRenderAttr("alt", __props.store.name)} class="-translate-x-1/2 -translate-y-1/2 absolute left-1/2 size-[100px] top-1/2 rounded-full object-cover" loading="lazy">`);
			else _push(`<!---->`);
			_push(`</div><div class="relative shrink-0 w-full"><div class="content-stretch flex flex-col gap-[12px] items-start p-[20px] relative size-full"><div class="content-stretch flex flex-col items-start relative shrink-0 w-full"><p class="[word-break:break-word] font-[&#39;Cinzel&#39;,sans-serif] font-bold leading-[normal] relative shrink-0 text-[18px] text-white w-full">${ssrInterpolate(__props.store.name)}</p></div><div class="content-stretch flex items-center justify-between relative shrink-0 w-full">`);
			_push(ssrRenderComponent(unref(Link), {
				href: `/${__props.store.slug}`,
				class: "content-stretch flex gap-[4px] items-center relative shrink-0"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<p class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[11px] uppercase whitespace-nowrap"${_scopeId}> Visit Store</p><div class="relative shrink-0 size-[12px]"${_scopeId}><svg class="absolute block inset-0 size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 12 12"${_scopeId}><g id="arrow-right"${_scopeId}><path${ssrRenderAttr("d", unref(svg_default).p278a3600)} id="Vector" stroke="var(--stroke-0, #7B4FE9)" strokeLinecap="round" strokeWidth="2"${_scopeId}></path></g></svg></div>`);
					else return [createVNode("p", { class: "[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[11px] uppercase whitespace-nowrap" }, " Visit Store"), createVNode("div", { class: "relative shrink-0 size-[12px]" }, [(openBlock(), createBlock("svg", {
						class: "absolute block inset-0 size-full",
						fill: "none",
						preserveAspectRatio: "none",
						viewBox: "0 0 12 12"
					}, [createVNode("g", { id: "arrow-right" }, [createVNode("path", {
						d: unref(svg_default).p278a3600,
						id: "Vector",
						stroke: "var(--stroke-0, #7B4FE9)",
						strokeLinecap: "round",
						strokeWidth: "2"
					}, null, 8, ["d"])])]))])];
				}),
				_: 1
			}, _parent));
			_push(`</div></div></div></div><div aria-hidden class="absolute border border-[rgba(220,193,117,0.1)] border-solid inset-0 pointer-events-none rounded-[8px]"></div></div>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Stores/Card.vue
var _sfc_setup$2 = Card_vue_vue_type_script_setup_true_lang_default.setup;
Card_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Stores/Card.vue");
	return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
var Card_default = Card_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/Stores/Hero.vue?vue&type=script&setup=true&lang.ts
var Hero_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Hero",
	__ssrInlineRender: true,
	props: { stores: {} },
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "lg:h-[641px] lg:max-h-[704.7000122070312px] relative shrink-0 w-full" }, _attrs))}><div class="bg-clip-padding border-0 border-[transparent] border-solid relative size-full">`);
			_push(ssrRenderComponent(HeroBG_default, null, null, _parent));
			_push(ssrRenderComponent(HeroSparkles_default, null, null, _parent));
			_push(ssrRenderComponent(FloatingRings_default, null, null, _parent));
			_push(`<div class="absolute content-stretch flex flex-col h-[40px] items-start left-[535px] top-[703px] w-px"><div class="absolute bg-gradient-to-b from-[rgba(255,185,0,0.35)] h-[5.363px] left-0 to-[rgba(0,0,0,0)] top-0 w-px"></div></div><div class="lg:absolute content-stretch flex flex-col gap-[24px] lg:h-[326px] items-center justify-center left-0 pb-[48px] pt-[80px] px-8 lg:px-16 top-[-15px] w-full"><div class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-start relative shrink-0 w-full"><h1 class="font-[&#39;Cinzel&#39;,sans-serif] font-bold leading-[0] min-w-full relative shrink-0 text-[64px] text-white w-[min-content]"><span class="leading-[normal]">Browse </span><span class="bg-clip-text leading-[normal] text-[transparent]" style="${ssrRenderStyle({ backgroundImage: "linear-gradient(90deg, rgb(76, 29, 149) 0%, rgb(124, 58, 237) 14.286%, rgb(168, 85, 247) 28.571%, rgb(192, 132, 252) 42.857%, rgb(221, 214, 254) 57.143%, rgb(168, 85, 247) 71.429%, rgb(124, 58, 237) 85.714%, rgb(76, 29, 149) 100%), linear-gradient(90deg, rgb(255, 255, 255) 0%, rgb(255, 255, 255) 100%)" })}"> Stores </span></h1><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] opacity-60 relative shrink-0 text-[#a3a3a3] text-[18px] whitespace-nowrap"> Find your next pull from verified local stores.</p></div></div><div class="lg:absolute content-stretch flex flex-col gap-[32px] lg:h-[378px] items-center justify-center left-0 pb-[80px] px-8 lg:px-16 top-[311px] w-full"><div class="content-stretch space-y-8 lg:space-y-0 lg:flex lg:gap-[24px] lg:items-start relative lg:shrink-0 w-full"><!--[-->`);
			ssrRenderList(__props.stores, (store) => {
				_push(ssrRenderComponent(Card_default, {
					key: store.id,
					store
				}, null, _parent));
			});
			_push(`<!--]--></div></div></div></div>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Stores/Hero.vue
var _sfc_setup$1 = Hero_vue_vue_type_script_setup_true_lang_default.setup;
Hero_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Stores/Hero.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var Hero_default = Hero_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Pages/Storefront/StoreIndex.vue?vue&type=script&setup=true&lang.ts
var StoreIndex_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "StoreIndex",
	__ssrInlineRender: true,
	props: {
		stores: {},
		whatsInThePool: {}
	},
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Stores" }, null, _parent));
			_push(`<main class="bg-[#06060b] content-stretch flex flex-col items-start relative size-full overflow-x-hidden">`);
			_push(ssrRenderComponent(Orbs_default, null, null, _parent));
			_push(`<div class="relative shrink-0"><div class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-[64px] py-[20px] relative size-full"><div class="h-[49px] relative shrink-0">`);
			_push(ssrRenderComponent(Nav_default, null, null, _parent));
			_push(`</div></div></div><div class="relative shrink-0 w-full"><div class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start relative size-full">`);
			_push(ssrRenderComponent(Hero_default, { stores: __props.stores }, null, _parent));
			_push(ssrRenderComponent(LivePool_default, { pulls: __props.whatsInThePool }, null, _parent));
			_push(`</div></div></main>`);
			_push(ssrRenderComponent(Footer_default, null, null, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/ts/Pages/Storefront/StoreIndex.vue
var _sfc_setup = StoreIndex_vue_vue_type_script_setup_true_lang_default.setup;
StoreIndex_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Storefront/StoreIndex.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var StoreIndex_default = StoreIndex_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { StoreIndex_default as default };

//# sourceMappingURL=StoreIndex-B7qPaSTU.js.map