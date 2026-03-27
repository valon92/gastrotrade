<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-slate-100/60">
    <!-- Hero / Header (desktop/tablet — në mobile shko direkt te kartat) -->
    <section class="hidden md:block relative overflow-hidden bg-slate-950 text-white">
      <div aria-hidden="true" class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.35),transparent_36%),radial-gradient(circle_at_bottom_right,rgba(37,99,235,0.25),transparent_34%)]" />
      <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'80\' height=\'80\' viewBox=\'0 0 80 80\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.04\'%3E%3Ccircle cx=\'6\' cy=\'6\' r=\'2\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]" aria-hidden="true" />
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-14">
        <p class="inline-flex items-center rounded-full border border-white/20 bg-white/5 px-4 py-1.5 text-[11px] sm:text-xs font-semibold tracking-[0.14em] text-primary-100 mb-4">
          ARONTRADE • KATALOGU B2B
        </p>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight mb-3">
          Produktet tona
        </h1>
        <p class="text-slate-200 text-lg sm:text-xl max-w-3xl mb-7">
          Zgjidhni nga gamë e gjerë produktesh të cilësisë së lartë – kese, letër, artikuj kuzhine dhe më shumë.
        </p>
        <div class="flex flex-wrap items-center gap-2.5 text-sm">
          <a href="tel:+38348756646" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-slate-100 hover:bg-white/20 transition-colors">📞 048 75 66 46</a>
          <a href="tel:+38344824314" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-slate-100 hover:bg-white/20 transition-colors">📞 044 82 43 14</a>
          <span class="inline-flex items-center gap-2 rounded-full border border-emerald-300/35 bg-emerald-500/10 px-3 py-1.5 text-emerald-100">💬 Viber në dispozicion</span>
        </div>
        <template v-if="isClientIdentified">
          <p class="mt-4 text-primary-100 text-sm">✓ I identifikuar: <strong>{{ clientDisplayName }}</strong></p>
          <button
            type="button"
            @click="logoutClient"
            class="inline-flex items-center gap-2 mt-3 px-5 py-2.5 bg-white/15 text-white font-semibold rounded-xl hover:bg-white/25 border border-white/35 transition-all duration-200"
          >
            Dil
          </button>
        </template>
        <template v-else>
          <router-link
            to="/kycu"
            class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-white text-slate-900 font-semibold rounded-xl hover:bg-slate-100 shadow-lg hover:shadow-xl transition-all duration-200"
          >
            Kyçu për çmimet tuaja
          </router-link>
        </template>
        <p class="mt-3 text-slate-300 text-sm">
          Çmimet vendosen nga AronTrade për klientët e regjistruar.
        </p>
      </div>
    </section>

    <!-- Search (desktop/tablet — në mobile pa hero/kërkim, vetëm kartat) -->
    <div class="hidden md:block relative z-0 bg-white/92 backdrop-blur-xl border-b border-slate-200/80 shadow-[0_6px_20px_rgba(15,23,42,0.04)] md:sticky md:top-0 md:z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
          <div class="relative max-w-2xl w-full">
          <span class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </span>
          <input
            v-model.trim="searchQuery"
            type="search"
            placeholder="Kërko produkt (p.sh. kese, letër kuzhine...)"
            class="w-full pl-12 pr-10 py-3 border border-slate-200 rounded-2xl bg-slate-50/70 focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 focus:outline-none transition-all text-slate-900 placeholder-slate-500"
          />
          <button
            v-if="searchQuery"
            type="button"
            @click="searchQuery = ''"
            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 rounded-lg p-1"
            aria-label="Pastro kërkimin"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          </div>
          <div class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs sm:text-sm text-slate-600 w-fit">
            <span class="font-semibold text-slate-700">Kategori aktive:</span>
            <span class="inline-flex items-center justify-center min-w-7 h-7 px-2 rounded-lg bg-primary-50 text-primary-700 font-bold">{{ visibleCategorySlugs.length }}</span>
          </div>
        </div>
        <p class="text-xs text-slate-500 mt-2">Kërkimi aplikohet menjëherë mbi të gjitha kategoritë.</p>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 md:py-8 lg:py-10">
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="w-12 h-12 border-3 border-primary-200 border-t-primary-600 rounded-full animate-spin" />
        <p class="mt-4 text-gray-600 font-medium">Duke ngarkuar produktet...</p>
      </div>

      <template v-else>
        <!-- No results -->
        <div v-if="visibleCategorySlugs.length === 0" class="text-center py-16 rounded-3xl bg-white border border-slate-200 shadow-sm">
          <div class="text-5xl text-gray-300 mb-4">📦</div>
          <h2 class="text-xl font-semibold text-gray-800 mb-2">Nuk u gjetën produkte</h2>
          <p class="text-gray-600">Provo një kërkim tjetër ose pastro fjalët kyçe.</p>
          <button
            type="button"
            @click="searchQuery = ''"
            class="mt-4 px-4 py-2 text-primary-600 font-medium hover:bg-primary-50 rounded-lg transition-colors"
          >
            Pastro kërkimin
          </button>
        </div>

        <!-- Mobile: vetëm kartat e produkteve (pa kategori), 3 në rresht -->
        <div v-else class="md:hidden">
          <div class="grid grid-cols-3 gap-2">
            <ProductCard
              v-for="product in filteredProducts"
              :key="`mobile-${product.id}`"
              :product="product"
              :compact-button="true"
            />
          </div>
        </div>

        <!-- Desktop/Tablet: sipas kategorive -->
        <div class="hidden md:block space-y-4">
          <div
            v-for="cat in visibleCategories"
            :key="cat.slug"
            :data-category="cat.slug"
            class="rounded-3xl border border-slate-200/90 bg-white shadow-sm overflow-hidden transition-all hover:shadow-lg hover:shadow-slate-900/5"
          >
            <button
              type="button"
              @click="toggleSection(cat.slug)"
              class="w-full flex items-center justify-between gap-4 px-5 sm:px-6 py-4 text-left bg-gradient-to-r from-slate-50 to-white hover:from-primary-50/60 hover:to-white border-b border-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 rounded-t-3xl"
              :aria-expanded="expandedSections[cat.slug]"
            >
              <div class="flex items-center gap-3 min-w-0">
                <span
                  class="flex-shrink-0 w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center shadow-sm"
                  aria-hidden="true"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                </span>
                <div class="min-w-0">
                  <h2 class="text-lg font-extrabold tracking-tight text-slate-900 truncate">{{ cat.name }}</h2>
                  <p class="text-sm text-slate-500">{{ productsByCategory[cat.slug]?.length || 0 }} produkt(e)</p>
                </div>
              </div>
              <span
                class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-b from-primary-600 to-primary-700 text-white flex items-center justify-center transition-transform shadow-md ring-1 ring-primary-700/40"
                aria-hidden="true"
              >
                <svg
                  v-if="expandedSections[cat.slug]"
                  class="w-5 h-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                  aria-label="Fsheh produktet"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
                <svg
                  v-else
                  class="w-5 h-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                  aria-label="Shfaq produktet"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </span>
            </button>

            <Transition
              enter-active-class="transition-all duration-300 ease-out"
              enter-from-class="opacity-0 max-h-0"
              enter-to-class="opacity-100 max-h-[5000px]"
              leave-active-class="transition-all duration-250 ease-in"
              leave-from-class="opacity-100 max-h-[5000px]"
              leave-to-class="opacity-0 max-h-0"
            >
              <div v-show="expandedSections[cat.slug]" class="overflow-hidden">
                <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6 bg-gradient-to-b from-white to-slate-50/35">
                  <ProductCard
                    v-for="product in productsByCategory[cat.slug]"
                    :key="product.id"
                    :product="product"
                    :compact-button="true"
                  />
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import ProductCard from '../components/ProductCard.vue'
import cartStore from '../store/cart'
import axios from 'axios'

export default {
  name: 'Products',
  components: { ProductCard },
  data() {
    return {
      products: [],
      categories: [],
      loading: true,
      searchQuery: '',
      expandedSections: {} // slug -> true/false
    }
  },
  computed: {
    isClientIdentified() {
      return !!cartStore.client
    },
    clientDisplayName() {
      if (!cartStore.client) return ''
      const c = cartStore.client
      return c.name || c.store_name || c.email || 'Klient'
    },
    filteredProducts() {
      const cmp = (a, b) => {
        const sa = Number(a.sort_order) || 0
        const sb = Number(b.sort_order) || 0
        if (sa !== sb) return sa - sb
        const c = (a.name || '').localeCompare(b.name || '', 'sq', { sensitivity: 'base' })
        if (c !== 0) return c
        return (a.id || 0) - (b.id || 0)
      }
      const q = this.searchQuery.trim().toLowerCase()
      let list
      if (!q) {
        list = this.products.slice()
      } else {
        list = this.products.filter(
          (p) =>
            p.name?.toLowerCase().includes(q) ||
            p.description?.toLowerCase().includes(q) ||
            p.category?.name?.toLowerCase().includes(q)
        )
      }
      return list.sort(cmp)
    },
    productsByCategory() {
      const map = {}
      const cmp = (a, b) => {
        const sa = Number(a.sort_order) || 0
        const sb = Number(b.sort_order) || 0
        if (sa !== sb) return sa - sb
        const c = (a.name || '').localeCompare(b.name || '', 'sq', { sensitivity: 'base' })
        if (c !== 0) return c
        return (a.id || 0) - (b.id || 0)
      }
      for (const cat of this.categories) {
        const list = this.filteredProducts.filter((p) => p.category?.slug === cat.slug)
        map[cat.slug] = list.slice().sort(cmp)
      }
      return map
    },
    visibleCategories() {
      const withProducts = this.categories.filter(
        (cat) => (this.productsByCategory[cat.slug]?.length || 0) > 0
      )
      return withProducts.slice().sort((a, b) => {
        const pa = this.productsByCategory[a.slug] || []
        const pb = this.productsByCategory[b.slug] || []
        const minA = pa.length ? Math.min(...pa.map((p) => Number(p.sort_order) || 0)) : 999999
        const minB = pb.length ? Math.min(...pb.map((p) => Number(p.sort_order) || 0)) : 999999
        if (minA !== minB) return minA - minB
        return (a.name || '').localeCompare(b.name || '', 'sq', { sensitivity: 'base' })
      })
    },
    visibleCategorySlugs() {
      return this.visibleCategories.map((c) => c.slug)
    }
  },
  watch: {
    visibleCategorySlugs: {
      immediate: true,
      handler(slugs) {
        const fromUrl = this.$route.query.kategoria
        if (fromUrl && slugs.includes(fromUrl)) {
          const next = {}
          slugs.forEach((slug) => { next[slug] = slug === fromUrl })
          this.expandedSections = { ...this.expandedSections, ...next }
          this.$nextTick(() => {
            const el = document.querySelector(`[data-category="${fromUrl}"]`)
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
          })
          return
        }
        const next = { ...this.expandedSections }
        slugs.forEach((slug) => {
          if (next[slug] === undefined) next[slug] = true
        })
        this.expandedSections = next
      }
    }
  },
  async mounted() {
    window.scrollTo({ top: 0, behavior: 'smooth' })
    await this.loadCategories()
    await this.loadProducts()
  },
  methods: {
    logoutClient() {
      cartStore.logoutSession()
    },
    toggleSection(slug) {
      this.expandedSections = { ...this.expandedSections, [slug]: !this.expandedSections[slug] }
    },
    async loadProducts() {
      this.loading = true
      try {
        const { data } = await axios.get('/api/products')
        this.products = data.data || []
      } catch (e) {
        console.error('Error loading products:', e)
        this.products = []
      } finally {
        this.loading = false
      }
    },
    async loadCategories() {
      try {
        const { data } = await axios.get('/api/categories')
        const list = data.data || []
        // Rendit kategoritë alfabetikisht sipas emrit
        this.categories = list.slice().sort((a, b) => {
          const an = (a.name || '').toString().trim()
          const bn = (b.name || '').toString().trim()
          return an.localeCompare(bn, 'sq', { sensitivity: 'base' })
        })
      } catch (e) {
        console.error('Error loading categories:', e)
        this.categories = []
      }
    }
  }
}
</script>
