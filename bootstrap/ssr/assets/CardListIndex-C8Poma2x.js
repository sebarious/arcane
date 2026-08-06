import { n as Footer_default, t as Nav_default } from "./Nav-xTq28nz2.js";
import { i as Orbs_default, n as HeroSparkles_default, r as HeroBG_default, t as FloatingRings_default } from "./FloatingRings-B4R2VTDJ.js";
import { t as svg_default } from "./svg-CfHhK0Wa.js";
import { t as Arcane_pack_default } from "./Arcane_pack-B7XfUZdn.js";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { computed, createBlock, createVNode, defineComponent, mergeProps, openBlock, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Components/CardLists/PackThumb.vue?vue&type=script&setup=true&lang.ts
var PACK_WIDTH = 108;
var PackThumb_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "PackThumb",
	__ssrInlineRender: true,
	props: {
		topCardImage1: {},
		topCardImage2: {}
	},
	setup(__props) {
		Math.max(Math.round(PACK_WIDTH * 88 / 63), Math.round(PACK_WIDTH * (350 / 330))) + 16;
		const packStyle = computed(() => ({
			left: "61px",
			top: "8px",
			width: "108px",
			height: "151px"
		}));
		const leftCardStyle = computed(() => ({
			left: "0px",
			top: "50%",
			width: "82px",
			height: "115px",
			transform: "translateY(-50%) rotate(-12deg)"
		}));
		const rightCardStyle = computed(() => ({
			left: "149px",
			top: "50%",
			width: "82px",
			height: "115px",
			transform: "translateY(-50%) rotate(12deg)"
		}));
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({
				class: "relative shrink-0",
				style: {
					width: "231px",
					height: "167px"
				}
			}, _attrs))}>`);
			if (__props.topCardImage1) _push(`<div class="absolute overflow-hidden rounded-[4px] shadow-lg" style="${ssrRenderStyle(leftCardStyle.value)}"><img${ssrRenderAttr("src", __props.topCardImage1)} alt="" class="w-full h-full object-cover select-none" draggable="false" loading="lazy"></div>`);
			else _push(`<!---->`);
			if (__props.topCardImage2) _push(`<div class="absolute overflow-hidden rounded-[4px] shadow-lg" style="${ssrRenderStyle(rightCardStyle.value)}"><img${ssrRenderAttr("src", __props.topCardImage2)} alt="" class="w-full h-full object-cover select-none" draggable="false" loading="lazy"></div>`);
			else _push(`<!---->`);
			_push(`<div class="absolute overflow-hidden rounded-[6px] shadow-xl" style="${ssrRenderStyle(packStyle.value)}"><img${ssrRenderAttr("src", unref(Arcane_pack_default))} alt="Arcane Mystery Pack" class="w-full h-full object-cover select-none" draggable="false" loading="lazy"></div></div>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/CardLists/PackThumb.vue
var _sfc_setup$3 = PackThumb_vue_vue_type_script_setup_true_lang_default.setup;
PackThumb_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/CardLists/PackThumb.vue");
	return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
var PackThumb_default = PackThumb_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/CardLists/Card.vue?vue&type=script&setup=true&lang.ts
var Card_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Card",
	__ssrInlineRender: true,
	props: { batch: {} },
	setup(__props) {
		const blurStyle = { backgroundImage: `url("data:image/svg+xml,${encodeURIComponent(`
  <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
    <rect x="0" y="0" height="100%" width="100%" fill="url(#grad)" opacity="1" />
    <defs>
      <radialGradient id="grad" gradientUnits="userSpaceOnUse" cx="0" cy="0" r="10"
        gradientTransform="matrix(0 -5 5 0 50 50)">
        <stop stop-color="rgba(123,79,233,0.25098)" offset="0" />
        <stop stop-color="rgba(62,40,117,0.12549)" offset="0.4" />
        <stop stop-color="rgba(0,0,0,0)" offset="0.8" />
      </radialGradient>
    </defs>
  </svg>
`)}")` };
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "bg-[#13101e] relative rounded-[8px]" }, _attrs))}><div class="content-stretch flex flex-col items-start overflow-clip relative rounded-[inherit] size-full"><div class="flex h-[190px] items-center justify-center overflow-clip relative shrink-0 w-full" style="${ssrRenderStyle({ backgroundImage: "linear-gradient(90deg, rgba(123, 79, 233, 0.2) 0%, rgba(0, 0, 0, 0) 100%), linear-gradient(90deg, rgb(6, 6, 11) 0%, rgb(6, 6, 11) 100%)" })}"><div class="-translate-x-1/2 -translate-y-1/2 absolute blur-[24px] left-1/2 size-[100px] top-1/2" style="${ssrRenderStyle(blurStyle)}"></div>`);
			if (__props.batch.store.logo) _push(`<img${ssrRenderAttr("src", __props.batch.store.logo)}${ssrRenderAttr("alt", __props.batch.store.name)} class="absolute left-3 top-3 size-[50px] rounded-[6px] bg-[#06060b] object-contain p-0.5 border border-[rgba(220,193,117,0.15)]" loading="lazy">`);
			else _push(`<!---->`);
			_push(ssrRenderComponent(PackThumb_default, {
				"top-card-image1": __props.batch.top_card_1_image,
				"top-card-image2": __props.batch.top_card_2_image
			}, null, _parent));
			_push(`</div><div class="relative shrink-0 w-full"><div class="content-stretch flex flex-col gap-[12px] items-start p-[20px] relative size-full"><div class="content-stretch flex flex-col items-start relative shrink-0 w-full gap-1"><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold leading-[normal] relative shrink-0 text-[18px] text-white w-full">${ssrInterpolate(__props.batch.type_label)}</p><p class="font-[&#39;Jost&#39;,sans-serif] text-xs text-[#a3a3a3]">${ssrInterpolate(__props.batch.store.name)} · ${ssrInterpolate(__props.batch.remaining_packs)}/${ssrInterpolate(__props.batch.pack_count)} packs left</p></div><div class="content-stretch flex items-center justify-between relative shrink-0 w-full">`);
			_push(ssrRenderComponent(unref(Link), {
				href: `/${__props.batch.store.slug}/${__props.batch.id}`,
				class: "content-stretch flex gap-[4px] items-center relative shrink-0"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<p class="font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[11px] uppercase whitespace-nowrap"${_scopeId}> View Cards</p><div class="relative shrink-0 size-[12px]"${_scopeId}><svg class="absolute block inset-0 size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 12 12"${_scopeId}><g id="arrow-right"${_scopeId}><path${ssrRenderAttr("d", unref(svg_default).p278a3600)} id="Vector" stroke="var(--stroke-0, #7B4FE9)" stroke-linecap="round" stroke-width="2"${_scopeId}></path></g></svg></div>`);
					else return [createVNode("p", { class: "font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[11px] uppercase whitespace-nowrap" }, " View Cards"), createVNode("div", { class: "relative shrink-0 size-[12px]" }, [(openBlock(), createBlock("svg", {
						class: "absolute block inset-0 size-full",
						fill: "none",
						preserveAspectRatio: "none",
						viewBox: "0 0 12 12"
					}, [createVNode("g", { id: "arrow-right" }, [createVNode("path", {
						d: unref(svg_default).p278a3600,
						id: "Vector",
						stroke: "var(--stroke-0, #7B4FE9)",
						"stroke-linecap": "round",
						"stroke-width": "2"
					}, null, 8, ["d"])])]))])];
				}),
				_: 1
			}, _parent));
			_push(`</div></div></div></div><div aria-hidden class="absolute border border-[rgba(220,193,117,0.1)] border-solid inset-0 pointer-events-none rounded-[8px]"></div></div>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/CardLists/Card.vue
var _sfc_setup$2 = Card_vue_vue_type_script_setup_true_lang_default.setup;
Card_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/CardLists/Card.vue");
	return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
var Card_default = Card_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/CardLists/Hero.vue?vue&type=script&setup=true&lang.ts
var Hero_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Hero",
	__ssrInlineRender: true,
	props: { batches: {} },
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "relative shrink-0 w-full" }, _attrs))}><div class="bg-clip-padding border-0 border-[transparent] border-solid relative size-full">`);
			_push(ssrRenderComponent(HeroBG_default, null, null, _parent));
			_push(ssrRenderComponent(HeroSparkles_default, null, null, _parent));
			_push(ssrRenderComponent(FloatingRings_default, null, null, _parent));
			_push(`<div class="lg:absolute content-stretch flex flex-col gap-[24px] lg:h-[326px] items-center justify-center left-0 pb-[48px] pt-[80px] px-8 lg:px-16 top-[-15px] w-full"><div class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-start relative shrink-0 w-full"><h1 class="font-[&#39;Cinzel&#39;,sans-serif] font-bold leading-[0] min-w-full relative shrink-0 text-[64px] text-white w-[min-content]"><span class="leading-[normal]">Browse </span><span class="bg-clip-text leading-[normal] text-[transparent]" style="${ssrRenderStyle({ backgroundImage: "linear-gradient(90deg, rgb(76, 29, 149) 0%, rgb(124, 58, 237) 14.286%, rgb(168, 85, 247) 28.571%, rgb(192, 132, 252) 42.857%, rgb(221, 214, 254) 57.143%, rgb(168, 85, 247) 71.429%, rgb(124, 58, 237) 85.714%, rgb(76, 29, 149) 100%), linear-gradient(90deg, rgb(255, 255, 255) 0%, rgb(255, 255, 255) 100%)" })}"> Card Lists </span></h1><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] opacity-60 relative shrink-0 text-[#a3a3a3] text-[18px]"> Every mystery pack batch on offer, across every store, in one place.</p></div></div><div class="relative flex flex-col gap-[32px] items-center justify-center pb-[80px] lg:pt-[311px] lg:pt-[380px] px-8 lg:px-16 w-full"><div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full"><!--[-->`);
			ssrRenderList(__props.batches, (batch) => {
				_push(ssrRenderComponent(Card_default, {
					key: batch.id,
					batch
				}, null, _parent));
			});
			_push(`<!--]--></div>`);
			if (__props.batches.length === 0) _push(`<p class="font-[&#39;Jost&#39;,sans-serif] text-[#a3a3a3] text-sm py-12"> No batches are on offer right now — check back soon. </p>`);
			else _push(`<!---->`);
			_push(`</div></div></div>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/CardLists/Hero.vue
var _sfc_setup$1 = Hero_vue_vue_type_script_setup_true_lang_default.setup;
Hero_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/CardLists/Hero.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var Hero_default = Hero_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Pages/Storefront/CardListIndex.vue?vue&type=script&setup=true&lang.ts
var CardListIndex_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "CardListIndex",
	__ssrInlineRender: true,
	props: { batches: {} },
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Card Lists" }, null, _parent));
			_push(`<main class="bg-[#06060b] content-stretch flex flex-col items-start relative size-full overflow-x-hidden">`);
			_push(ssrRenderComponent(Orbs_default, null, null, _parent));
			_push(`<div class="relative shrink-0"><div class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-[64px] py-[20px] relative size-full"><div class="h-[49px] relative shrink-0">`);
			_push(ssrRenderComponent(Nav_default, null, null, _parent));
			_push(`</div></div></div><div class="relative shrink-0 w-full"><div class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start relative size-full">`);
			_push(ssrRenderComponent(Hero_default, { batches: __props.batches }, null, _parent));
			_push(`</div></div></main>`);
			_push(ssrRenderComponent(Footer_default, null, null, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/ts/Pages/Storefront/CardListIndex.vue
var _sfc_setup = CardListIndex_vue_vue_type_script_setup_true_lang_default.setup;
CardListIndex_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Storefront/CardListIndex.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var CardListIndex_default = CardListIndex_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { CardListIndex_default as default };

//# sourceMappingURL=CardListIndex-C8Poma2x.js.map