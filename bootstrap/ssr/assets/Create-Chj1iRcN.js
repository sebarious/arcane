import { n as Footer_default, t as Nav_default } from "./Nav-CpsP5fGk.js";
import { Head, useForm } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { computed, defineComponent, ref, unref, useSSRContext } from "vue";
//#region resources/ts/Pages/Sell/Create.vue?vue&type=script&setup=true&lang.ts
var MAX_ITEMS = 100;
var Create_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Create",
	__ssrInlineRender: true,
	setup(__props) {
		const checklist = [
			"Prices are using our Live Market Pricing aggregation through PulseTCG.",
			"Cards must be in a near-mint condition or better.",
			"Cards must be sent, checked and price confirmed by a member of our team.",
			"Fast, secure payment on confirmation of the price agreed with you.",
			"No fees — you just box up and send your cards to us, we do the rest.",
			"Maximum of 100 cards per submission."
		];
		const nameQuery = ref("");
		const numberQuery = ref("");
		const results = ref([]);
		const searching = ref(false);
		const searchError = ref("");
		const hasSearched = ref(false);
		const selected = ref([]);
		const affiliateCodeInput = ref("");
		const affiliateApplying = ref(false);
		const affiliateValid = ref(null);
		const affiliateError = ref("");
		const affiliateStoreName = ref(null);
		const affiliateBonusPercentage = ref(0);
		const bonusPercentLabel = computed(() => `${Math.round(affiliateBonusPercentage.value * 100)}%`);
		const totalItemCount = computed(() => selected.value.reduce((sum, c) => sum + c.quantity, 0));
		function formatPence(pence) {
			if (pence === null) return "—";
			return "£" + (pence / 100).toFixed(2);
		}
		function boostedPence(pence) {
			if (pence === null || !affiliateValid.value) return null;
			return Math.round(pence * (1 + affiliateBonusPercentage.value));
		}
		function baseLineOfferPence(card) {
			return (card.unit_offer_pence ?? 0) * card.quantity;
		}
		function lineOfferPence(card) {
			const base = baseLineOfferPence(card);
			return affiliateValid.value ? Math.round(base * (1 + affiliateBonusPercentage.value)) : base;
		}
		const totalBaseOfferPence = computed(() => selected.value.reduce((sum, c) => sum + baseLineOfferPence(c), 0));
		const totalOfferPence = computed(() => selected.value.reduce((sum, c) => sum + lineOfferPence(c), 0));
		const form = useForm({
			customer_name: "",
			customer_email: "",
			customer_phone: "",
			customer_postcode: "",
			description: "",
			affiliate_code: "",
			items: []
		});
		function itemError(index) {
			return form.errors[`items.${index}.product_id`];
		}
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Sell to Us" }, null, _parent));
			_push(`<main class="bg-[#0d0b14] overflow-x-hidden"><div class="relative shrink-0"><div class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative size-full"><div class="h-[49px] relative shrink-0">`);
			_push(ssrRenderComponent(Nav_default, null, null, _parent));
			_push(`</div></div></div><div class="relative shrink-0 w-full"><div class="content-stretch flex flex-col gap-[40px] items-start pb-[120px] pt-[80px] px-8 lg:px-[64px] relative size-full"><div class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-start relative shrink-0"><p class="font-[&#39;Cinzel&#39;,sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white"><span class="leading-[normal]">Sell</span><span class="leading-[normal] text-[#c9a84c]"> to us</span></p><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px] max-w-2xl"> Search for your cards below, add a quantity, and see our offer instantly — based on live market pricing.</p></div><div class="bg-[#2a1a1a] border border-[rgba(220,80,80,0.4)] rounded-[12px] p-[24px] relative shrink-0 w-full"><p class="font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] text-[15px] text-white"> We only currently buy <span class="text-[#e8a0a0]">Full Art, Illustration Rare, or higher tier</span> cards. </p><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] text-[14px] text-[#c9a3a3] mt-[6px]"> Please ensure all cards submitted meet this criteria. Any cards sent to us that do not meet this criteria will need you to arrange collection to be returned to you. </p></div><div class="grid sm:grid-cols-2 gap-[16px] w-full"><!--[-->`);
			ssrRenderList(checklist, (point) => {
				_push(`<div class="content-stretch flex items-start gap-[12px] bg-[#13101e] border border-[rgba(124,58,237,0.25)] rounded-[10px] p-[16px]"><svg class="shrink-0 mt-[2px]" width="18" height="18" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="#c9a84c" opacity="0.2"></circle><path d="M6 10.5L8.5 13L14 7" stroke="#c9a84c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg><p class="font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] text-[14px] text-[#d8d3e0]">${ssrInterpolate(point)}</p></div>`);
			});
			_push(`<!--]--></div><div class="content-stretch space-y-8 lg:space-y-0 lg:flex lg:gap-[32px] lg:items-start relative lg:shrink-0 w-full"><div class="bg-[#13101e] drop-shadow-[0px_0px_9px_rgba(124,58,237,0.2)] flex flex-col gap-[24px] items-start p-[40px] relative rounded-[16px] shrink-0 flex-1"><div aria-hidden class="absolute border border-[rgba(124,58,237,0.4)] border-solid inset-0 pointer-events-none rounded-[16px]"></div><div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full"><label class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap"> Affiliate code <small class="normal-case">(optional — get ${ssrInterpolate(bonusPercentLabel.value || "5%")} more)</small></label><div class="flex gap-[12px] w-full max-w-md"><div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] flex-1"><div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]"></div><div class="flex flex-row items-center size-full"><div class="content-stretch flex items-center p-[14px] relative size-full"><input type="text"${ssrRenderAttr("value", affiliateCodeInput.value)}${ssrIncludeBooleanAttr(affiliateValid.value === true) ? " disabled" : ""} placeholder="e.g. ARCANE123" class="w-full bg-transparent border-none outline-none text-[15px] text-white uppercase font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white placeholder:normal-case focus:ring-0 focus:outline-none disabled:opacity-60"></div></div></div>`);
			if (affiliateValid.value !== true) _push(`<button type="button"${ssrIncludeBooleanAttr(affiliateApplying.value || !affiliateCodeInput.value.trim()) ? " disabled" : ""} class="shrink-0 px-5 h-[48px] rounded-[6px] border border-[#3d2f6e] text-white text-sm font-[&#39;Jost&#39;,sans-serif] font-semibold uppercase tracking-wide hover:border-[#c9a84c] transition-colors disabled:opacity-50">${ssrInterpolate(affiliateApplying.value ? "Checking…" : "Apply")}</button>`);
			else _push(`<button type="button" class="shrink-0 px-5 h-[48px] rounded-[6px] border border-[#3d2f6e] text-[#a3a3a3] text-sm font-[&#39;Jost&#39;,sans-serif] font-semibold uppercase tracking-wide hover:border-red-400 hover:text-red-400 transition-colors"> Remove </button>`);
			_push(`</div>`);
			if (affiliateValid.value === true) _push(`<p class="text-[13px] text-[#7fd4a0] font-[&#39;Jost&#39;,sans-serif]"> ✓ Applied — ${ssrInterpolate(affiliateStoreName.value)}&#39;s code. You&#39;ll get ${ssrInterpolate(bonusPercentLabel.value)} more on every offer below. </p>`);
			else if (affiliateValid.value === false) _push(`<p class="text-[13px] text-red-400 font-[&#39;Jost&#39;,sans-serif]">${ssrInterpolate(affiliateError.value)}</p>`);
			else _push(`<!---->`);
			_push(`</div><div class="w-full h-px bg-[rgba(124,58,237,0.2)] shrink-0"></div><div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full"><label class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap"> Find your cards</label><div class="flex flex-col sm:flex-row gap-[12px] w-full"><div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] flex-[3] w-full"><div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]"></div><div class="flex flex-row items-center size-full"><div class="content-stretch flex items-center p-[14px] relative size-full"><input type="text"${ssrRenderAttr("value", nameQuery.value)} placeholder="Card name, e.g. Charizard ex" class="w-full bg-transparent border-none outline-none text-[15px] text-white font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none"></div></div></div><div class="flex sm:hidden items-center gap-[10px] w-full"><div class="flex-1 h-px bg-[#3d2f6e]"></div><span class="font-[&#39;Jost&#39;,sans-serif] font-semibold text-[11px] text-[rgba(255,255,255,0.35)] uppercase">Or</span><div class="flex-1 h-px bg-[#3d2f6e]"></div></div><div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] flex-[1] w-full"><div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]"></div><div class="flex flex-row items-center size-full"><div class="content-stretch flex items-center p-[14px] relative size-full"><input type="text"${ssrRenderAttr("value", numberQuery.value)} placeholder="Number, e.g. 199/165" class="w-full bg-transparent border-none outline-none text-[15px] text-white font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none"></div></div></div></div>`);
			if (searching.value) _push(`<div class="text-[13px] text-[#a3a3a3] font-[&#39;Jost&#39;,sans-serif] mt-[4px]"> Searching… </div>`);
			else if (searchError.value) _push(`<div class="text-[13px] text-red-400 font-[&#39;Jost&#39;,sans-serif] mt-[4px]">${ssrInterpolate(searchError.value)}</div>`);
			else if (hasSearched.value && results.value.length === 0) _push(`<div class="text-[13px] text-[#a3a3a3] font-[&#39;Jost&#39;,sans-serif] mt-[4px]"> No matches found. </div>`);
			else _push(`<!---->`);
			if (results.value.length) {
				_push(`<div class="flex flex-col gap-[8px] w-full max-h-[360px] overflow-y-auto mt-[4px]"><!--[-->`);
				ssrRenderList(results.value, (card) => {
					_push(`<button type="button" class="flex items-center gap-[12px] p-[10px] rounded-[8px] border text-left transition-colors border-[#3d2f6e] hover:border-[#c9a84c] bg-[#1a1628] cursor-pointer">`);
					if (card.image_url) _push(`<img${ssrRenderAttr("src", card.image_url)} class="w-[36px] h-[50px] object-cover rounded-[4px]">`);
					else _push(`<!---->`);
					_push(`<div class="flex-1 min-w-0"><p class="font-[&#39;Jost&#39;,sans-serif] text-[14px] text-white truncate">${ssrInterpolate(card.card_name)}</p><p class="font-[&#39;Jost&#39;,sans-serif] text-[12px] text-[#a3a3a3] truncate">${ssrInterpolate(card.set_name)} · ${ssrInterpolate(card.card_number)} · ${ssrInterpolate(card.rarity)}</p></div><div class="text-right shrink-0">`);
					if (affiliateValid.value) _push(`<p class="font-[&#39;Jost&#39;,sans-serif] text-[11px] text-[#71717a] line-through"> Offer ${ssrInterpolate(formatPence(card.unit_offer_pence))}</p>`);
					else _push(`<!---->`);
					_push(`<p class="font-[&#39;Jost&#39;,sans-serif] text-[13px] text-[#c9a84c]"> Offer ${ssrInterpolate(formatPence(affiliateValid.value ? boostedPence(card.unit_offer_pence) : card.unit_offer_pence))} `);
					if (affiliateValid.value) _push(`<span class="text-[10px] text-[#7fd4a0]">(+${ssrInterpolate(bonusPercentLabel.value)})</span>`);
					else _push(`<!---->`);
					_push(`</p><p class="font-[&#39;Jost&#39;,sans-serif] text-[11px] text-[#71717a]"> Market ${ssrInterpolate(formatPence(card.market_value_pence))}</p></div></button>`);
				});
				_push(`<!--]--></div>`);
			} else _push(`<!---->`);
			_push(`</div>`);
			if (selected.value.length) {
				_push(`<div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full"><label class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap"> Your cards (${ssrInterpolate(totalItemCount.value)}/${ssrInterpolate(MAX_ITEMS)})</label><div class="flex flex-col gap-[8px] w-full"><!--[-->`);
				ssrRenderList(selected.value, (card, index) => {
					_push(`<div class="flex items-center gap-[12px] p-[12px] rounded-[8px] border border-[#3d2f6e] bg-[#1a1628]">`);
					if (card.image_url) _push(`<img${ssrRenderAttr("src", card.image_url)} class="w-[36px] h-[50px] object-cover rounded-[4px]">`);
					else _push(`<!---->`);
					_push(`<div class="flex-1 min-w-0"><p class="font-[&#39;Jost&#39;,sans-serif] text-[14px] text-white truncate">${ssrInterpolate(card.card_name)}</p><p class="font-[&#39;Jost&#39;,sans-serif] text-[12px] text-[#a3a3a3] truncate">${ssrInterpolate(card.set_name)} · ${ssrInterpolate(card.card_number)}</p>`);
					if (itemError(index)) _push(`<p class="text-[11px] text-red-400 mt-[2px]">${ssrInterpolate(itemError(index))}</p>`);
					else _push(`<!---->`);
					_push(`</div><input type="number" min="1" max="999"${ssrRenderAttr("value", card.quantity)} class="w-[56px] bg-[#0d0b14] border border-[#3d2f6e] rounded-[4px] text-center text-white text-[14px] py-[6px] font-[&#39;Jost&#39;,sans-serif]"><div class="w-[92px] text-right shrink-0">`);
					if (affiliateValid.value) _push(`<p class="font-[&#39;Jost&#39;,sans-serif] text-[11px] text-[#71717a] line-through leading-tight">${ssrInterpolate(formatPence(baseLineOfferPence(card)))}</p>`);
					else _push(`<!---->`);
					_push(`<p class="font-[&#39;Jost&#39;,sans-serif] text-[14px] text-[#c9a84c] leading-tight">${ssrInterpolate(formatPence(lineOfferPence(card)))} `);
					if (affiliateValid.value) _push(`<span class="text-[10px] text-[#7fd4a0]">(+${ssrInterpolate(bonusPercentLabel.value)})</span>`);
					else _push(`<!---->`);
					_push(`</p></div><button type="button" class="text-[#a3a3a3] hover:text-red-400 shrink-0 text-[18px] leading-none px-[4px]"> × </button></div>`);
				});
				_push(`<!--]--></div><div class="flex items-center justify-between w-full pt-[8px] border-t border-[#3d2f6e]"><p class="font-[&#39;Jost&#39;,sans-serif] text-[15px] text-white">Total offer</p><div class="text-right">`);
				if (affiliateValid.value) _push(`<p class="font-[&#39;Jost&#39;,sans-serif] text-[13px] text-[#71717a] line-through">${ssrInterpolate(formatPence(totalBaseOfferPence.value))}</p>`);
				else _push(`<!---->`);
				_push(`<p class="font-[&#39;Jost&#39;,sans-serif] font-bold text-[20px] text-[#c9a84c]">${ssrInterpolate(formatPence(totalOfferPence.value))} `);
				if (affiliateValid.value) _push(`<span class="text-[12px] font-normal text-[#7fd4a0]">(+${ssrInterpolate(bonusPercentLabel.value)})</span>`);
				else _push(`<!---->`);
				_push(`</p></div></div>`);
				if (unref(form).errors.items) _push(`<div class="text-[11px] text-red-400">${ssrInterpolate(unref(form).errors.items)}</div>`);
				else _push(`<!---->`);
				_push(`</div>`);
			} else _push(`<!---->`);
			_push(`<div class="w-full h-px bg-[rgba(124,58,237,0.2)] shrink-0"></div><div class="content-stretch flex flex-col sm:flex-row gap-[24px] items-start relative shrink-0 w-full"><div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative w-full"><div class="content-stretch flex items-center relative shrink-0"><label for="customer_name" class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap"> Name</label></div><div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full"><div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]"></div><div class="flex flex-row items-center size-full"><div class="content-stretch flex items-center p-[14px] relative size-full"><input id="customer_name" type="text"${ssrRenderAttr("value", unref(form).customer_name)} class="w-full bg-transparent border-none outline-none text-[15px] text-white font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none"></div></div>`);
			if (unref(form).errors.customer_name) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors.customer_name)}</div>`);
			else _push(`<!---->`);
			_push(`</div></div><div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative w-full"><div class="content-stretch flex items-center relative shrink-0"><label for="customer_email" class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap"> Email address</label></div><div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full"><div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]"></div><div class="flex flex-row items-center size-full"><div class="content-stretch flex items-center p-[14px] relative size-full"><input id="customer_email" type="email"${ssrRenderAttr("value", unref(form).customer_email)} class="w-full bg-transparent border-none outline-none text-[15px] text-white font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none"></div></div>`);
			if (unref(form).errors.customer_email) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors.customer_email)}</div>`);
			else _push(`<!---->`);
			_push(`</div></div></div><div class="content-stretch flex flex-col sm:flex-row gap-[24px] items-start relative shrink-0 w-full"><div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative w-full"><div class="content-stretch flex items-center relative shrink-0"><label for="customer_phone" class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap"> Phone number <small>(optional)</small></label></div><div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full"><div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]"></div><div class="flex flex-row items-center size-full"><div class="content-stretch flex items-center p-[14px] relative size-full"><input id="customer_phone" type="text"${ssrRenderAttr("value", unref(form).customer_phone)} class="w-full bg-transparent border-none outline-none text-[15px] text-white font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none"></div></div>`);
			if (unref(form).errors.customer_phone) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors.customer_phone)}</div>`);
			else _push(`<!---->`);
			_push(`</div></div><div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative w-full"><div class="content-stretch flex items-center relative shrink-0"><label for="customer_postcode" class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap"> Postcode <small>(optional)</small></label></div><div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full"><div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]"></div><div class="flex flex-row items-center size-full"><div class="content-stretch flex items-center p-[14px] relative size-full"><input id="customer_postcode" type="text"${ssrRenderAttr("value", unref(form).customer_postcode)} class="w-full bg-transparent border-none outline-none text-[15px] text-white font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none"></div></div>`);
			if (unref(form).errors.customer_postcode) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors.customer_postcode)}</div>`);
			else _push(`<!---->`);
			_push(`</div></div></div><div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full"><div class="content-stretch flex items-center relative shrink-0"><label for="description" class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap"> Anything else we should know? <small>(optional)</small></label></div><div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] relative rounded-[6px] shrink-0 w-full"><div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]"></div><div class="flex flex-row items-center size-full"><div class="content-stretch flex items-center p-[14px] relative size-full"><textarea id="description" rows="3" class="w-full bg-transparent border-none outline-none text-[15px] text-white font-[&#39;Jost&#39;,sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" placeholder="Any condition notes or other details…">${ssrInterpolate(unref(form).description)}</textarea></div></div>`);
			if (unref(form).errors.description) _push(`<div class="text-[11px] text-red-400 mt-1">${ssrInterpolate(unref(form).errors.description)}</div>`);
			else _push(`<!---->`);
			_push(`</div></div><div class="content-stretch flex flex-col gap-[16px] items-start relative shrink-0 w-full"><button type="submit"${ssrIncludeBooleanAttr(unref(form).processing || selected.value.length === 0) ? " disabled" : ""} class="content-stretch drop-shadow-[0px_0px_9px_rgba(201,168,76,0.25)] flex h-[56px] items-start justify-center py-[16px] relative rounded-[4px] shrink-0 w-full disabled:opacity-50" style="${ssrRenderStyle({ "background-image": "linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%)" })}" data-name="Frame"><p class="[word-break:break-word] font-[&#39;Jost&#39;,sans-serif] font-bold leading-[normal] relative shrink-0 text-[#0d0b14] text-[16px] uppercase whitespace-nowrap">`);
			if (unref(form).processing) _push(`<span>Submitting…</span>`);
			else if (selected.value.length === 0) _push(`<span>Add a card to continue</span>`);
			else _push(`<span>Submit (${ssrInterpolate(formatPence(totalOfferPence.value))})</span>`);
			_push(`</p></button></div></div></div></div></div></main>`);
			_push(ssrRenderComponent(Footer_default, null, null, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/ts/Pages/Sell/Create.vue
var _sfc_setup = Create_vue_vue_type_script_setup_true_lang_default.setup;
Create_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/ts/Pages/Sell/Create.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Create_default = Create_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Create_default as default };

//# sourceMappingURL=Create-Chj1iRcN.js.map