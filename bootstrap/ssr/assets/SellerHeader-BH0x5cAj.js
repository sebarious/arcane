import { Link } from "@inertiajs/vue3";
import { ssrRenderAttrs, ssrRenderComponent } from "vue/server-renderer";
import { createTextVNode, createVNode, defineComponent, mergeProps, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Components/Layout/SellerHeader.vue?vue&type=script&setup=true&lang.ts
var SellerHeader_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "SellerHeader",
	__ssrInlineRender: true,
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<header${ssrRenderAttrs(mergeProps({ class: "sticky top-0 border-b border-arcane-border/60 bg-arcane-bg/90 backdrop-blur z-10" }, _attrs))}><div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/",
				class: "flex items-center gap-2"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<img src="/images/logo.png" alt="Arcane" class="h-10"${_scopeId}>`);
					else return [createVNode("img", {
						src: "/images/logo.png",
						alt: "Arcane",
						class: "h-10"
					})];
				}),
				_: 1
			}, _parent));
			_push(`<nav class="flex items-center gap-4 text-sm text-white/70">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/seller",
				class: "hover:text-white"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Dashboard`);
					else return [createTextVNode("Dashboard")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), {
				href: "/seller/batches",
				class: "hover:text-white"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Batches`);
					else return [createTextVNode("Batches")];
				}),
				_: 1
			}, _parent));
			_push(`<div class="outline-root">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/seller/request-batch",
				class: "btn-primary text-xs outline-inner"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<span${_scopeId}>Buy more</span>`);
					else return [createVNode("span", null, "Buy more")];
				}),
				_: 1
			}, _parent));
			_push(`</div></nav></div></header>`);
		};
	}
});
//#endregion
//#region resources/ts/Components/Layout/SellerHeader.vue
var _sfc_setup = SellerHeader_vue_vue_type_script_setup_true_lang_default.setup;
SellerHeader_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/Layout/SellerHeader.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var SellerHeader_default = SellerHeader_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { SellerHeader_default as t };

//# sourceMappingURL=SellerHeader-BH0x5cAj.js.map