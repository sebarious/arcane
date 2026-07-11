import { n as Footer_default, t as Nav_default } from "./Nav-Bb-_Zprs.js";
import { a as Orbs_default, i as LivePool_default, n as HeroSparkles_default, r as HeroBG_default, t as FloatingRings_default } from "./FloatingRings-DGLKUBIF.js";
import { t as HoloText_default } from "./HoloText-C1kAbqXE.js";
import { t as PullsSlider_default } from "./PullsSlider-CAM7cC_x.js";
import { Link } from "@inertiajs/vue3";
import { ssrGetDirectiveProps, ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderComponent, ssrRenderList, ssrRenderStyle, ssrRenderVNode } from "vue/server-renderer";
import { computed, createTextVNode, createVNode, defineComponent, mergeProps, onMounted, onUnmounted, ref, resolveDirective, resolveDynamicComponent, unref, useSSRContext, withCtx } from "vue";
import { Eye, Package, Shield } from "lucide-vue-next";
//#region resources/ts/Assets/Arcane_pack.png
var Arcane_pack_default = "/build/assets/Arcane_pack-rTmMKIQw.png";
//#endregion
//#region resources/ts/Assets/Charizard.png
var Charizard_default = "/build/assets/Charizard-DXJpWvGQ.png";
//#endregion
//#region resources/ts/Assets/Umbreon.png
var Umbreon_default = "/build/assets/Umbreon-CW-O5A1-.png";
//#endregion
//#region resources/ts/Assets/Pikachu.png
var Pikachu_default = "/build/assets/Pikachu-DQCTH0jM.png";
//#endregion
//#region resources/ts/Assets/Lugia.png
var Lugia_default = "/build/assets/Lugia-C3KNsBl2.png";
//#endregion
//#region resources/ts/Assets/Mew.png
var Mew_default = "/build/assets/Mew-DY9bLn_u.png";
//#endregion
//#region resources/ts/Assets/Rayquaza.png
var Rayquaza_default = "/build/assets/Rayquaza-4Fu-xvhf.png";
//#endregion
//#region resources/ts/Components/Homepage/data.ts
var steps = [
	{
		num: "01",
		icon: Shield,
		title: "Authenticated Singles",
		desc: "Every card in our pool is Near-mint condition, verified authentic, toploaded from the moment it arrives."
	},
	{
		num: "02",
		icon: Package,
		title: "Sealed Mystery Pack",
		desc: "We pull one card blind from the live pool and seal it into your pack. You won't know which card until you open it — that's the magic."
	},
	{
		num: "03",
		icon: Eye,
		title: "Live Pool Transparency",
		desc: "See exactly which cards are still in your local shop's pool before you buy. No blind guessing — full odds visibility, always."
	}
];
var tickerItems = [
	"Near Mint Only",
	"Live Card Pool",
	"Toploaded Hits",
	"Authenticated Singles",
	"Mystery Packs",
	"Full Transparency",
	"Hit Guaranteed"
];
var CARD_IMAGES = [
	Charizard_default,
	Umbreon_default,
	Pikachu_default,
	Lugia_default,
	Mew_default,
	Rayquaza_default
];
//#endregion
//#region resources/ts/Components/Homepage/FloatingPack.vue?vue&type=script&setup=true&lang.ts
var FloatingPack_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "FloatingPack",
	__ssrInlineRender: true,
	props: {
		mouseX: {},
		mouseY: {}
	},
	setup(__props) {
		const props = __props;
		const PACK_TIERS = [
			{
				name: "Sapphire",
				qty: "x125",
				color: "#93c5fd",
				glow: "rgba(59,130,246,0.7)",
				bg: "linear-gradient(135deg, #0c1f6e 0%, #1a4db5 100%)",
				border: "rgba(96,165,250,0.5)"
			},
			{
				name: "Ruby",
				qty: "x250",
				color: "#fca5a5",
				glow: "rgba(220,38,38,0.7)",
				bg: "linear-gradient(135deg, #6e0c0c 0%, #b91c1c 100%)",
				border: "rgba(248,113,113,0.5)"
			},
			{
				name: "Diamond",
				qty: "x500",
				color: "#e8f0ff",
				glow: "rgba(200,220,255,0.8)",
				bg: "rgba(255,255,255,0.18)",
				border: "rgba(210,230,255,0.55)"
			}
		];
		const cardIndex = ref(0);
		const rotationY = ref(0);
		const ringRotation = ref(0);
		let flipInterval = null;
		let ringInterval = null;
		onMounted(() => {
			flipInterval = window.setInterval(() => {
				rotationY.value += 180;
				window.setTimeout(() => {
					cardIndex.value = (cardIndex.value + 1) % CARD_IMAGES.length;
				}, 450);
			}, 3e3);
			ringInterval = window.setInterval(() => {
				ringRotation.value += 1;
			}, 16);
		});
		onUnmounted(() => {
			if (flipInterval !== null) window.clearInterval(flipInterval);
			if (ringInterval !== null) window.clearInterval(ringInterval);
		});
		const currentCard = computed(() => CARD_IMAGES[cardIndex.value]);
		const tiltX = computed(() => props.mouseY * -12);
		const tiltY = computed(() => props.mouseX * 16);
		const wrapperStyle = computed(() => ({
			transform: `
    translateY(${Math.sin(Date.now() / 700) * 6}px)
    rotateX(${tiltX.value}deg)
    rotateY(${tiltY.value}deg)
  `,
			transformStyle: "preserve-3d"
		}));
		const flipContainerStyle = computed(() => ({
			width: "330px",
			aspectRatio: "63 / 88",
			position: "relative",
			transformStyle: "preserve-3d",
			transition: "transform 0.9s cubic-bezier(0.16, 1, 0.3, 1)",
			transform: `rotateY(${rotationY.value}deg)`
		}));
		const sharedFaceStyle = {
			position: "absolute",
			inset: "0",
			borderRadius: "10px",
			overflow: "hidden",
			backfaceVisibility: "hidden",
			boxShadow: "0 0 40px rgba(124,58,237,0.45), 0 30px 60px rgba(0,0,0,0.75)"
		};
		const frontFaceStyle = {
			...sharedFaceStyle,
			transform: "rotateY(0deg)"
		};
		const backFaceStyle = {
			...sharedFaceStyle,
			transform: "rotateY(180deg)"
		};
		const ringStyle = computed(() => ({
			transform: `rotate(${ringRotation.value}deg)`,
			transition: "transform 16ms linear"
		}));
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "flex flex-col items-center justify-center h-[520px]" }, _attrs))}><div class="relative flex flex-col items-center justify-center" style="${ssrRenderStyle({ perspective: "1200px" })}"><div class="absolute w-[420px] h-[420px] rounded-full blur-3xl" style="${ssrRenderStyle({ background: "radial-gradient(circle, rgba(124,58,237,0.22) 0%, rgba(220,193,117,0.12) 35%, transparent 72%)" })}"></div><div class="absolute w-[380px] h-[380px] rounded-full border border-[#DCC175]/10" style="${ssrRenderStyle(ringStyle.value)}"></div><div class="relative z-10 transition-transform duration-200" style="${ssrRenderStyle(wrapperStyle.value)}"><div class="relative" style="${ssrRenderStyle(flipContainerStyle.value)}"><div style="${ssrRenderStyle(frontFaceStyle)}"><img${ssrRenderAttr("src", unref(Arcane_pack_default))} alt="Arcane Mystery Pack" class="w-full h-full object-cover select-none" draggable="false"></div><div style="${ssrRenderStyle(backFaceStyle)}"><img${ssrRenderAttr("src", currentCard.value)} alt="Pokémon card" class="w-full h-full object-cover select-none" draggable="false"></div></div></div><div class="flex gap-3 mt-8"><!--[-->`);
			ssrRenderList(PACK_TIERS, (tier) => {
				_push(`<div class="px-4 py-3 backdrop-blur-xl flex flex-col items-center min-w-[96px] transition-transform duration-300 hover:scale-105" style="${ssrRenderStyle({
					borderRadius: "6px",
					background: tier.bg,
					border: `1px solid ${tier.border}`,
					boxShadow: `0 4px 24px rgba(0,0,0,0.55), 0 0 18px ${tier.glow}`
				})}"><span class="text-[11px] font-bold tracking-[0.12em] uppercase" style="${ssrRenderStyle({
					color: tier.color,
					fontFamily: "Cinzel, serif",
					textShadow: `0 0 10px ${tier.glow}`
				})}">${ssrInterpolate(tier.name)}</span><span class="text-[9px] tracking-widest mt-1" style="${ssrRenderStyle({
					color: `${tier.color}90`,
					fontFamily: "Jost, sans-serif"
				})}">${ssrInterpolate(tier.qty)}</span></div>`);
			});
			_push(`<!--]--></div></div></div>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Homepage/FloatingPack.vue
var _sfc_setup$6 = FloatingPack_vue_vue_type_script_setup_true_lang_default.setup;
FloatingPack_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/FloatingPack.vue");
	return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
var FloatingPack_default = FloatingPack_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/Homepage/SplitWords.vue?vue&type=script&setup=true&lang.ts
var SplitWords_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "SplitWords",
	__ssrInlineRender: true,
	props: {
		text: {},
		className: {},
		delay: {},
		style: {}
	},
	setup(__props) {
		const props = __props;
		const className = computed(() => props.className ?? "");
		computed(() => props.delay ?? 0);
		const words = computed(() => props.text.split(" "));
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<span${ssrRenderAttrs(mergeProps({ class: ["inline-flex flex-wrap gap-x-[0.25em]", className.value] }, _attrs))}><!--[-->`);
			ssrRenderList(words.value, (word, i) => {
				_push(`<span class="overflow-hidden inline-block"><span class="inline-block" style="${ssrRenderStyle(__props.style)}">${ssrInterpolate(word)}</span></span>`);
			});
			_push(`<!--]--></span>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Homepage/SplitWords.vue
var _sfc_setup$5 = SplitWords_vue_vue_type_script_setup_true_lang_default.setup;
SplitWords_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/SplitWords.vue");
	return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
var SplitWords_default = SplitWords_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/Homepage/Hero.vue?vue&type=script&setup=true&lang.ts
var Hero_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Hero",
	__ssrInlineRender: true,
	props: { totalAvailableCards: {} },
	setup(__props) {
		const PACK_TIERS = [
			{
				name: "Sapphire",
				qty: "x125",
				color: "#93c5fd",
				glow: "rgba(59,130,246,0.7)",
				bg: "linear-gradient(135deg, #0c1f6e 0%, #1a4db5 100%)",
				border: "rgba(96,165,250,0.5)"
			},
			{
				name: "Ruby",
				qty: "x250",
				color: "#fca5a5",
				glow: "rgba(220,38,38,0.7)",
				bg: "linear-gradient(135deg, #6e0c0c 0%, #b91c1c 100%)",
				border: "rgba(248,113,113,0.5)"
			},
			{
				name: "Diamond",
				qty: "x500",
				color: "#e8f0ff",
				glow: "rgba(200,220,255,0.8)",
				bg: "rgba(255,255,255,0.18)",
				border: "rgba(210,230,255,0.55)"
			}
		];
		const sectionRef = ref(null);
		const scrollYProgress = ref(0);
		const onScroll = () => {
			const el = sectionRef.value;
			if (!el) return;
			const rect = el.getBoundingClientRect();
			const viewportHeight = window.innerHeight || 1;
			const start = rect.top - viewportHeight;
			const end = rect.bottom;
			const y = -start;
			const range = end - start || 1;
			scrollYProgress.value = Math.min(1, Math.max(0, y / range));
		};
		const isMobile = ref(false);
		const checkMobile = () => {
			isMobile.value = window.innerWidth < 1024;
		};
		onMounted(() => {
			checkMobile();
			window.addEventListener("resize", checkMobile);
			window.addEventListener("scroll", onScroll, { passive: true });
			onScroll();
		});
		onUnmounted(() => {
			window.removeEventListener("resize", checkMobile);
			window.removeEventListener("scroll", onScroll);
		});
		const contentStyle = computed(() => {
			if (isMobile.value) return {
				transform: "translateY(0)",
				opacity: 1
			};
			const y = isMobile.value ? 0 : 28 * scrollYProgress.value;
			const fadeStart = .3;
			const fadeEnd = 1;
			let opacity = 1;
			if (!isMobile.value && scrollYProgress.value > fadeStart) {
				const t = (scrollYProgress.value - fadeStart) / (fadeEnd - fadeStart || 1);
				opacity = 1 - Math.min(1, Math.max(0, t));
			}
			return {
				transform: `translateY(${y}%)`,
				opacity
			};
		});
		const rawX = ref(0);
		const rawY = ref(0);
		const infiniteChaseStyle = {
			backgroundImage: "linear-gradient(90deg,#4c1d95,#7c3aed,#a855f7,#c084fc,#ddd6fe,#a855f7,#7c3aed,#4c1d95)",
			backgroundSize: "300% 100%",
			WebkitBackgroundClip: "text",
			WebkitTextFillColor: "transparent",
			backgroundClip: "text",
			display: "inline-block"
		};
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<section${ssrRenderAttrs(mergeProps({
				ref_key: "sectionRef",
				ref: sectionRef,
				class: "relative sm:min-h-[95vh] sm:max-h-[95vh] flex items-center overflow-hidden"
			}, _attrs))}>`);
			_push(ssrRenderComponent(HeroBG_default, null, null, _parent));
			_push(ssrRenderComponent(HeroSparkles_default, null, null, _parent));
			_push(ssrRenderComponent(FloatingRings_default, null, null, _parent));
			_push(`<div class="relative z-10 w-full px-8 lg:px-16 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center pt-24 pb-16 lg:pt-14 lg:pb-0" style="${ssrRenderStyle(contentStyle.value)}"><div><div class="inline-flex items-center gap-2 px-3 py-1.5 border border-[#DCC175]/25 bg-[#DCC175]/8 mb-9" style="${ssrRenderStyle({ borderRadius: "3px" })}"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span><span class="text-[10px] text-[#DCC175]/70 tracking-[0.3em] uppercase" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}"> Live Pool — ${ssrInterpolate((__props.totalAvailableCards ?? 0).toLocaleString())} Cards Available </span></div><div class="mb-8" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 900,
				fontSize: "64px",
				lineHeight: 1.05
			})}"><div class="overflow-hidden mb-1">`);
			_push(ssrRenderComponent(SplitWords_default, {
				text: "One Card.",
				className: "text-white",
				delay: .25
			}, null, _parent));
			_push(`</div><div class="overflow-hidden whitespace-nowrap">`);
			_push(ssrRenderComponent(SplitWords_default, {
				text: "Infinite Chase.",
				delay: .5,
				style: infiniteChaseStyle
			}, null, _parent));
			_push(`</div></div><p class="text-base leading-relaxed mb-10 max-w-md" style="${ssrRenderStyle({
				fontFamily: "Jost, sans-serif",
				fontWeight: 300,
				color: "#e8e4f0"
			})}"> Authenticated, near‑mint Pokémon singles sealed into mystery packs — one toploaded hit per pack, every time. See the live card pool before you buy. </p><div class="flex gap-4 flex-wrap">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/stores",
				class: "px-8 py-3.5 bg-[#DCC175] text-black text-xs tracking-[0.22em] uppercase font-semibold hover:bg-[#e8d49a] transition-colors duration-300",
				style: {
					borderRadius: "3px",
					fontFamily: "Jost, sans-serif"
				}
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(` Browse Stores `);
					else return [createTextVNode(" Browse Stores ")];
				}),
				_: 1
			}, _parent));
			_push(`<a href="#how-it-works" class="px-8 py-3.5 text-[#DCC175]/70 text-xs tracking-[0.22em] uppercase border border-[#DCC175]/25 hover:border-[#DCC175]/50 hover:text-[#DCC175] transition-all duration-300 backdrop-blur-sm" style="${ssrRenderStyle({
				borderRadius: "3px",
				fontFamily: "Jost, sans-serif"
			})}"> Sign Up </a></div><div class="flex gap-2.5 mt-8 lg:hidden"><!--[-->`);
			ssrRenderList(PACK_TIERS, (tier) => {
				_push(`<div class="flex-1 px-3 py-2.5 backdrop-blur-xl flex flex-col gap-0.5" style="${ssrRenderStyle({
					borderRadius: "6px",
					background: tier.bg,
					border: `1px solid ${tier.border}`,
					boxShadow: `0 4px 24px rgba(0,0,0,0.6), 0 0 18px ${tier.glow}`
				})}"><span class="text-[10px] font-bold tracking-[0.12em] uppercase" style="${ssrRenderStyle({
					color: tier.color,
					fontFamily: "Cinzel, serif",
					textShadow: `0 0 10px ${tier.glow}`
				})}">${ssrInterpolate(tier.name)}</span><span class="text-[9px] tracking-widest" style="${ssrRenderStyle({
					color: `${tier.color}90`,
					fontFamily: "Jost, sans-serif"
				})}">${ssrInterpolate(tier.qty)}</span></div>`);
			});
			_push(`<!--]--></div></div><div class="hidden lg:block">`);
			_push(ssrRenderComponent(FloatingPack_default, {
				mouseX: rawX.value,
				mouseY: rawY.value
			}, null, _parent));
			_push(`</div></div></section>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Homepage/Hero.vue
var _sfc_setup$4 = Hero_vue_vue_type_script_setup_true_lang_default.setup;
Hero_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/Hero.vue");
	return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
var Hero_default = Hero_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/Homepage/Ticker.vue?vue&type=script&setup=true&lang.ts
var Ticker_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Ticker",
	__ssrInlineRender: true,
	setup(__props) {
		const doubled = computed(() => [...tickerItems, ...tickerItems]);
		const motionOptions = {
			initial: { x: "0%" },
			enter: {
				x: "-50%",
				transition: {
					duration: 2e4,
					repeat: Infinity,
					easing: "linear"
				}
			}
		};
		return (_ctx, _push, _parent, _attrs) => {
			const _directive_motion = resolveDirective("motion");
			_push(`<section${ssrRenderAttrs(mergeProps({ class: "py-4 border-y border-[#DCC175]/10 overflow-hidden" }, _attrs))}><div${ssrRenderAttrs(mergeProps({ class: "flex gap-12 whitespace-nowrap" }, ssrGetDirectiveProps(_ctx, _directive_motion, motionOptions)))}><!--[-->`);
			ssrRenderList(doubled.value, (item, i) => {
				_push(`<span class="text-[10px] tracking-[0.35em] uppercase text-[#DCC175]/60 flex items-center gap-12" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}">${ssrInterpolate(item)} <span style="${ssrRenderStyle({ color: "#DCC175" })}">✦</span></span>`);
			});
			_push(`<!--]--></div></section>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Homepage/Ticker.vue
var _sfc_setup$3 = Ticker_vue_vue_type_script_setup_true_lang_default.setup;
Ticker_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/Ticker.vue");
	return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
var Ticker_default = Ticker_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/Homepage/HowItWorks.vue?vue&type=script&setup=true&lang.ts
var HowItWorks_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "HowItWorks",
	__ssrInlineRender: true,
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<section${ssrRenderAttrs(mergeProps({
				id: "how-it-works",
				class: "px-8 lg:px-16 py-[12px] lg:py-[42px]"
			}, _attrs))}><div class="mb-16"><h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 700
			})}"> How It `);
			_push(ssrRenderComponent(HoloText_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Works`);
					else return [createTextVNode("Works")];
				}),
				_: 1
			}, _parent));
			_push(`</h2></div><div class="grid grid-cols-1 md:grid-cols-3 gap-6"><!--[-->`);
			ssrRenderList(unref(steps), (step, i) => {
				_push(`<div class="p-8 border border-[#DCC175]/10 bg-[#0e0e1d]/60 relative overflow-hidden group" style="${ssrRenderStyle({ borderRadius: "4px" })}"><div class="absolute inset-0 bg-gradient-to-br from-amber-900/0 to-amber-900/0 group-hover:from-amber-900/8 group-hover:to-transparent transition-all duration-500"></div><div class="flex items-start justify-between mb-6"><span class="text-[10px] text-[#DCC175]/60 tracking-[0.3em]" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}">${ssrInterpolate(step.num)}</span><div class="w-9 h-9 rounded-full bg-[#DCC175]/10 border border-[#DCC175]/50 flex items-center justify-center">`);
				ssrRenderVNode(_push, createVNode(resolveDynamicComponent(step.icon), {
					size: 16,
					class: "text-[#DCC175]"
				}, null), _parent);
				_push(`</div></div><h3 class="text-xl text-white mb-3 tracking-tight" style="${ssrRenderStyle({
					fontFamily: "Cinzel, serif",
					fontWeight: 600
				})}">${ssrInterpolate(step.title)}</h3><p class="text-sm text-[#DCC175]/60 leading-relaxed" style="${ssrRenderStyle({
					fontFamily: "Jost, sans-serif",
					fontWeight: 300
				})}">${ssrInterpolate(step.desc)}</p></div>`);
			});
			_push(`<!--]--></div></section>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Homepage/HowItWorks.vue
var _sfc_setup$2 = HowItWorks_vue_vue_type_script_setup_true_lang_default.setup;
HowItWorks_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/HowItWorks.vue");
	return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
var HowItWorks_default = HowItWorks_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/Homepage/CTA.vue?vue&type=script&setup=true&lang.ts
var CTA_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "CTA",
	__ssrInlineRender: true,
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<section${ssrRenderAttrs(mergeProps({ class: "px-8 lg:px-16 py-[50px] lg:py-[102px] relative overflow-hidden max-h-[80vh] flex items-center" }, _attrs))}><div class="absolute inset-0 pointer-events-none"><div class="absolute inset-0" style="${ssrRenderStyle({ background: "radial-gradient(ellipse at 50% 50%, rgba(124,58,237,0.18) 0%, transparent 58%)" })}"></div></div><div class="max-w-3xl mx-auto text-center relative z-10"><div><span class="text-[10px] tracking-[0.5em] uppercase text-[#DCC175]/60 block mb-7" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}"> Ready? </span><h2 class="leading-none mb-9" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 900,
				fontSize: "clamp(3rem,9vw,7.5rem)"
			})}">`);
			_push(ssrRenderComponent(HoloText_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Open Your Pack`);
					else return [createTextVNode("Open Your Pack")];
				}),
				_: 1
			}, _parent));
			_push(`</h2><p class="text-[#DCC175]/60 mb-14 max-w-sm mx-auto text-base leading-relaxed" style="${ssrRenderStyle({
				fontFamily: "Jost, sans-serif",
				fontWeight: 300
			})}"> Check the live pool at your nearest participating shop and grab your mystery pack today. </p>`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/stores",
				class: "inline-block px-14 py-4 text-black text-xs tracking-[0.3em] uppercase font-bold relative overflow-hidden group",
				style: {
					background: "linear-gradient(135deg, #DCC175, #e8d49a)",
					borderRadius: "3px",
					fontFamily: "Jost, sans-serif"
				}
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="${ssrRenderStyle({ background: "linear-gradient(135deg, #7c3aed, #9d5cf5)" })}"${_scopeId}></div><span class="relative"${_scopeId}>Find a Shop</span>`);
					else return [createVNode("div", {
						class: "absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300",
						style: { background: "linear-gradient(135deg, #7c3aed, #9d5cf5)" }
					}), createVNode("span", { class: "relative" }, "Find a Shop")];
				}),
				_: 1
			}, _parent));
			_push(`</div></div></section>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Homepage/CTA.vue
var _sfc_setup$1 = CTA_vue_vue_type_script_setup_true_lang_default.setup;
CTA_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/CTA.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var CTA_default = CTA_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Pages/Welcome.vue?vue&type=script&setup=true&lang.ts
var Welcome_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Welcome",
	__ssrInlineRender: true,
	props: {
		recentPulls: {},
		whatsInThePool: {},
		totalAvailableCards: {}
	},
	setup(__props) {
		const filmGrainStyle = {
			backgroundImage: "url(\"data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='[w3.org](http://www.w3.org/2000/svg)'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E\")",
			backgroundSize: "200px 200px"
		};
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "bg-background text-foreground min-h-screen relative overflow-x-hidden" }, _attrs))}><div class="fixed inset-0 pointer-events-none z-10 opacity-[0.028]" style="${ssrRenderStyle(filmGrainStyle)}"></div>`);
			_push(ssrRenderComponent(Orbs_default, null, null, _parent));
			_push(ssrRenderComponent(Nav_default, null, null, _parent));
			_push(`<main class="relative z-20">`);
			_push(ssrRenderComponent(Hero_default, { "total-available-cards": __props.totalAvailableCards }, null, _parent));
			_push(ssrRenderComponent(Ticker_default, null, null, _parent));
			_push(ssrRenderComponent(PullsSlider_default, { pulls: __props.recentPulls }, null, _parent));
			_push(ssrRenderComponent(HowItWorks_default, null, null, _parent));
			_push(ssrRenderComponent(LivePool_default, { pulls: __props.whatsInThePool }, null, _parent));
			_push(ssrRenderComponent(CTA_default, null, null, _parent));
			_push(ssrRenderComponent(Footer_default, null, null, _parent));
			_push(`</main></div>`);
		};
	}
});
//#endregion
//#region resources/ts/Pages/Welcome.vue
var _sfc_setup = Welcome_vue_vue_type_script_setup_true_lang_default.setup;
Welcome_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Welcome.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Welcome_default = Welcome_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Welcome_default as default };

//# sourceMappingURL=Welcome-C4mGKelr.js.map