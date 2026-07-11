import { n as Footer_default, t as Nav_default } from "./Nav-Bb-_Zprs.js";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrLooseContain, ssrRenderAttr, ssrRenderComponent, ssrRenderStyle } from "vue/server-renderer";
import { createTextVNode, defineComponent, unref, useSSRContext, withCtx } from "vue";
//#region resources/ts/Pages/Auth/Login.vue?vue&type=script&setup=true&lang.ts
var Login_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Login",
	__ssrInlineRender: true,
	setup(__props) {
		usePage();
		const form = useForm({
			email: "",
			password: "",
			remember: false
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[--><main class="bg-[#0d0b14] overflow-x-hidden"><div class="relative shrink-0"><div class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative size-full"><div class="h-[49px] relative shrink-0">`);
			_push(ssrRenderComponent(Nav_default, null, null, _parent));
			_push(`</div></div></div><div class="relative shrink-0 w-full"><div class="content-stretch flex flex-col gap-[56px] items-start pb-[120px] pt-[80px] px-8 lg:px-[64px] relative max-w-[600px] mx-auto"><div class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center text-center mx-auto relative shrink-0"><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white"><span class="leading-[normal]">Log</span><span class="leading-[normal] text-[#c9a84c]"> in</span></p><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px]"> Fill out the details below to access the seller dashboard.</p></div><div class="-translate-y-1/2 absolute right-[-220px] size-[720px] top-[calc(50%-0.5px)]"><div class="absolute inset-[-22.22%]"><svg class="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 1040 1040"><g filter="url(#filter0_f_145_2261)" id="Ellipse" opacity="0.18"><circle cx="520" cy="520" fill="url(#paint0_radial_145_2261)" r="360"></circle></g><defs><filter colorInterpolationFilters="sRGB" filterUnits="userSpaceOnUse" height="1040" id="filter0_f_145_2261" width="1040" x="0" y="0"><feFlood floodOpacity="0" result="BackgroundImageFix"></feFlood><feBlend in="SourceGraphic" in2="BackgroundImageFix" mode="normal" result="shape"></feBlend><feGaussianBlur result="effect1_foregroundBlur_145_2261" stdDeviation="80"></feGaussianBlur></filter><radialGradient cx="0" cy="0" gradientTransform="translate(520 520) rotate(-90) scale(509.112)" gradientUnits="userSpaceOnUse" id="paint0_radial_145_2261" r="1"><stop stopColor="#7C3AED"></stop><stop offset="1" stopOpacity="0"></stop></radialGradient></defs></svg></div></div><div class="content-stretch space-y-8 lg:space-y-0 lg:flex lg:gap-[32px] lg:items-start relative lg:shrink-0 w-full max-w-[600px] mx-auto"><div class="bg-[#13101e] content-stretch drop-shadow-[0px_0px_9px_rgba(124,58,237,0.2)] flex flex-col gap-[24px] items-start p-[40px] relative rounded-[16px] shrink-0 flex-1"><div aria-hidden class="absolute border border-[rgba(124,58,237,0.4)] border-solid inset-0 pointer-events-none rounded-[16px]"></div><div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full"><div class="content-stretch flex items-center relative shrink-0"><label for="email" class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap"> Email address</label></div><div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full"><div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]"></div><div class="flex flex-row items-center size-full"><div class="content-stretch flex items-center p-[14px] relative size-full"><input id="email" type="email"${ssrRenderAttr("value", unref(form).email)} class="w-full bg-transparent border-none outline-none text-[15px] text-white font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none"></div></div>`);
			if (unref(form).errors.email) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors.email)}</div>`);
			else _push(`<!---->`);
			_push(`</div></div><div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full"><div class="content-stretch flex items-center relative shrink-0"><label for="password" class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap"> Password</label></div><div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full"><div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]"></div><div class="flex flex-row items-center size-full"><div class="content-stretch flex items-center p-[14px] relative size-full"><input id="password" type="password"${ssrRenderAttr("value", unref(form).password)} class="w-full bg-transparent border-none outline-none text-[15px] text-white font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none"></div></div>`);
			if (unref(form).errors.password) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors.password)}</div>`);
			else _push(`<!---->`);
			_push(`</div></div><div class="w-full"><div class="flex items-center justify-between text-xs text-white/60"><label class="inline-flex items-center gap-2"><input${ssrIncludeBooleanAttr(Array.isArray(unref(form).remember) ? ssrLooseContain(unref(form).remember, null) : unref(form).remember) ? " checked" : ""} type="checkbox" class="rounded border-arcane-border bg-arcane-surface"><span>Remember me</span></label>`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/forgot-password",
				class: "text-white/60 hover:text-white transition duration-150 ease-in-out"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(` Forgot password? `);
					else return [createTextVNode(" Forgot password? ")];
				}),
				_: 1
			}, _parent));
			_push(`</div></div><div class="content-stretch flex flex-col gap-[16px] items-start relative shrink-0 w-full"><button type="submit"${ssrIncludeBooleanAttr(unref(form).processing) ? " disabled" : ""} class="content-stretch drop-shadow-[0px_0px_9px_rgba(201,168,76,0.25)] flex h-[56px] items-start justify-center py-[16px] relative rounded-[4px] shrink-0 w-full" style="${ssrRenderStyle({ "background-image": "linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%)" })}" data-name="Frame"><p class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-bold leading-[normal] relative shrink-0 text-[#0d0b14] text-[16px] uppercase whitespace-nowrap">`);
			if (unref(form).processing) _push(`<span>Signing in...</span>`);
			else _push(`<span>Sign in</span>`);
			_push(`</p></button></div></div></div></div></div></main>`);
			_push(ssrRenderComponent(Footer_default, null, null, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/ts/Pages/Auth/Login.vue
var _sfc_setup = Login_vue_vue_type_script_setup_true_lang_default.setup;
Login_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Auth/Login.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Login_default = Login_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Login_default as default };

//# sourceMappingURL=Login-Cn0ZmYau.js.map