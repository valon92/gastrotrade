<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <!-- Hero / Header -->
    <section class="relative overflow-hidden bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 text-white">
      <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-60" aria-hidden="true" />
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight mb-3">
          Produktet tona
        </h1>
        <p class="text-primary-100 text-lg sm:text-xl max-w-2xl mb-6">
          Zgjidhni nga gamë e gjerë produktesh të cilësisë së lartë – kese, letër, artikuj kuzhine dhe më shumë.
        </p>
        <div class="flex flex-wrap items-center gap-3 text-sm text-primary-100">
          <a href="tel:+38348756646" class="inline-flex items-center gap-2 hover:text-white transition-colors">📞 048 75 66 46</a>
          <a href="tel:+38344824314" class="inline-flex items-center gap-2 hover:text-white transition-colors">📞 044 82 43 14</a>
          <span class="hidden sm:inline text-primary-300">·</span>
          <span class="inline-flex items-center gap-2">💬 Viber në dispozicion</span>
        </div>
        <router-link
          to="/kycu"
          class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-white text-primary-700 font-semibold rounded-xl hover:bg-primary-50 shadow-lg hover:shadow-xl transition-all duration-200"
        >
          Kyçu – Të dhënat e biznesit për çmimet tuaja
        </router-link>
        <p class="mt-3 text-primary-200 text-sm">
          Çmimet vendosen nga GastroTrade për klientët e regjistruar.
        </p>
      </div>
    </section>

    <!-- Search -->
    <div class="sticky top-0 z-10 bg-white/95 backdrop-blur-md border-b border-gray-200/80 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="relative max-w-2xl">
          <span class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </span>
          <input
            v-model.trim="searchQuery"
            type="search"
            placeholder="Kërko produkt (p.sh. kese, letër kuzhine...)"
            class="w-full pl-12 pr-10 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 focus:outline-none transition-all text-gray-900 placeholder-gray-500"
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
        <p class="text-xs text-gray-500 mt-2">Kërkimi aplikohet menjëherë mbi të gjitha kategoritë.</p>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="w-12 h-12 border-3 border-primary-200 border-t-primary-600 rounded-full animate-spin" />
        <p class="mt-4 text-gray-600 font-medium">Duke ngarkuar produktet...</p>
      </div>

      <template v-else>
        <!-- No results -->
        <div v-if="visibleCategorySlugs.length === 0" class="text-center py-16 rounded-2xl bg-gray-50 border border-gray-100">
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

        <!-- Category sections: expand (minus) = show products, collapse (plus) = hide -->
        <div v-else class="space-y-4">
          <div
            v-for="cat in visibleCategories"
            :key="cat.slug"
            :data-category="cat.slug"
            class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden transition-shadow hover:shadow-md"
          >
            <button
              type="button"
              @click="toggleSection(cat.slug)"
              class="w-full flex items-center justify-between gap-4 px-5 sm:px-6 py-4 text-left bg-gray-50/80 hover:bg-primary-50/50 border-b border-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 rounded-t-2xl"
              :aria-expanded="expandedSections[cat.slug]"
            >
              <div class="flex items-center gap-3 min-w-0">
                <span
                  class="flex-shrink-0 w-10 h-10 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center"
                  aria-hidden="true"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                </span>
                <div class="min-w-0">
                  <h2 class="text-lg font-bold text-gray-900 truncate">{{ cat.name }}</h2>
                  <p class="text-sm text-gray-500">{{ productsByCategory[cat.slug]?.length || 0 }} produkt(e)</p>
                </div>
              </div>
              <span
                class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-600 text-white flex items-center justify-center transition-transform"
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
                <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
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
    filteredProducts() {
      if (!this.searchQuery.trim()) return this.products
      const q = this.searchQuery.toLowerCase().trim()
      return this.products.filter(
        (p) =>
          p.name?.toLowerCase().includes(q) ||
          p.description?.toLowerCase().includes(q) ||
          p.category?.name?.toLowerCase().includes(q)
      )
    },
    productsByCategory() {
      const map = {}
      for (const cat of this.categories) {
        map[cat.slug] = this.filteredProducts.filter((p) => p.category?.slug === cat.slug)
      }
      return map
    },
    visibleCategories() {
      return this.categories.filter((cat) => (this.productsByCategory[cat.slug]?.length || 0) > 0)
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
        this.categories = data.data || []
      } catch (e) {
        console.error('Error loading categories:', e)
        this.categories = []
      }
    }
  }
}
</script>
