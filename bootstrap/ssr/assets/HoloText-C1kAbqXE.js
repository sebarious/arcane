import { ssrGetDirectiveProps, ssrRenderAttrs, ssrRenderComponent, ssrRenderSlot } from "vue/server-renderer";
import { computed, createBlock, defineComponent, mergeProps, openBlock, renderSlot, resolveComponent, resolveDirective, useSSRContext, withCtx, withDirectives } from "vue";
//#region resources/ts/Components/HoloText.vue?vue&type=script&setup=true&lang.ts
var HoloText_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "HoloText",
	__ssrInlineRender: true,
	props: { className: {} },
	setup(__props) {
		const props = __props;
		const className = computed(() => props.className ?? "");
		const baseStyle = {
			backgroundImage: "linear-gradient(90deg,#4c1d95,#7c3aed,#a855f7,#c084fc,#ddd6fe,#a855f7,#7c3aed,#4c1d95)",
			backgroundSize: "300% 100%",
			WebkitBackgroundClip: "text",
			WebkitTextFillColor: "transparent"
		};
		const motionOptions = {
			initial: { backgroundPosition: "0% 50%" },
			enter: {
				backgroundPosition: "200% 50%",
				transition: {
					duration: 5e3,
					repeat: Infinity,
					easing: "linear"
				}
			}
		};
		return (_ctx, _push, _parent, _attrs) => {
			const _component_ClientOnly = resolveComponent("ClientOnly");
			const _directive_motion = resolveDirective("motion");
			_push(ssrRenderComponent(_component_ClientOnly, _attrs, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<span${ssrRenderAttrs(mergeProps({
							class: ["inline-block text-transparent bg-clip-text", className.value],
							style: baseStyle
						}, ssrGetDirectiveProps(_ctx, _directive_motion, motionOptions)))}${_scopeId}>`);
						ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent, _scopeId);
						_push(`</span>`);
					} else return [withDirectives((openBlock(), createBlock("span", {
						class: ["inline-block text-transparent bg-clip-text", className.value],
						style: baseStyle
					}, [renderSlot(_ctx.$slots, "default")], 2)), [[_directive_motion, motionOptions]])];
				}),
				_: 3
			}, _parent));
		};
	}
});
//#endregion
//#region resources/ts/Components/HoloText.vue
var _sfc_setup = HoloText_vue_vue_type_script_setup_true_lang_default.setup;
HoloText_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Components/HoloText.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var HoloText_default = HoloText_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { HoloText_default as t };

//# sourceMappingURL=HoloText-C1kAbqXE.js.map