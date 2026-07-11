import { Link, usePage } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { createTextVNode, defineComponent, mergeProps, onMounted, onUnmounted, ref, unref, useSSRContext, watch, withCtx } from "vue";
import { Menu, X } from "lucide-vue-next";
//#region resources/ts/Assets/Link___Arcane.png
var Link___Arcane_default = "/build/assets/Link___Arcane-Cz3234Pi.png";
//#endregion
//#region resources/ts/Components/Layout/Footer.vue?vue&type=script&setup=true&lang.ts
var Footer_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Footer",
	__ssrInlineRender: true,
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<footer${ssrRenderAttrs(mergeProps({ class: "px-8 lg:px-16 py-8 border-t border-[#DCC175]/8" }, _attrs))}><div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4"><div class="flex items-center gap-2.5"><div><img${ssrRenderAttr("src", unref(Link___Arcane_default))} alt="Arcane" class="h-8 w-auto"></div><span class="text-xs tracking-[0.25em] text-[#DCC175]/60 uppercase" style="${ssrRenderStyle({
				fontFamily: "Cinzel, serif",
				fontWeight: 600
			})}"> Arcane </span></div><span class="text-[10px] text-[#DCC175]/50 tracking-widest text-center" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}"> © ${ssrInterpolate((/* @__PURE__ */ new Date()).getFullYear())} Arcane. All cards authenticated. All packs sealed fresh. </span></div></footer>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Layout/Footer.vue
var _sfc_setup$1 = Footer_vue_vue_type_script_setup_true_lang_default.setup;
Footer_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Layout/Footer.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var Footer_default = Footer_vue_vue_type_script_setup_true_lang_default;
//#endregion
//#region resources/ts/Components/Layout/Nav.vue?vue&type=script&setup=true&lang.ts
var Nav_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Nav",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const NAV_LINKS = [
			[
				"Stores",
				"/stores",
				!!(page?.props?.route)?.name?.startsWith("stores")
			],
			[
				"Sell to Us",
				"/sell",
				!!(page?.props?.route)?.name?.startsWith("sell")
			],
			[
				"Log In",
				"/login",
				!!(page?.props?.route)?.name?.startsWith("login")
			]
		];
		const scrolled = ref(false);
		const open = ref(false);
		const close = () => {
			open.value = false;
		};
		const onScroll = () => {
			scrolled.value = window.scrollY > 60;
		};
		onMounted(() => {
			window.addEventListener("scroll", onScroll, { passive: true });
		});
		onUnmounted(() => {
			window.removeEventListener("scroll", onScroll);
		});
		watch(open, (val) => {
			document.body.style.overflow = val ? "hidden" : "";
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(_attrs)}><nav class="${ssrRenderClass(["fixed top-0 left-0 right-0 z-50 px-6 lg:px-16 py-5 flex items-center justify-between transition-all duration-500", scrolled.value ? "backdrop-blur-2xl bg-[#06060b]/70 border-b border-[#DCC175]/10" : ""])}"><a class="flex items-center" href="/" title="Arcane"><img${ssrRenderAttr("src", unref(Link___Arcane_default))} alt="Arcane" class="h-10 w-auto"></a><div class="hidden md:flex items-center gap-9"><div class="flex gap-9 text-xs tracking-[0.22em] uppercase text-[#DCC175]/60" style="${ssrRenderStyle({ fontFamily: "Jost, sans-serif" })}"><!--[-->`);
			ssrRenderList(NAV_LINKS, ([label, href, active]) => {
				_push(`<a${ssrRenderAttr("href", href)} class="${ssrRenderClass(["hover:text-[#DCC175] transition-colors duration-300", active ? "text-white underline" : "text-[#DCC175]/60"])}">${ssrInterpolate(label)}</a>`);
			});
			_push(`<!--]--></div>`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/apply",
				class: "text-xs tracking-[0.18em] uppercase px-5 py-2.5 bg-[#DCC175] text-black font-semibold hover:bg-[#e8d49a] transition-all duration-300",
				style: {
					borderRadius: "3px",
					fontFamily: "Jost, sans-serif"
				}
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(` Become a Seller `);
					else return [createTextVNode(" Become a Seller ")];
				}),
				_: 1
			}, _parent));
			_push(`</div><button class="md:hidden flex items-center justify-center w-10 h-10 text-[#DCC175]/70 hover:text-[#DCC175] transition-colors" aria-label="Toggle menu">`);
			if (open.value) _push(ssrRenderComponent(unref(X), { size: 22 }, null, _parent));
			else _push(ssrRenderComponent(unref(Menu), { size: 22 }, null, _parent));
			_push(`</button></nav>`);
			if (open.value) {
				_push(`<div class="fixed inset-0 z-[100] md:hidden flex flex-col" style="${ssrRenderStyle({
					background: "rgba(6,6,11,0.97)",
					backdropFilter: "blur(24px)"
				})}"${ssrRenderAttr("aria-hidden", !open.value)}><div class="h-20"></div><nav class="flex flex-col px-8 gap-1 flex-1"><!--[-->`);
				ssrRenderList(NAV_LINKS, ([label, href, active], i) => {
					_push(`<a${ssrRenderAttr("href", href)} class="${ssrRenderClass(["py-4 text-2xl border-b border-[#DCC175]/8 transition-colors", active ? "text-white underline" : "text-[#DCC175]/70 hover:text-[#DCC175]"])}" style="${ssrRenderStyle({
						fontFamily: "Cinzel, serif",
						fontWeight: 600
					})}">${ssrInterpolate(label)}</a>`);
				});
				_push(`<!--]--></nav><div class="px-8 pb-12 pt-8">`);
				_push(ssrRenderComponent(unref(Link), {
					href: "/apply",
					onClick: close,
					class: "block w-full text-center py-4 bg-[#DCC175] text-black text-sm font-bold tracking-[0.2em] uppercase hover:bg-[#e8d49a] transition-colors",
					style: {
						borderRadius: "4px",
						fontFamily: "Jost, sans-serif"
					}
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(` Become a Seller `);
						else return [createTextVNode(" Become a Seller ")];
					}),
					_: 1
				}, _parent));
				_push(`</div></div>`);
			} else _push(`<!---->`);
			_push(`</div>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Layout/Nav.vue
var _sfc_setup = Nav_vue_vue_type_script_setup_true_lang_default.setup;
Nav_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Layout/Nav.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Nav_default = Nav_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Footer_default as n, Link___Arcane_default as r, Nav_default as t };

//# sourceMappingURL=Nav-Bb-_Zprs.js.map