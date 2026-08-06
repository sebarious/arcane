import { t as SellerLayout_default } from "./SellerLayout-Oli8Lv5k.js";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, ref, renderList, toDisplayString, unref, useSSRContext, vModelText, withCtx, withDirectives } from "vue";
//#region resources/ts/Pages/Seller/Profile.vue?vue&type=script&setup=true&lang.ts
var Profile_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Profile",
	__ssrInlineRender: true,
	props: {
		stores: {},
		store: {}
	},
	setup(__props) {
		const props = __props;
		const SOCIAL_FIELDS = [
			{
				key: "website",
				label: "Website",
				placeholder: "https://yourstore.com"
			},
			{
				key: "instagram",
				label: "Instagram",
				placeholder: "https://instagram.com/yourstore"
			},
			{
				key: "tiktok",
				label: "TikTok",
				placeholder: "https://tiktok.com/@yourstore"
			},
			{
				key: "youtube",
				label: "YouTube",
				placeholder: "https://youtube.com/@yourstore"
			},
			{
				key: "x",
				label: "X / Twitter",
				placeholder: "https://x.com/yourstore"
			},
			{
				key: "facebook",
				label: "Facebook",
				placeholder: "https://facebook.com/yourstore"
			},
			{
				key: "discord",
				label: "Discord",
				placeholder: "https://discord.gg/invite"
			}
		];
		const form = useForm({
			description: props.store.description ?? "",
			location: props.store.location ?? "",
			logo: null,
			social_links: SOCIAL_FIELDS.reduce((acc, f) => ({
				...acc,
				[f.key]: props.store.social_links[f.key] ?? ""
			}), {})
		});
		const logoPreview = ref(props.store.logo);
		function onLogoChange(e) {
			const file = e.target.files?.[0] ?? null;
			form.logo = file;
			if (file) logoPreview.value = URL.createObjectURL(file);
		}
		function submit() {
			form.post(`/seller/profile/${props.store.id}`, {
				forceFormData: true,
				preserveScroll: true
			});
		}
		function switchStore(storeId) {
			router.get("/seller/profile", { store: storeId });
		}
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Store profile" }, null, _parent));
			_push(ssrRenderComponent(SellerLayout_default, {
				title: "Store profile",
				subtitle: "This is what customers see on your public Arcane page."
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (__props.stores.length > 1) {
							_push(`<div class="mb-6"${_scopeId}><select${ssrRenderAttr("value", __props.store.id)} class="bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-3 py-2 text-sm text-white font-[&#39;Jost&#39;,sans-serif]"${_scopeId}><!--[-->`);
							ssrRenderList(__props.stores, (s) => {
								_push(`<option${ssrRenderAttr("value", s.id)}${_scopeId}>${ssrInterpolate(s.name)}</option>`);
							});
							_push(`<!--]--></select></div>`);
						} else _push(`<!---->`);
						_push(`<div class="grid lg:grid-cols-3 gap-6"${_scopeId}><div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6 h-fit"${_scopeId}><p class="font-[&#39;Jost&#39;,sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-4"${_scopeId}>Profile image</p><div class="size-32 rounded-full bg-black mx-auto mb-4 overflow-hidden border border-[#3d2f6e]"${_scopeId}>`);
						if (logoPreview.value) _push(`<img${ssrRenderAttr("src", logoPreview.value)}${ssrRenderAttr("alt", __props.store.name)} class="w-full h-full object-cover"${_scopeId}>`);
						else _push(`<!---->`);
						_push(`</div><label class="block w-full text-center px-4 py-2 rounded-[6px] border border-[#3d2f6e] text-sm text-white font-[&#39;Jost&#39;,sans-serif] cursor-pointer hover:border-[#c9a84c] transition-colors"${_scopeId}> Choose image <input type="file" accept="image/*" class="hidden"${_scopeId}></label>`);
						if (unref(form).errors.logo) _push(`<p class="text-xs text-red-400 mt-2"${_scopeId}>${ssrInterpolate(unref(form).errors.logo)}</p>`);
						else _push(`<!---->`);
						_push(`<p class="font-[&#39;Jost&#39;,sans-serif] text-[11px] text-[#71717a] mt-3 text-center"${_scopeId}>Recommended: square, at least 300×300px.</p></div><div class="lg:col-span-2 bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6"${_scopeId}><div class="mb-5"${_scopeId}><label class="block font-[&#39;Jost&#39;,sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2"${_scopeId}>Bio</label><textarea rows="4" placeholder="Tell customers a bit about your store…" class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-[&#39;Jost&#39;,sans-serif] focus:outline-none focus:border-[#c9a84c]"${_scopeId}>${ssrInterpolate(unref(form).description)}</textarea>`);
						if (unref(form).errors.description) _push(`<p class="text-xs text-red-400 mt-1"${_scopeId}>${ssrInterpolate(unref(form).errors.description)}</p>`);
						else _push(`<!---->`);
						_push(`</div><div class="mb-6"${_scopeId}><label class="block font-[&#39;Jost&#39;,sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2"${_scopeId}>Location</label><input${ssrRenderAttr("value", unref(form).location)} type="text" placeholder="e.g. Leeds, Bristol, Online only" class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-[&#39;Jost&#39;,sans-serif] focus:outline-none focus:border-[#c9a84c]"${_scopeId}>`);
						if (unref(form).errors.location) _push(`<p class="text-xs text-red-400 mt-1"${_scopeId}>${ssrInterpolate(unref(form).errors.location)}</p>`);
						else _push(`<!---->`);
						_push(`</div><p class="font-[&#39;Jost&#39;,sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-3"${_scopeId}>Social links</p><div class="grid sm:grid-cols-2 gap-4 mb-6"${_scopeId}><!--[-->`);
						ssrRenderList(SOCIAL_FIELDS, (field) => {
							_push(`<div${_scopeId}><label class="block font-[&#39;Jost&#39;,sans-serif] text-xs text-[#a3a3a3] mb-1.5"${_scopeId}>${ssrInterpolate(field.label)}</label><input${ssrRenderAttr("value", unref(form).social_links[field.key])} type="url"${ssrRenderAttr("placeholder", field.placeholder)} class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-3 py-2 text-sm text-white font-[&#39;Jost&#39;,sans-serif] focus:outline-none focus:border-[#c9a84c]"${_scopeId}></div>`);
						});
						_push(`<!--]--></div><button type="button"${ssrIncludeBooleanAttr(unref(form).processing) ? " disabled" : ""} class="px-6 py-3 rounded-[4px] text-sm font-[&#39;Jost&#39;,sans-serif] font-bold uppercase tracking-wide text-[#0d0b14] disabled:opacity-50" style="${ssrRenderStyle({ "background-image": "linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%)" })}"${_scopeId}>${ssrInterpolate(unref(form).processing ? "Saving…" : "Save changes")}</button></div></div>`);
					} else return [__props.stores.length > 1 ? (openBlock(), createBlock("div", {
						key: 0,
						class: "mb-6"
					}, [createVNode("select", {
						value: __props.store.id,
						onChange: ($event) => switchStore(Number($event.target.value)),
						class: "bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-3 py-2 text-sm text-white font-['Jost',sans-serif]"
					}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.stores, (s) => {
						return openBlock(), createBlock("option", {
							key: s.id,
							value: s.id
						}, toDisplayString(s.name), 9, ["value"]);
					}), 128))], 40, ["value", "onChange"])])) : createCommentVNode("", true), createVNode("div", { class: "grid lg:grid-cols-3 gap-6" }, [createVNode("div", { class: "bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6 h-fit" }, [
						createVNode("p", { class: "font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-4" }, "Profile image"),
						createVNode("div", { class: "size-32 rounded-full bg-black mx-auto mb-4 overflow-hidden border border-[#3d2f6e]" }, [logoPreview.value ? (openBlock(), createBlock("img", {
							key: 0,
							src: logoPreview.value,
							alt: __props.store.name,
							class: "w-full h-full object-cover"
						}, null, 8, ["src", "alt"])) : createCommentVNode("", true)]),
						createVNode("label", { class: "block w-full text-center px-4 py-2 rounded-[6px] border border-[#3d2f6e] text-sm text-white font-['Jost',sans-serif] cursor-pointer hover:border-[#c9a84c] transition-colors" }, [createTextVNode(" Choose image "), createVNode("input", {
							type: "file",
							accept: "image/*",
							class: "hidden",
							onChange: onLogoChange
						}, null, 32)]),
						unref(form).errors.logo ? (openBlock(), createBlock("p", {
							key: 0,
							class: "text-xs text-red-400 mt-2"
						}, toDisplayString(unref(form).errors.logo), 1)) : createCommentVNode("", true),
						createVNode("p", { class: "font-['Jost',sans-serif] text-[11px] text-[#71717a] mt-3 text-center" }, "Recommended: square, at least 300×300px.")
					]), createVNode("div", { class: "lg:col-span-2 bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6" }, [
						createVNode("div", { class: "mb-5" }, [
							createVNode("label", { class: "block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2" }, "Bio"),
							withDirectives(createVNode("textarea", {
								"onUpdate:modelValue": ($event) => unref(form).description = $event,
								rows: "4",
								placeholder: "Tell customers a bit about your store…",
								class: "w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]"
							}, null, 8, ["onUpdate:modelValue"]), [[vModelText, unref(form).description]]),
							unref(form).errors.description ? (openBlock(), createBlock("p", {
								key: 0,
								class: "text-xs text-red-400 mt-1"
							}, toDisplayString(unref(form).errors.description), 1)) : createCommentVNode("", true)
						]),
						createVNode("div", { class: "mb-6" }, [
							createVNode("label", { class: "block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2" }, "Location"),
							withDirectives(createVNode("input", {
								"onUpdate:modelValue": ($event) => unref(form).location = $event,
								type: "text",
								placeholder: "e.g. Leeds, Bristol, Online only",
								class: "w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]"
							}, null, 8, ["onUpdate:modelValue"]), [[vModelText, unref(form).location]]),
							unref(form).errors.location ? (openBlock(), createBlock("p", {
								key: 0,
								class: "text-xs text-red-400 mt-1"
							}, toDisplayString(unref(form).errors.location), 1)) : createCommentVNode("", true)
						]),
						createVNode("p", { class: "font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-3" }, "Social links"),
						createVNode("div", { class: "grid sm:grid-cols-2 gap-4 mb-6" }, [(openBlock(), createBlock(Fragment, null, renderList(SOCIAL_FIELDS, (field) => {
							return createVNode("div", { key: field.key }, [createVNode("label", { class: "block font-['Jost',sans-serif] text-xs text-[#a3a3a3] mb-1.5" }, toDisplayString(field.label), 1), withDirectives(createVNode("input", {
								"onUpdate:modelValue": ($event) => unref(form).social_links[field.key] = $event,
								type: "url",
								placeholder: field.placeholder,
								class: "w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-3 py-2 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]"
							}, null, 8, ["onUpdate:modelValue", "placeholder"]), [[vModelText, unref(form).social_links[field.key]]])]);
						}), 64))]),
						createVNode("button", {
							type: "button",
							onClick: submit,
							disabled: unref(form).processing,
							class: "px-6 py-3 rounded-[4px] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide text-[#0d0b14] disabled:opacity-50",
							style: { "background-image": "linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%)" }
						}, toDisplayString(unref(form).processing ? "Saving…" : "Save changes"), 9, ["disabled"])
					])])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/ts/Pages/Seller/Profile.vue
var _sfc_setup = Profile_vue_vue_type_script_setup_true_lang_default.setup;
Profile_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Seller/Profile.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Profile_default = Profile_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Profile_default as default };

//# sourceMappingURL=Profile-Ccb2NEDH.js.map