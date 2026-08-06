import { n as Footer_default, t as Nav_default } from "./Nav-xTq28nz2.js";
import { Head } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
import { defineComponent, unref, useSSRContext } from "vue";
//#region resources/ts/Pages/Legal/PrivacyPolicy.vue?vue&type=script&setup=true&lang.ts
var PrivacyPolicy_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "PrivacyPolicy",
	__ssrInlineRender: true,
	props: { contactEmail: {} },
	setup(__props) {
		const sections = [
			{
				title: "1. Who we are",
				body: ["Arcane (arcanepacks.com) processes personal data as the data controller for the purposes described in this policy. This policy explains what we collect, why, and what rights you have under UK data protection law."]
			},
			{
				title: "2. What we collect",
				body: [
					"Account & seller data: name, email, phone number, address, and store details for sellers running a storefront on Arcane.",
					"Sell-to-Us submissions: name, email, phone number, and postcode, plus details of the cards you're submitting (card, quantity, offer) and any notes you add.",
					"Order data: contact and delivery details needed to fulfil a pack purchase.",
					"Technical data: standard web request data (IP address, browser type, pages visited) and essential cookies needed to keep the site working (for example, keeping you logged in, and remembering if you've dismissed a pop-up)."
				]
			},
			{
				title: "3. Why we use it",
				body: [
					"To fulfil pack orders and Sell to Us submissions, including contacting you about the status of either.",
					"To operate seller storefronts and pay out invoices to stores.",
					"To detect and prevent fraud or abuse (for example, of the affiliate program).",
					"To meet our legal and accounting obligations."
				]
			},
			{
				title: "4. Legal basis",
				body: ["We process most of this data because it's necessary to perform a contract with you (fulfilling an order, processing a submission, or running your store). Some processing (like fraud prevention) relies on our legitimate interest in running a safe, working service."]
			},
			{
				title: "5. Who we share it with",
				body: [
					"Payment processors, to take payment for orders.",
					"Email delivery providers, to send order confirmations, submission updates, and account notifications.",
					"Card pricing data providers, to look up live market prices — this only involves card identifiers, not your personal data.",
					"We don't sell your personal data to third parties."
				]
			},
			{
				title: "6. How long we keep it",
				body: ["We keep order, submission, and account data for as long as your account or store is active, and afterwards for as long as needed to meet our legal and accounting obligations (typically up to 6 years for financial records)."]
			},
			{
				title: "7. Your rights",
				body: ["Under UK data protection law, you can ask us to: give you a copy of your personal data, correct inaccurate data, delete your data, or restrict or object to certain processing. To exercise any of these rights, contact us using the details below.", "You also have the right to complain to the Information Commissioner's Office (ico.org.uk) if you think we've mishandled your data."]
			},
			{
				title: "8. Cookies",
				body: ["We use essential cookies only — to keep you logged in, protect against cross-site request forgery, and remember simple preferences like dismissing a pop-up. We don't currently use tracking or advertising cookies."]
			},
			{
				title: "9. Children",
				body: ["Arcane's services are intended for users aged 18 and over. We don't knowingly collect personal data from children."]
			},
			{
				title: "10. Changes to this policy",
				body: ["We may update this policy from time to time. Significant changes will be reflected by an updated \"last updated\" date at the top of this page."]
			}
		];
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Privacy Policy" }, null, _parent));
			_push(`<main class="bg-[#0d0b14] overflow-x-hidden min-h-screen"><div class="relative shrink-0"><div class="flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative w-full"><div class="h-[49px] relative shrink-0 w-full">`);
			_push(ssrRenderComponent(Nav_default, null, null, _parent));
			_push(`</div></div></div><div class="px-8 lg:px-[64px] pt-[60px] pb-[120px] max-w-3xl mx-auto"><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[40px] lg:text-[48px] text-white leading-tight"> Privacy <span class="text-[#c9a84c]">Policy</span></p><p class="font-[&#39;Jost&#39;,sans-serif] text-[#a3a3a3] text-[14px] mt-[8px]"> Last updated ${ssrInterpolate((/* @__PURE__ */ new Date()).toLocaleDateString("en-GB", {
				year: "numeric",
				month: "long"
			}))}</p><div class="flex flex-col gap-[36px] mt-[48px]"><!--[-->`);
			ssrRenderList(sections, (section) => {
				_push(`<section><h2 class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[20px] text-[#c9a84c] mb-[12px]">${ssrInterpolate(section.title)}</h2><!--[-->`);
				ssrRenderList(section.body, (para, i) => {
					_push(`<p class="font-[&#39;Jost&#39;,sans-serif] text-[15px] leading-relaxed text-[#d8d3e0] mb-[10px] last:mb-0">${ssrInterpolate(para)}</p>`);
				});
				_push(`<!--]--></section>`);
			});
			_push(`<!--]--><section><h2 class="font-[&#39;Cinzel&#39;,sans-serif] font-bold text-[20px] text-[#c9a84c] mb-[12px]"> 11. Contact us </h2><p class="font-[&#39;Jost&#39;,sans-serif] text-[15px] leading-relaxed text-[#d8d3e0]"> Questions about this policy, or want to exercise one of your rights? Email us at `);
			if (__props.contactEmail) _push(`<a${ssrRenderAttr("href", `mailto:${__props.contactEmail}`)} class="text-[#c9a84c] hover:underline">${ssrInterpolate(__props.contactEmail)}</a>`);
			else _push(`<!---->`);
			_push(`. </p></section></div></div></main>`);
			_push(ssrRenderComponent(Footer_default, null, null, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/ts/Pages/Legal/PrivacyPolicy.vue
var _sfc_setup = PrivacyPolicy_vue_vue_type_script_setup_true_lang_default.setup;
PrivacyPolicy_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Legal/PrivacyPolicy.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var PrivacyPolicy_default = PrivacyPolicy_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { PrivacyPolicy_default as default };

//# sourceMappingURL=PrivacyPolicy-_YNB1nH8.js.map