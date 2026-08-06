import { t as HoloText_default } from "./HoloText-C1kAbqXE.js";
import { Link } from "@inertiajs/vue3";
import { ssrGetDirectiveProps, ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { computed, createTextVNode, defineComponent, mergeProps, resolveDirective, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Components/Homepage/PoolTile.vue?vue&type=script&setup=true&lang.ts
var PoolTile_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "PoolTile",
	__ssrInlineRender: true,
	props: { pull: {} },
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({
				class: "flex-shrink-0 mx-2 border border-[#DCC175]/10 bg-[#0e0e1d]/80 group relative overflow-hidden flex p-3 gap-5",
				style: {
					borderRadius: "6px",
					height: "110px",
					width: "280px"
				}
			}, _attrs))}><div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500" style="${ssrRenderStyle({ background: "radial-gradient(circle at 50% 0%, rgba(220,193,117,0.06) 0%, transparent 70%)" })}"></div><div class="flex-shrink-0 h-full overflow-hidden relative z-10" style="${ssrRenderStyle({
				aspectRatio: "63/88",
				borderRadius: "3px"
			})}"><img${ssrRenderAttr("src", __props.pull.card.image)}${ssrRenderAttr("alt", __props.pull.card.name)} class="w-full h-full object-cover" loading="lazy"></div><div class="flex flex-col justify-center flex-1 min-w-0 relative z-10"><div><h4 class="text-sm text-white leading-snug mb-0.5 truncate" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 600
			})}">${ssrInterpolate(__props.pull.card.name)}</h4><p class="text-[10px] text-[#DCC175] truncate mb-1" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}">${ssrInterpolate(__props.pull.card.set)}</p><p class="text-[10px] text-purple-300/50" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}">${ssrInterpolate(__props.pull.store.name)}</p></div></div></div>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Homepage/PoolTile.vue
var _sfc_setup$1 = PoolTile_vue_vue_type_script_setup_true_lang_default.setup;
PoolTile_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Homepage/PoolTile.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var PoolTile_default = PoolTile_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/LivePool.vue?vue&type=script&setup=true&lang.ts
var LivePool_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "LivePool",
	__ssrInlineRender: true,
	props: { pulls: {} },
	setup(__props) {
		const props = __props;
		const poolRow1 = props.pulls.slice(0, 4);
		const poolRow2 = props.pulls.slice(4);
		const row1Doubled = computed(() => [...poolRow1, ...poolRow1]);
		const row2Doubled = computed(() => [...poolRow2, ...poolRow2]);
		const headerMotion = {
			initial: {
				opacity: 0,
				y: 30
			},
			enter: {
				opacity: 1,
				y: 0,
				transition: { duration: 800 }
			}
		};
		const row1Motion = {
			initial: { x: "0%" },
			enter: {
				x: "-50%",
				transition: {
					duration: 22e3,
					repeat: Infinity,
					easing: "linear"
				}
			}
		};
		const row2Motion = {
			initial: { x: "-50%" },
			enter: {
				x: "0%",
				transition: {
					duration: 22e3,
					repeat: Infinity,
					easing: "linear"
				}
			}
		};
		const linkMotion = {
			initial: { opacity: 0 },
			enter: {
				opacity: 1,
				transition: { delay: 500 }
			}
		};
		return (_ctx, _push, _parent, _attrs) => {
			const _directive_motion = resolveDirective("motion");
			if (__props.pulls.length) {
				_push(`<section${ssrRenderAttrs(mergeProps({
					id: "pool",
					class: "py-[52px] lg:py-[82px] w-full"
				}, _attrs))}><div${ssrRenderAttrs(mergeProps({ class: "px-8 lg:px-16 flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-14" }, ssrGetDirectiveProps(_ctx, _directive_motion, headerMotion)))}><div><div class="flex items-center gap-2 mb-5"><span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span><span class="text-[10px] tracking-[0.45em] uppercase text-emerald-400/70" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}"> Live — Updated Now </span></div><h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none" style="${ssrRenderStyle({
					fontFamily: "Cinzel, serif",
					fontWeight: 700
				})}">${ssrInterpolate("What's in")} `);
				_push(ssrRenderComponent(HoloText_default, null, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`the Pool`);
						else return [createTextVNode("the Pool")];
					}),
					_: 1
				}, _parent));
				_push(`</h2></div><p class="text-sm text-[#DCC175] max-w-xs leading-relaxed" style="${ssrRenderStyle({
					fontFamily: "Jost, sans-serif",
					fontWeight: 300
				})}"> Every card listed below is available at your local shop. Full odds transparency — always. </p></div><div class="overflow-hidden mb-4"><div${ssrRenderAttrs(mergeProps({ class: "flex" }, ssrGetDirectiveProps(_ctx, _directive_motion, row1Motion)))}><!--[-->`);
				ssrRenderList(row1Doubled.value, (card, i) => {
					_push(ssrRenderComponent(PoolTile_default, {
						key: i,
						pull: card
					}, null, _parent));
				});
				_push(`<!--]--></div></div><div class="overflow-hidden"><div${ssrRenderAttrs(mergeProps({ class: "flex" }, ssrGetDirectiveProps(_ctx, _directive_motion, row2Motion)))}><!--[-->`);
				ssrRenderList(row2Doubled.value, (card, i) => {
					_push(ssrRenderComponent(PoolTile_default, {
						key: i,
						pull: card
					}, null, _parent));
				});
				_push(`<!--]--></div></div><div${ssrRenderAttrs(mergeProps({ class: "mt-10 text-center" }, ssrGetDirectiveProps(_ctx, _directive_motion, linkMotion)))}>`);
				_push(ssrRenderComponent(unref(Link), {
					href: "/stores",
					class: "inline-block text-[10px] text-[#DCC175] tracking-[0.3em] uppercase border-b border-[#DCC175]/50 pb-0.5 hover:text-[#DCC175]/80 hover:border-[#DCC175]/70 transition-colors",
					style: { fontFamily: "Jost, sans-serif" }
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(` Find Your Nearest Shop → `);
						else return [createTextVNode(" Find Your Nearest Shop → ")];
					}),
					_: 1
				}, _parent));
				_push(`</div></section>`);
			} else _push(`<!---->`);
		};
	}
});
//#endregion
//#region resources/ts/Components/LivePool.vue
var _sfc_setup = LivePool_vue_vue_type_script_setup_true_lang_default.setup;
LivePool_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/LivePool.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var LivePool_default = LivePool_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { LivePool_default as t };

//# sourceMappingURL=LivePool-BFh-Gu-S.js.map