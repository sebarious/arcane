import { t as HoloText_default } from "./HoloText-C1kAbqXE.js";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { computed, createTextVNode, defineComponent, mergeProps, onMounted, onUnmounted, ref, unref, useSSRContext, withCtx } from "vue";
import { ChevronLeft, ChevronRight } from "lucide-vue-next";
//#region resources/ts/Components/PullCard.vue?vue&type=script&setup=true&lang.ts
var PullCard_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "PullCard",
	__ssrInlineRender: true,
	props: {
		pull: {},
		active: { type: Boolean }
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
		const colors = computed(() => RARITY_COLORS[props.pull.card.band]);
		const glowStyle = computed(() => ({
			background: `radial-gradient(ellipse at 50% 60%, ${colors.value.glow} 0%, transparent 70%)`,
			filter: "blur(22px)",
			opacity: props.active ? .9 : .15,
			transform: props.active ? "scale(1.05)" : "scale(0.9)"
		}));
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({
				class: "flex-shrink-0 flex flex-col",
				style: { width: "280px" }
			}, _attrs))}><div class="relative" style="${ssrRenderStyle({ padding: "28px 12px 12px" })}"><div class="absolute inset-0 rounded-2xl transition-all duration-300" style="${ssrRenderStyle(glowStyle.value)}"></div><div class="${ssrRenderClass([["will-change-transform", __props.active ? "scale-105 opacity-100 shadow-xl" : "scale-90 opacity-50"], "relative overflow-hidden transition-all duration-300"])}" style="${ssrRenderStyle({
				aspectRatio: "63/88",
				borderRadius: "10px"
			})}"><img${ssrRenderAttr("src", __props.pull.card.image)}${ssrRenderAttr("alt", __props.pull.card.name)} class="w-full h-full object-cover" draggable="false"></div></div><div class="${ssrRenderClass([__props.active ? "opacity-100" : "opacity-50", "px-3 mt-2 transition-opacity duration-300"])}"><h4 class="text-base text-white leading-snug mb-0.5" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 700
			})}">${ssrInterpolate(__props.pull.card.name)}</h4><p class="text-[10px] tracking-[0.18em] uppercase mb-3" style="${ssrRenderStyle({
				fontFamily: "Jost, sans-serif",
				color: "rgba(255,255,255,0.35)"
			})}">${ssrInterpolate(__props.pull.card.set)}</p><div class="flex items-center justify-between"><div class="flex gap-1.5"><span class="text-[9px] px-2 py-0.5 tracking-[0.2em] uppercase font-bold" style="${ssrRenderStyle({
				borderRadius: "2px",
				fontFamily: "Jost, sans-serif",
				background: "rgba(220,193,117,0.1)",
				color: "#DCC175",
				border: "1px solid rgba(220,193,117,0.25)"
			})}">${ssrInterpolate(__props.pull.card.number)}</span><span class="text-[9px] px-2 py-0.5 tracking-[0.15em] uppercase font-bold" style="${ssrRenderStyle({
				borderRadius: "2px",
				fontFamily: "Jost, sans-serif",
				color: colors.value.badge,
				background: `${colors.value.badge}18`,
				border: `1px solid ${colors.value.badge}40`
			})}">${ssrInterpolate(__props.pull.card.band)}</span></div></div></div></div>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/PullCard.vue
var _sfc_setup$1 = PullCard_vue_vue_type_script_setup_true_lang_default.setup;
PullCard_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/PullCard.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var PullCard_default = PullCard_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/PullsSlider.vue?vue&type=script&setup=true&lang.ts
var CARD_W = 280;
var CARD_GAP = 20;
var PullsSlider_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "PullsSlider",
	__ssrInlineRender: true,
	props: { pulls: {} },
	setup(__props) {
		const props = __props;
		const STEP = 300;
		const INF_PULLS = [
			...props.pulls,
			...props.pulls,
			...props.pulls
		];
		const INF_OFFSET = props.pulls.length;
		const current = ref(INF_OFFSET);
		ref(null);
		const activePullIndex = computed(() => current.value % props.pulls.length);
		const trackStyle = computed(() => ({
			gap: `${CARD_GAP}px`,
			transform: `translateX(${-current.value * STEP}px)`,
			transition: "transform 0.4s cubic-bezier(0.16, 1, 0.3, 1)"
		}));
		const dotStyle = (i) => ({
			width: i === activePullIndex.value ? "20px" : "6px",
			height: "6px",
			borderRadius: "3px",
			background: i === activePullIndex.value ? "#DCC175" : "rgba(220,193,117,0.25)"
		});
		const prev = () => {
			const nextIndex = current.value - 1;
			current.value = nextIndex < 1 ? INF_OFFSET - 1 : nextIndex;
		};
		const next = () => {
			const nextIndex = current.value + 1;
			current.value = nextIndex > INF_PULLS.length - 2 ? INF_OFFSET + 1 : nextIndex;
		};
		let intervalId = null;
		onMounted(() => {
			intervalId = window.setInterval(() => {
				next();
			}, 4e3);
		});
		onUnmounted(() => {
			if (intervalId !== null) window.clearInterval(intervalId);
		});
		let startX = 0;
		let dragging = false;
		const onPointerMove = (e) => {
			if (!dragging) return;
			const delta = e.clientX - startX;
			if (delta < -60) {
				dragging = false;
				next();
			} else if (delta > 60) {
				dragging = false;
				prev();
			}
		};
		const onPointerUp = () => {
			dragging = false;
			window.removeEventListener("pointermove", onPointerMove);
			window.removeEventListener("pointerup", onPointerUp);
		};
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<section${ssrRenderAttrs(mergeProps({
				id: "pulls",
				class: "py-[52px] lg:py-[82px]"
			}, _attrs))}><div class="px-8 lg:px-16 flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-6"><div><h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 700
			})}"> Latest `);
			_push(ssrRenderComponent(HoloText_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Pulls`);
					else return [createTextVNode("Pulls")];
				}),
				_: 1
			}, _parent));
			_push(`</h2></div><div class="flex items-center gap-3"><button class="w-10 h-10 rounded-full border border-[#DCC175]/25 flex items-center justify-center text-[#DCC175]/60 hover:border-[#DCC175]/70 hover:text-[#DCC175] transition-all duration-200">`);
			_push(ssrRenderComponent(unref(ChevronLeft), { size: 18 }, null, _parent));
			_push(`</button><div class="flex gap-1.5"><!--[-->`);
			ssrRenderList(__props.pulls, (p, i) => {
				_push(`<button class="transition-all duration-300" style="${ssrRenderStyle(dotStyle(i))}"></button>`);
			});
			_push(`<!--]--></div><button class="w-10 h-10 rounded-full border border-[#DCC175]/25 flex items-center justify-center text-[#DCC175]/60 hover:border-[#DCC175]/70 hover:text-[#DCC175] transition-all duration-200">`);
			_push(ssrRenderComponent(unref(ChevronRight), { size: 18 }, null, _parent));
			_push(`</button></div></div><div class="overflow-hidden py-10" style="${ssrRenderStyle({ paddingLeft: `calc(50vw - ${CARD_W / 2}px)` })}"><div class="flex cursor-grab active:cursor-grabbing" style="${ssrRenderStyle(trackStyle.value)}"><!--[-->`);
			ssrRenderList(INF_PULLS, (pull, i) => {
				_push(ssrRenderComponent(PullCard_default, {
					key: i,
					pull,
					active: i === current.value
				}, null, _parent));
			});
			_push(`<!--]--><div style="${ssrRenderStyle({
				width: "calc(100vw - 560px)",
				flexShrink: 0
			})}"></div></div></div></section>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/PullsSlider.vue
var _sfc_setup = PullsSlider_vue_vue_type_script_setup_true_lang_default.setup;
PullsSlider_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/PullsSlider.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var PullsSlider_default = PullsSlider_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { PullsSlider_default as t };

//# sourceMappingURL=PullsSlider-CAM7cC_x.js.map