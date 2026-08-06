import { n as Footer_default, t as Nav_default } from "./Nav-CpsP5fGk.js";
import { a as Orbs_default, i as LivePool_default, n as HeroSparkles_default, r as HeroBG_default, t as FloatingRings_default } from "./FloatingRings-C-rVTQ18.js";
import { t as HoloText_default } from "./HoloText-C1kAbqXE.js";
import { t as PullsSlider_default } from "./PullsSlider-BvBnamvP.js";
import { Head, Link } from "@inertiajs/vue3";
import { ssrGetDirectiveProps, ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { computed, createTextVNode, createVNode, defineComponent, mergeProps, onMounted, onUnmounted, ref, resolveDirective, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Assets/Arcane_pack.png
var Arcane_pack_default = "/build/assets/Arcane_pack-BdPRylHy.png";
//#endregion
//#region resources/ts/Assets/svp-208.png
var svp_208_default = "/build/assets/svp-208-CV2yfAav.png";
//#endregion
//#region resources/ts/Assets/sv4pt5-232.png
var sv4pt5_232_default = "/build/assets/sv4pt5-232-BkRGXh6K.png";
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
		title: "Packed & Sealed by Arcane.",
		desc: "Every mystery pack is assembled, quality checked and sealed by Arcane before it’s dispatched.\n\nStreamers never handle or preview the contents beforehand, meaning nobody knows what’s inside until it’s opened live. Every opening begins with a genuinely sealed pack."
	},
	{
		num: "02",
		title: "Every Pull. Proven Live.",
		desc: "Each card is revealed, scanned and verified using its unique QR code, permanently recording every pull as it happens. This creates a live, tamper-proof history that buyers, sellers and viewers can trust with complete confidence."
	},
	{
		num: "03",
		title: "Live Pull Rates. Always Accurate.",
		desc: "Every pull instantly updates the published odds for that mystery batch.\n\nCustomers always see the current pull rates, remaining chase cards and live batch progress—ensuring complete transparency from the first pack to the last."
	},
	{
		num: "04",
		title: "Live Market Pricing - Powered by PulseTCG",
		desc: "Built to order. Priced in real time.Every batch uses live PulseTCG pricing, ensuring accurate valuations and complete transparency from the moment it’s created.\n\nNo outdated valuations. No guesswork. Just real-time pricing."
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
	svp_208_default,
	sv4pt5_232_default,
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
		const RARITY_COLORS = {
			common: {
				glow: "rgba(163,163,163,0.65)",
				shimmer: "rgba(163,163,163,0.1)",
				badge: "#a3a3a3"
			},
			rare: {
				glow: "rgba(59,130,246,0.75)",
				shimmer: "rgba(59,130,246,0.1)",
				badge: "#3b82f6"
			},
			super: {
				glow: "rgba(45,212,191,0.75)",
				shimmer: "rgba(45,212,191,0.1)",
				badge: "#2dd4bf"
			},
			legendary: {
				glow: "rgba(123,79,233,0.65)",
				shimmer: "rgba(123,79,233,0.1)",
				badge: "#7b4fe9"
			},
			mythic: {
				glow: "rgba(201,168,76,0.75)",
				shimmer: "rgba(201, 168, 76, 0.2)",
				badge: "#c9a84c"
			}
		};
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
		const ringRotation = ref(0);
		let ringInterval = null;
		onMounted(() => {
			ringInterval = window.setInterval(() => {
				ringRotation.value += 1;
			}, 16);
		});
		onUnmounted(() => {
			if (ringInterval !== null) window.clearInterval(ringInterval);
		});
		computed(() => CARD_IMAGES[cardIndex.value]);
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
			position: "relative"
		}));
		const sharedFaceStyle = {
			position: "absolute",
			inset: "0",
			borderRadius: "10px",
			overflow: "hidden",
			backfaceVisibility: "hidden"
		};
		const backCard1 = computed(() => ({
			...sharedFaceStyle,
			left: "-187.5px",
			width: "250px",
			height: "350px",
			top: "50%",
			transform: "translateY(-50%) rotate(-12deg)"
		}));
		const backCard2 = computed(() => ({
			...sharedFaceStyle,
			left: "100%",
			marginLeft: "-62.5px",
			width: "250px",
			height: "350px",
			top: "50%",
			transform: "translateY(-50%) rotate(12deg)"
		}));
		const ringStyle = computed(() => ({
			transform: `rotate(${ringRotation.value}deg)`,
			transition: "transform 16ms linear"
		}));
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "flex flex-col items-center justify-center h-[280px] md:h-[520px]" }, _attrs))}><div class="relative flex flex-col items-center justify-center" style="${ssrRenderStyle({ perspective: "1200px" })}"><div class="hidden md:absolute w-[420px] h-[420px] rounded-full blur-3xl" style="${ssrRenderStyle({ background: "radial-gradient(circle, rgba(124,58,237,0.22) 0%, rgba(220,193,117,0.12) 35%, transparent 72%)" })}"></div><div class="hidden md:absolute w-[380px] md:h-[380px] rounded-full border border-[#DCC175]/10" style="${ssrRenderStyle(ringStyle.value)}"></div><div class="relative z-10 transition-transform duration-200 scale-50 md:scale-100" style="${ssrRenderStyle(wrapperStyle.value)}"><div class="relative" style="${ssrRenderStyle(flipContainerStyle.value)}"><div style="${ssrRenderStyle(backCard1.value)}"><img${ssrRenderAttr("src", unref(CARD_IMAGES)[0])} alt="Pokémon card" class="w-full h-full object-cover select-none" draggable="false" loading="lazy"><div class="absolute bottom-0 left-0 right-0 h-[50%] bg-gradient-to-t from-black/100 to-transparent pointer-events-none"><div class="absolute bottom-4 left-2.5"><span class="text-[9px] px-2 py-0.5 tracking-[0.15em] uppercase font-bold" style="${ssrRenderStyle({
				borderRadius: "2px",
				fontFamily: "Jost, sans-serif",
				color: RARITY_COLORS["super"].badge,
				background: `${RARITY_COLORS["super"].badge}18`,
				border: `1px solid ${RARITY_COLORS["super"].badge}40`
			})}"> Super </span><p class="text-xs font-medium text-white/80 mt-1">Victini</p></div></div></div><div style="${ssrRenderStyle(backCard2.value)}" class="back-card"><img${ssrRenderAttr("src", unref(CARD_IMAGES)[1])} alt="Pokémon card" class="w-full h-full object-cover select-none" draggable="false" loading="lazy"><div class="absolute bottom-0 left-0 right-0 h-[50%] bg-gradient-to-t from-black/100 to-transparent pointer-events-none"><div class="absolute bottom-4 right-2.5 text-right"><span class="text-[9px] px-2 py-0.5 tracking-[0.15em] uppercase font-bold" style="${ssrRenderStyle({
				borderRadius: "2px",
				fontFamily: "Jost, sans-serif",
				color: RARITY_COLORS["mythic"].badge,
				background: `${RARITY_COLORS["mythic"].badge}18`,
				border: `1px solid ${RARITY_COLORS["mythic"].badge}40`
			})}"> Mythic </span><p class="text-xs font-medium text-white/80 mt-1">Mew Ex</p></div></div></div><div style="${ssrRenderStyle(sharedFaceStyle)}"><img${ssrRenderAttr("src", unref(Arcane_pack_default))} alt="Arcane Mystery Pack" class="w-full h-full object-cover select-none" draggable="false" loading="lazy"></div></div></div><div class="gap-3 mt-8 hidden lg:flex"><!--[-->`);
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
var _sfc_setup$8 = FloatingPack_vue_vue_type_script_setup_true_lang_default.setup;
FloatingPack_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/FloatingPack.vue");
	return _sfc_setup$8 ? _sfc_setup$8(props, ctx) : void 0;
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
var _sfc_setup$7 = SplitWords_vue_vue_type_script_setup_true_lang_default.setup;
SplitWords_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/SplitWords.vue");
	return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
var SplitWords_default = SplitWords_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/Homepage/Hero.vue?vue&type=script&setup=true&lang.ts
var Hero_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Hero",
	__ssrInlineRender: true,
	props: { totalAvailableCards: {} },
	setup(__props) {
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
			_push(`<div class="relative z-10 w-full px-8 lg:px-16 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center pt-24 pb-16 lg:pt-14 lg:pb-0"><div class="relative z-[2]">`);
			if ((__props.totalAvailableCards ?? 0) > 0) _push(`<div class="inline-flex items-center gap-2 px-3 py-1.5 border border-[#DCC175]/25 bg-[#DCC175]/8 mb-9" style="${ssrRenderStyle({ borderRadius: "3px" })}"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span><span class="text-[10px] text-[#DCC175]/70 tracking-[0.3em] uppercase" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}"> Live Pool — ${ssrInterpolate((__props.totalAvailableCards ?? 0).toLocaleString())} Cards Available </span></div>`);
			else _push(`<!---->`);
			_push(`<div class="mb-8 text-[42px] md:text-[64px]" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 900,
				lineHeight: 1.05
			})}"><div class="overflow-hidden mb-1">`);
			_push(ssrRenderComponent(SplitWords_default, {
				text: "Know the Odds.",
				className: "text-white",
				delay: .25
			}, null, _parent));
			_push(`</div><div class="overflow-hidden whitespace-nowrap">`);
			_push(ssrRenderComponent(SplitWords_default, {
				text: "Love Every Pull.",
				delay: .5,
				style: infiniteChaseStyle
			}, null, _parent));
			_push(`</div></div><p class="text-sm md:text-base leading-relaxed mb-10 max-w-md" style="${ssrRenderStyle({
				fontFamily: "Jost, sans-serif",
				fontWeight: 300,
				color: "#e8e4f0"
			})}"> Mystery packs built on transparency. Live card pools, real-time pricing and verified pulls give you complete confidence before every purchase. </p><div class="grid grid-cols-2 md:flex gap-4 md:flex-wrap">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/apply",
				class: "text-center md:px-8 py-3.5 bg-[#DCC175] text-black text-xs tracking-[0.22em] uppercase font-semibold hover:bg-[#e8d49a] transition-colors duration-300",
				style: {
					borderRadius: "3px",
					fontFamily: "Jost, sans-serif"
				}
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(` Apply Now `);
					else return [createTextVNode(" Apply Now ")];
				}),
				_: 1
			}, _parent));
			_push(`<a href="/stores" class="text-center md:px-8 py-3.5 text-[#DCC175]/70 text-xs tracking-[0.22em] uppercase border border-[#DCC175]/25 hover:border-[#DCC175]/50 hover:text-[#DCC175] transition-all duration-300 backdrop-blur-sm" style="${ssrRenderStyle({
				borderRadius: "3px",
				fontFamily: "Jost, sans-serif"
			})}"> Browse stores </a></div></div><div class="relative z-[1]">`);
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
var _sfc_setup$6 = Hero_vue_vue_type_script_setup_true_lang_default.setup;
Hero_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/Hero.vue");
	return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
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
				_push(`<span class="text-[10px] tracking-[0.35em] uppercase text-[#DCC175] flex items-center gap-12" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}">${ssrInterpolate(item)} <span style="${ssrRenderStyle({ color: "#DCC175" })}">✦</span></span>`);
			});
			_push(`<!--]--></div></section>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Homepage/Ticker.vue
var _sfc_setup$5 = Ticker_vue_vue_type_script_setup_true_lang_default.setup;
Ticker_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/Ticker.vue");
	return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
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
			_push(`</h2></div><div class="grid grid-cols-1 md:grid-cols-2 gap-6"><!--[-->`);
			ssrRenderList(unref(steps), (step, i) => {
				_push(`<div class="p-8 border border-[#DCC175]/10 bg-[#0e0e1d]/60 relative overflow-hidden group" style="${ssrRenderStyle({ borderRadius: "4px" })}"><div class="absolute inset-0 bg-gradient-to-br from-amber-900/0 to-amber-900/0 group-hover:from-amber-900/8 group-hover:to-transparent transition-all duration-500"></div><div class="flex items-start justify-between mb-6"><span class="text-[10px] text-[#DCC175] tracking-[0.3em]" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}">${ssrInterpolate(step.num)}</span></div><h3 class="text-xl text-white mb-3 tracking-tight" style="${ssrRenderStyle({
					fontFamily: "Cinzel, serif",
					fontWeight: 600
				})}">${ssrInterpolate(step.title)}</h3><p class="text-sm text-white/70 leading-relaxed" style="${ssrRenderStyle({
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
var _sfc_setup$4 = HowItWorks_vue_vue_type_script_setup_true_lang_default.setup;
HowItWorks_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/HowItWorks.vue");
	return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
var HowItWorks_default = HowItWorks_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/Homepage/CTA.vue?vue&type=script&setup=true&lang.ts
var CTA_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "CTA",
	__ssrInlineRender: true,
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<section${ssrRenderAttrs(mergeProps({ class: "px-8 lg:px-16 py-[50px] lg:py-[102px] relative overflow-hidden max-h-[80vh] flex items-center" }, _attrs))}><div class="absolute inset-0 pointer-events-none"><div class="absolute inset-0" style="${ssrRenderStyle({ background: "radial-gradient(ellipse at 50% 50%, rgba(124,58,237,0.18) 0%, transparent 58%)" })}"></div></div><div class="max-w-3xl mx-auto text-center relative z-10"><div><span class="text-[10px] tracking-[0.5em] uppercase text-[#DCC175] block mb-7" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}"> Stream With Confidence. </span><h2 class="leading-none mb-9" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 900,
				fontSize: "clamp(3rem,9vw,5.5rem)"
			})}">`);
			_push(ssrRenderComponent(HoloText_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Grow Without Compromise.`);
					else return [createTextVNode("Grow Without Compromise.")];
				}),
				_: 1
			}, _parent));
			_push(`</h2><p class="text-white/70 mb-14 max-w-lg mx-auto text-base leading-relaxed" style="${ssrRenderStyle({
				fontFamily: "Jost, sans-serif",
				fontWeight: 300
			})}"> Arcane handles the packing, verification and transparency, so you can focus on creating great content. Give your audience a mystery experience they can trust—backed by live verification, published pull rates and real market pricing. </p>`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/apply",
				class: "inline-block px-14 py-4 text-black text-xs tracking-[0.3em] uppercase font-bold relative overflow-hidden group",
				style: {
					background: "linear-gradient(135deg, #DCC175, #e8d49a)",
					borderRadius: "3px",
					fontFamily: "Jost, sans-serif"
				}
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="${ssrRenderStyle({ background: "linear-gradient(135deg, #7c3aed, #9d5cf5)" })}"${_scopeId}></div><span class="relative"${_scopeId}>Apply to Partner</span>`);
					else return [createVNode("div", {
						class: "absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300",
						style: { background: "linear-gradient(135deg, #7c3aed, #9d5cf5)" }
					}), createVNode("span", { class: "relative" }, "Apply to Partner")];
				}),
				_: 1
			}, _parent));
			_push(`</div></div></section>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Homepage/CTA.vue
var _sfc_setup$3 = CTA_vue_vue_type_script_setup_true_lang_default.setup;
CTA_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/CTA.vue");
	return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
var CTA_default = CTA_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/Tiers.vue?vue&type=script&setup=true&lang.ts
var Tiers_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Tiers",
	__ssrInlineRender: true,
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<section${ssrRenderAttrs(mergeProps({ class: "px-8 lg:px-16 py-[12px] lg:py-[42px]" }, _attrs))}><div class="mb-16 max-w-2xl"><h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 700
			})}"> Flexible Selling `);
			_push(ssrRenderComponent(HoloText_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Tiers`);
					else return [createTextVNode("Tiers")];
				}),
				_: 1
			}, _parent));
			_push(`</h2><p class="text-base leading-relaxed text-white/80"> Built to Grow With You. Arcane offers three transparent pricing tiers designed to suit all sellers and buyers. </p></div><div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6"><div class="price-card rounded-xl p-6 flex flex-col gap-4" style="${ssrRenderStyle({
				"--glow": "rgba(163,163,163,0.65)",
				"--shimmer": "rgba(163,163,163,0.1)"
			})}"><div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center"><span class="price-dot w-4 h-4 rounded-full"></span></div><div><div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Common</div><div class="text-2xl font-black text-white">£1–£4</div></div></div><div class="price-card rounded-xl p-6 flex flex-col gap-4" style="${ssrRenderStyle({
				"--glow": "rgba(59,130,246,0.75)",
				"--shimmer": "rgba(59,130,246,0.1)"
			})}"><div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center"><span class="price-dot w-4 h-4 rounded-full"></span></div><div><div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Rare</div><div class="text-2xl font-black text-white">£5–£11</div></div></div><div class="price-card rounded-xl p-6 flex flex-col gap-4" style="${ssrRenderStyle({
				"--glow": "rgba(45,212,191,0.75)",
				"--shimmer": "rgba(45,212,191,0.1)"
			})}"><div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center"><span class="price-dot w-4 h-4 rounded-full"></span></div><div><div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Super</div><div class="text-2xl font-black text-white">£11–£50</div></div></div><div class="price-card rounded-xl p-6 flex flex-col gap-4" style="${ssrRenderStyle({
				"--glow": "rgba(123,79,233,0.65)",
				"--shimmer": "rgba(123,79,233,0.1)"
			})}"><div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center"><span class="price-dot w-4 h-4 rounded-full"></span></div><div><div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Legendary</div><div class="text-2xl font-black text-white">£50–£200</div></div></div><div class="price-card rounded-xl p-6 flex flex-col gap-4" style="${ssrRenderStyle({
				"--glow": "rgba(201,168,76,0.75)",
				"--shimmer": "rgba(201,168,76,0.2)"
			})}"><div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center"><span class="price-dot w-4 h-4 rounded-full"></span></div><div><div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Mythic</div><div class="text-2xl font-black text-white">£200+</div></div></div></div><div class="grid grid-cols-1 md:grid-cols-3 gap-6"><div class="card-diamond p-8 border bg-[#0e0e1d]/60 relative overflow-hidden group rounded transition-colors duration-500"><div class="absolute inset-0 bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/10 group-hover:to-transparent transition-all duration-500"></div><div class="tier-chip-diamond inline-flex items-center gap-2 px-3 py-1.5 rounded-md mb-6"><span class="text-lg leading-none">💎</span><span class="text-[11px] font-bold tracking-[0.12em] uppercase" style="${ssrRenderStyle({ "color": "rgb(232,240,255)" })}">Diamond — Built to Scale</span></div><p class="text-sm text-white/80 leading-relaxed mb-6"> Our best value for high-volume sellers collections. </p><div class="space-y-3 border-t border-white/10 pt-6"><div class="flex items-baseline justify-between"><span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Batch Size</span><span class="text-sm text-white">500 packs</span></div></div><div class="space-y-3 border-t border-white/10 pt-6"><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-mythic"></span>Mythic</span><span class="text-white/50">2 <span class="text-white/80">· 0.4%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-mythic" style="${ssrRenderStyle({ "width": "0.4%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-legendary"></span>Legendary</span><span class="text-white/50">10 <span class="text-white/80">· 2.0%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-legendary" style="${ssrRenderStyle({ "width": "2.0%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-super"></span>Super</span><span class="text-white/50">15 <span class="text-white/80">· 3.0%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-super" style="${ssrRenderStyle({ "width": "3.0%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-rare"></span>Rare</span><span class="text-white/50">73 <span class="text-white/80">· 14.6%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-rare" style="${ssrRenderStyle({ "width": "14.6%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-common"></span>Common</span><span class="text-white/50">400 <span class="text-white/80">· 80.0%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-common" style="${ssrRenderStyle({ "width": "80.0%" })}"></div></div></div></div></div><div class="card-ruby p-8 border bg-[#0e0e1d]/60 relative overflow-hidden group rounded transition-colors duration-500"><div class="absolute inset-0 bg-gradient-to-br from-red-900/0 to-red-900/0 group-hover:from-red-900/10 group-hover:to-transparent transition-all duration-500"></div><div class="tier-chip-ruby inline-flex items-center gap-2 px-3 py-1.5 rounded-md mb-6"><span class="text-lg leading-none">❤️</span><span class="text-[11px] font-bold tracking-[0.12em] uppercase text-red-200">Ruby — Ready to Grow</span></div><p class="text-sm text-white/80 leading-relaxed mb-6"> More value for sellers looking to scale. </p><div class="space-y-3 border-t border-white/10 pt-6"><div class="flex items-baseline justify-between"><span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Batch Size</span><span class="text-sm text-white">250 packs</span></div></div><div class="space-y-3 border-t border-white/10 pt-6"><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-mythic"></span>Mythic</span><span class="text-white/50">1 <span class="text-white/80">· 0.4%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-mythic" style="${ssrRenderStyle({ "width": "0.4%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-legendary"></span>Legendary</span><span class="text-white/50">5 <span class="text-white/80">· 2.0%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-legendary" style="${ssrRenderStyle({ "width": "2.0%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-super"></span>Super</span><span class="text-white/50">8 <span class="text-white/80">· 3.2%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-super" style="${ssrRenderStyle({ "width": "3.2%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-rare"></span>Rare</span><span class="text-white/50">36 <span class="text-white/80">· 14.4%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-rare" style="${ssrRenderStyle({ "width": "14.4%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-common"></span>Common</span><span class="text-white/50">200 <span class="text-white/80">· 80.0%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-common" style="${ssrRenderStyle({ "width": "80.0%" })}"></div></div></div></div></div><div class="card-sapphire p-8 border bg-[#0e0e1d]/60 relative overflow-hidden group rounded transition-colors duration-500"><div class="absolute inset-0 bg-gradient-to-br from-blue-900/0 to-blue-900/0 group-hover:from-blue-900/10 group-hover:to-transparent transition-all duration-500"></div><div class="tier-chip-sapphire inline-flex items-center gap-2 px-3 py-1.5 rounded-md mb-6"><span class="text-lg leading-none">💎</span><span class="text-[11px] font-bold tracking-[0.12em] uppercase text-blue-200">Sapphire — Start Strong</span></div><p class="text-sm text-white/80 leading-relaxed mb-6"> Perfect for launching your first mystery batch. </p><div class="space-y-3 border-t border-white/10 pt-6"><div class="flex items-baseline justify-between"><span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Batch Size</span><span class="text-sm text-white">125 packs</span></div></div><div class="space-y-3 border-t border-white/10 pt-6"><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-mythic"></span>Mythic</span><span class="text-white/50">1 <span class="text-white/80">· 0.8%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-mythic" style="${ssrRenderStyle({ "width": "0.8%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-legendary"></span>Legendary</span><span class="text-white/50">2 <span class="text-white/80">· 1.6%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-legendary" style="${ssrRenderStyle({ "width": "1.6%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-super"></span>Super</span><span class="text-white/50">3 <span class="text-white/80">· 2.4%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-super" style="${ssrRenderStyle({ "width": "2.4%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-rare"></span>Rare</span><span class="text-white/50">6 <span class="text-white/80">· 4.8%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-rare" style="${ssrRenderStyle({ "width": "4.8%" })}"></div></div></div><div><div class="flex items-center justify-between text-xs mb-1.5"><span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full dot-common"></span>Common</span><span class="text-white/50">113 <span class="text-white/80">· 90.4%</span></span></div><div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full rounded-full bar-common" style="${ssrRenderStyle({ "width": "90.4%" })}"></div></div></div></div></div></div></section>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Tiers.vue
var _sfc_setup$2 = Tiers_vue_vue_type_script_setup_true_lang_default.setup;
Tiers_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Tiers.vue");
	return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
var Tiers_default = Tiers_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/SellCardsPopup.vue?vue&type=script&setup=true&lang.ts
var STORAGE_KEY = "arcane_sell_popup_dismissed";
var SHOW_DELAY_MS = 1e4;
var SellCardsPopup_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "SellCardsPopup",
	__ssrInlineRender: true,
	setup(__props) {
		const visible = ref(false);
		let timer;
		onMounted(() => {
			if (localStorage.getItem(STORAGE_KEY)) return;
			timer = setTimeout(() => {
				visible.value = true;
			}, SHOW_DELAY_MS);
		});
		onUnmounted(() => {
			if (timer) clearTimeout(timer);
		});
		return (_ctx, _push, _parent, _attrs) => {
			if (visible.value) {
				_push(`<div${ssrRenderAttrs(mergeProps({ class: "fixed bottom-6 right-6 z-50 w-[300px] bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[12px] p-[20px] shadow-[0px_8px_28px_rgba(0,0,0,0.45)]" }, _attrs))}><button type="button" aria-label="Dismiss" class="absolute top-[10px] right-[12px] text-[#71717a] hover:text-white text-[18px] leading-none"> × </button><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[16px] text-white pr-[16px]"> We want to buy your cards </p><p class="font-[&#39;Jost&#39;,sans-serif] font-normal text-[13px] text-[#a3a3a3] mt-[8px] leading-relaxed"> Get an instant offer of up to <span class="text-[#c9a84c] font-semibold">80% of market value</span> — or <span class="text-[#c9a84c] font-semibold">85% with an affiliate code</span> — fast, secure payment, no fees. </p>`);
				_push(ssrRenderComponent(unref(Link), {
					href: "/sell",
					class: "mt-[16px] flex items-center justify-center h-[40px] rounded-[4px] w-full",
					style: { "background-image": "linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%)" }
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`<span class="font-[&#39;Jost&#39;,sans-serif] font-bold text-[13px] text-[#0d0b14] uppercase"${_scopeId}>Sell to us</span>`);
						else return [createVNode("span", { class: "font-['Jost',sans-serif] font-bold text-[13px] text-[#0d0b14] uppercase" }, "Sell to us")];
					}),
					_: 1
				}, _parent));
				_push(`</div>`);
			} else _push(`<!---->`);
		};
	}
});
//#endregion
//#region resources/ts/Components/SellCardsPopup.vue
var _sfc_setup$1 = SellCardsPopup_vue_vue_type_script_setup_true_lang_default.setup;
SellCardsPopup_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/SellCardsPopup.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var SellCardsPopup_default = SellCardsPopup_vue_vue_type_script_setup_true_lang_default;
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
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Live Pull Rates, Real Cards, Zero Guesswork" }, null, _parent));
			_push(`<div class="bg-background text-foreground min-h-screen relative overflow-x-hidden"><div class="fixed inset-0 pointer-events-none z-10 opacity-[0.028]" style="${ssrRenderStyle(filmGrainStyle)}"></div>`);
			_push(ssrRenderComponent(Orbs_default, null, null, _parent));
			_push(ssrRenderComponent(Nav_default, null, null, _parent));
			_push(`<main class="relative z-20">`);
			_push(ssrRenderComponent(Hero_default, { "total-available-cards": __props.totalAvailableCards }, null, _parent));
			_push(ssrRenderComponent(Ticker_default, null, null, _parent));
			_push(ssrRenderComponent(PullsSlider_default, { pulls: __props.recentPulls }, null, _parent));
			_push(ssrRenderComponent(HowItWorks_default, null, null, _parent));
			_push(ssrRenderComponent(LivePool_default, { pulls: __props.whatsInThePool }, null, _parent));
			_push(`<section class="px-8 lg:px-16 py-[12px] lg:py-[42px]"><div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center"><div><h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 700
			})}"> Why choose `);
			_push(ssrRenderComponent(HoloText_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Arcane`);
					else return [createTextVNode("Arcane")];
				}),
				_: 1
			}, _parent));
			_push(`</h2><p class="text-base leading-relaxed max-w-md text-white/70"> Every pack we seal is built on proof, not promises. Authenticated singles, a live card pool you can check before you buy, and real-time pricing mean you always know exactly what you&#39;re paying for — and exactly what&#39;s left to pull. </p></div><div class="grid grid-cols-1 sm:grid-cols-2 gap-6"><div class="p-6 border border-[#DCC175]/10 bg-[#0e0e1d]/60 relative overflow-hidden group rounded"><div class="absolute inset-0 bg-gradient-to-br from-amber-900/0 to-amber-900/0 group-hover:from-amber-900/8 group-hover:to-transparent transition-all duration-500"></div><div class="flex items-start justify-between mb-4"><span class="text-[10px] text-[#DCC175] tracking-[0.3em]">01</span></div><h3 class="text-xl text-white mb-3 tracking-tight" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 600
			})}"> Batch Merge </h3><p class="text-sm text-white/70 leading-relaxed" style="${ssrRenderStyle({
				fontFamily: "Jost, sans-serif",
				fontWeight: 300
			})}"> As chase cards are pulled, your batch naturally changes. With Batch Merge, you can combine your remaining cards into a brand-new batch, restoring balanced pull rates and giving your inventory a fresh opportunity to sell.<br><br>More momentum. Better visibility. Longer-lasting batches. </p></div><div class="p-6 border border-[#DCC175]/10 bg-[#0e0e1d]/60 relative overflow-hidden group rounded"><div class="absolute inset-0 bg-gradient-to-br from-amber-900/0 to-amber-900/0 group-hover:from-amber-900/8 group-hover:to-transparent transition-all duration-500"></div><div class="flex items-start justify-between mb-4"><span class="text-[10px] text-[#DCC175] tracking-[0.3em]">02</span></div><h3 class="text-xl text-white mb-3 tracking-tight" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 600
			})}"> Buy-Back Promise </h3><p class="text-sm text-white/70 leading-relaxed" style="${ssrRenderStyle({
				fontFamily: "Jost, sans-serif",
				fontWeight: 300
			})}"> We believe selling through Arcane should be as flexible as it is transparent. If your batch isn’t selling, you decide to take a break, or your circumstances change, we’ll buy back your remaining eligible cards for 80% of their live market value. It’s our commitment to reducing risk while giving you the confidence to sell on your terms. Less risk. More flexibility. Complete peace of mind. </p></div><div class="p-6 border border-[#DCC175]/10 bg-[#0e0e1d]/60 relative overflow-hidden group rounded col-span-1 sm:col-span-2"><div class="absolute inset-0 bg-gradient-to-br from-amber-900/0 to-amber-900/0 group-hover:from-amber-900/8 group-hover:to-transparent transition-all duration-500"></div><div class="flex items-start justify-between mb-4"><span class="text-[10px] text-[#DCC175] tracking-[0.3em]">03</span></div><h3 class="text-xl text-white mb-3 tracking-tight" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 600
			})}"> No Subscription </h3><p class="text-sm text-white/70 leading-relaxed" style="${ssrRenderStyle({
				fontFamily: "Jost, sans-serif",
				fontWeight: 300
			})}"> Sell on Your Terms. Get started without monthly subscriptions, hidden fees or long-term commitments. Create batches when you want, pause whenever you like and grow at your own pace. <br><br>Arcane gives you the flexibility to sell your way. </p></div></div></div></section>`);
			_push(ssrRenderComponent(Tiers_default, null, null, _parent));
			_push(ssrRenderComponent(CTA_default, null, null, _parent));
			_push(ssrRenderComponent(Footer_default, null, null, _parent));
			_push(`</main>`);
			_push(ssrRenderComponent(SellCardsPopup_default, null, null, _parent));
			_push(`</div><!--]-->`);
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

//# sourceMappingURL=Welcome-CGbhkW4T.js.map