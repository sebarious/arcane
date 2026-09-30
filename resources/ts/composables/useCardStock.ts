import { computed, ref } from 'vue';
import axios from 'axios';

export interface StockCard {
  id: number;
  card_name: string;
  set_name: string | null;
  card_number: string | null;
  rarity: string | null;
  image_url: string | null;
  price_pence: number;
  product_badges: string[];
}

/**
 * Everything the kiosk and the public catalogue both do with sellable stock:
 * typed search, A-Z browse, set/rarity filters, and the shuffled "featured"
 * landing view — all against the same /kiosk endpoints.
 *
 * Shared because the two pages had already drifted into near-identical
 * copies of this logic, and every feature added since has had to be written
 * twice. The pages keep their own layout and extras (the kiosk's basket, the
 * catalogue's nav) and take only the data plumbing from here.
 */
export function useCardStock() {
  const query = ref('');
  const results = ref<StockCard[]>([]);
  const searching = ref(false);
  const hasSearched = ref(false);

  const browseLetter = ref<string | null>(null);
  const browseResults = ref<StockCard[]>([]);
  const browsePage = ref(1);
  const browseHasMore = ref(true);
  const browseLoading = ref(false);

  const activeSet = ref<string | null>(null);
  const activeRarity = ref<string | null>(null);
  const gradedOnly = ref(false);
  const filterSets = ref<string[]>([]);
  const filterRarities = ref<string[]>([]);
  const hasGradedStock = ref(false);
  const setSearch = ref('');

  // One shuffle per visit. Generated here and sent with every page so the
  // featured order holds still while someone scrolls it — a server-side
  // random would reshuffle per request and show duplicates.
  const seed = Math.floor(Math.random() * 999999) + 1;

  const searchMode = computed(() => query.value.trim().length >= 2);
  const hasFilters = computed(() => activeSet.value !== null || activeRarity.value !== null || gradedOnly.value);

  /** The landing view: nothing typed, no letter, no filters. */
  const isFeatured = computed(() => !searchMode.value && browseLetter.value === null && !hasFilters.value);

  /** Anything that isn't a typed search comes from the paginated browse endpoint. */
  const listMode = computed(() => !searchMode.value);

  const displayResults = computed(() => (listMode.value ? browseResults.value : results.value));

  // 80-odd sets is far too many to thumb through on a tablet, so the picker
  // narrows as you type.
  const visibleSets = computed(() => {
    const q = setSearch.value.trim().toLowerCase();
    return q ? filterSets.value.filter((s) => s.toLowerCase().includes(q)) : filterSets.value;
  });

  function filterParams(): Record<string, string> {
    const params: Record<string, string> = {};
    if (activeSet.value) params.set = activeSet.value;
    if (activeRarity.value) params.rarity = activeRarity.value;
    if (gradedOnly.value) params.graded = '1';
    return params;
  }

  let debounceTimer: ReturnType<typeof setTimeout> | undefined;

  function scheduleSearch() {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(runSearch, 350);
  }

  async function runSearch() {
    if (!searchMode.value) {
      results.value = [];
      hasSearched.value = false;
      // Dropped below two characters — fall back to whatever the filters
      // (or nothing at all) should be showing, rather than an empty panel.
      restartList();
      return;
    }

    // Typing replaces an A-Z selection, but keeps filters applied.
    browseLetter.value = null;
    searching.value = true;
    hasSearched.value = true;

    try {
      const { data } = await axios.get('/kiosk/search', {
        params: { q: query.value.trim(), ...filterParams() },
      });
      results.value = data.data ?? [];
    } catch {
      results.value = [];
    } finally {
      searching.value = false;
    }
  }

  async function loadPage() {
    if (!listMode.value || browseLoading.value || !browseHasMore.value) return;

    browseLoading.value = true;

    try {
      const { data } = await axios.get('/kiosk/browse', {
        params: {
          ...(browseLetter.value ? { letter: browseLetter.value } : {}),
          ...(isFeatured.value ? { featured: 1, seed } : {}),
          page: browsePage.value,
          ...filterParams(),
        },
      });
      browseResults.value.push(...(data.data ?? []));
      browseHasMore.value = Boolean(data.has_more);
      browsePage.value += 1;
    } catch {
      browseHasMore.value = false;
    } finally {
      browseLoading.value = false;
    }
  }

  /** Reloads the list from page 1 — after a filter change, or when a search is abandoned. */
  function restartList() {
    browseResults.value = [];
    browsePage.value = 1;
    browseHasMore.value = true;
    loadPage();
  }

  function selectLetter(letter: string) {
    query.value = '';
    results.value = [];
    hasSearched.value = false;
    browseLetter.value = letter;
    restartList();
  }

  /** Drops the letter but keeps any filters, reloading whatever those still match. */
  function clearLetter() {
    browseLetter.value = null;
    restartList();
  }

  async function loadFilterOptions() {
    try {
      const { data } = await axios.get('/kiosk/filters');
      filterSets.value = data.data?.sets ?? [];
      filterRarities.value = data.data?.rarities ?? [];
      hasGradedStock.value = Boolean(data.data?.has_graded);
    } catch {
      // Non-fatal — search and browse still work unfiltered.
    }
  }

  /** Re-runs whichever view is showing, so a filter change lands immediately. */
  function applyFilters() {
    searchMode.value ? runSearch() : restartList();
  }

  function toggleRarity(rarity: string) {
    activeRarity.value = activeRarity.value === rarity ? null : rarity;
    applyFilters();
  }

  function selectSet(set: string | null) {
    activeSet.value = set;
    setSearch.value = '';
    applyFilters();
  }

  function toggleGraded() {
    gradedOnly.value = !gradedOnly.value;
    applyFilters();
  }

  function clearFilters() {
    activeSet.value = null;
    activeRarity.value = null;
    gradedOnly.value = false;
    applyFilters();
  }

  /** Back to the featured landing view. */
  function reset() {
    query.value = '';
    results.value = [];
    hasSearched.value = false;
    browseLetter.value = null;
    restartList();
  }

  // Infinite scroll — fetch the next page a little before the user actually
  // hits the bottom, so it's already loaded by the time they get there.
  function onScroll(event: Event) {
    if (!listMode.value) return;

    const el = event.target as HTMLElement;
    if (el.scrollHeight - el.scrollTop - el.clientHeight < 200) loadPage();
  }

  /** Removes a card from the visible list once it's been claimed, without reloading. */
  function removeFromResults(id: number) {
    browseResults.value = browseResults.value.filter((c) => c.id !== id);
    results.value = results.value.filter((c) => c.id !== id);
  }

  function init() {
    loadFilterOptions();
    restartList();
  }

  return {
    query, results, searching, hasSearched,
    browseLetter, browseResults, browseLoading, browseHasMore,
    activeSet, activeRarity, gradedOnly, filterSets, filterRarities, hasGradedStock, setSearch,
    searchMode, hasFilters, isFeatured, listMode, displayResults, visibleSets,
    scheduleSearch, runSearch, loadPage, restartList, selectLetter, clearLetter,
    loadFilterOptions, applyFilters, toggleRarity, selectSet, toggleGraded, clearFilters,
    reset, onScroll, removeFromResults, init,
  };
}
