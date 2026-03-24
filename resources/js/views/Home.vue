<template>
  <div>
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-slate-950 text-white py-20 sm:py-24">
      <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(20,184,166,0.28),transparent_40%),radial-gradient(circle_at_bottom_left,rgba(14,165,233,0.18),transparent_38%)]"></div>
      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="inline-flex items-center rounded-full border border-white/20 bg-white/5 px-4 py-1.5 text-xs font-semibold tracking-widest text-primary-100 mb-5">ARONTRADE B2B PLATFORM</p>
        <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-6">
          Zgjidhja Juaj për Furnizime
        </h1>
        <p class="text-lg md:text-2xl mb-8 text-slate-200 max-w-3xl mx-auto">
          Cilësi e lartë, shërbim i besueshëm dhe produkte të zgjedhura me kujdes për biznesin tuaj
        </p>
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center items-stretch sm:items-center max-w-md mx-auto">
          <router-link 
            to="/produktet" 
            class="w-full sm:w-auto inline-flex justify-center items-center bg-gradient-to-r from-cyan-400 to-primary-500 text-white font-semibold py-3 px-8 rounded-xl hover:from-cyan-500 hover:to-primary-600 transition-all duration-200 text-lg sm:text-sm shadow-lg shadow-primary-900/20 hover:shadow-xl hover:shadow-primary-900/30 hover:-translate-y-0.5 ring-1 ring-white/20"
          >
            Shiko Produktet
          </router-link>
          <template v-if="isClientIdentified">
            <router-link 
              to="/shporta" 
              class="w-full sm:w-auto inline-flex justify-center items-center bg-gradient-to-r from-violet-500 to-indigo-600 text-white font-semibold py-3 px-8 rounded-xl hover:from-violet-600 hover:to-indigo-700 border border-white/20 transition-all duration-200 text-lg shadow-md shadow-indigo-900/25 hover:shadow-lg hover:shadow-indigo-900/30 hover:-translate-y-0.5"
            >
              Shporta
            </router-link>
            <button
              type="button"
              @click="logoutClient"
              class="w-full sm:w-auto inline-flex justify-center items-center bg-white/10 text-white font-semibold py-3 px-8 rounded-xl border border-white/45 hover:bg-white/20 hover:border-white/60 transition-all duration-200 text-lg shadow-sm backdrop-blur-sm hover:-translate-y-0.5"
            >
              Dil
            </button>
          </template>
          <template v-else>
            <router-link 
              to="/kycu" 
              class="w-full sm:w-auto inline-flex justify-center items-center bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold py-3 px-8 rounded-xl hover:from-primary-400 hover:to-primary-500 border border-white/45 transition-all duration-200 text-lg shadow-md hover:shadow-lg hover:-translate-y-0.5"
            >
              Kyçu
            </router-link>
            <router-link 
              to="/shporta" 
              class="w-full sm:w-auto inline-flex justify-center items-center border border-white/60 text-white font-semibold py-3 px-8 rounded-xl hover:bg-white/10 transition-all duration-200 text-lg hover:-translate-y-0.5"
            >
              Shporta
            </router-link>
          </template>
        </div>
        <p class="mt-4 text-primary-100 text-sm" v-if="isClientIdentified">
          ✓ I identifikuar: <strong>{{ clientDisplayName }}</strong>
        </p>
        <p class="mt-4 text-primary-100 text-sm" v-else>
          Keni llogari? <router-link to="/kycu" class="underline hover:no-underline">Kyçuni</router-link> me email dhe fjalëkalim për të parë çmimet tuaja.
        </p>
        <p class="mt-2 text-primary-200/90 text-xs max-w-xl mx-auto">
          Çmimet për klientët e regjistruar vendosen nga kompania AronTrade, jo nga klientët.
        </p>
      </div>
    </section>

    <!-- Featured Products Section -->
    <section class="py-16 bg-gradient-to-b from-white to-slate-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-slate-900 mb-4">
            Produktet Tona Kryesore
          </h2>
          <p class="text-lg text-slate-600">
            Zgjidhni nga një gamë e gjerë produktesh të cilësisë së lartë
          </p>
        </div>
        <div class="max-w-3xl mx-auto mb-10">
          <div class="relative">
            <input
              v-model.trim="searchQuery"
              type="search"
              placeholder="Kërko për produkt (p.sh. Pipëza, Letër Kuzhine...)"
              class="w-full px-4 py-3 pl-11 border border-slate-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
            >
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
              🔍
            </span>
            <button
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"
              aria-label="Pastro kërkimin"
            >
              ✖
            </button>
          </div>
          <p class="text-sm text-gray-500 mt-2 text-center">
            Kërko menjëherë brenda produkteve kryesore.
          </p>
        </div>
        
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          <p class="mt-4 text-gray-600">Duke ngarkuar produktet...</p>
        </div>
        
        <div v-else-if="filteredFeaturedProducts.length" class="space-y-10">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <ProductCard 
              v-for="product in displayedFeaturedProducts" 
              :key="product.id" 
              :product="product"
            />
          </div>
          <div v-if="showMoreFeaturedVisible" class="text-center">
            <button
              type="button"
              @click="showMoreFeatured = true"
              class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 shadow-md hover:shadow-lg transition-all duration-200"
            >
              Më shumë
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </div>
        </div>
        <div v-else class="bg-white rounded-2xl shadow-md p-10 text-center text-gray-600 border border-slate-100">
          <p class="text-2xl mb-2">😕 Nuk u gjet asnjë produkt</p>
          <p>Provoni një term tjetër kërkimi ose hiqni filtrin.</p>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-slate-900 mb-4">
            Kategoritë Tona
          </h2>
          <p class="text-xl text-gray-600">
            Organizuar sipas nevojave tuaja
          </p>
        </div>
        
        <div v-if="categoriesLoading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          <p class="mt-4 text-gray-600">Duke ngarkuar kategoritë...</p>
        </div>
        
        <div v-else class="grid grid-cols-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
          <div 
            v-for="category in categories" 
            :key="category.id"
            class="group rounded-2xl border border-slate-200 bg-white/80 backdrop-blur-sm p-3 sm:p-4 lg:p-6 text-center shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 cursor-pointer"
            @click="goToCategory(category.slug)"
          >
            <div class="text-primary-600 text-2xl sm:text-3xl lg:text-4xl mb-2 sm:mb-3 lg:mb-4 transition-transform duration-300 group-hover:scale-110">
              {{ getCategoryIcon(category.slug) }}
            </div>
            <h3 class="text-xs sm:text-sm lg:text-xl font-semibold text-slate-900 mb-1 sm:mb-1.5 lg:mb-2 leading-tight">
              {{ category.name }}
            </h3>
            <p class="text-slate-600 text-[10px] sm:text-xs lg:text-sm">
              {{ category.products_count }} produkte
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import ProductCard from '../components/ProductCard.vue'
import cartStore from '../store/cart'
import axios from 'axios'

export default {
  name: 'Home',
  components: {
    ProductCard
  },
  data() {
    return {
      featuredProducts: [],
      categories: [],
      loading: true,
      categoriesLoading: true,
      searchQuery: '',
      showMoreFeatured: false,
      featuredLimitDesktop: 21
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
    filteredFeaturedProducts() {
      if (!this.searchQuery) {
        return this.featuredProducts
      }
      const query = this.searchQuery.toLowerCase()
      return this.featuredProducts.filter(product => {
        return (
          product.name?.toLowerCase().includes(query) ||
          product.description?.toLowerCase().includes(query) ||
          product.category?.name?.toLowerCase().includes(query)
        )
      })
    },
    displayedFeaturedProducts() {
      if (this.showMoreFeatured) return this.filteredFeaturedProducts
      return this.filteredFeaturedProducts.slice(0, this.featuredLimitDesktop)
    },
    showMoreFeaturedVisible() {
      return !this.showMoreFeatured && this.filteredFeaturedProducts.length > this.featuredLimitDesktop
    }
  },
  watch: {
    searchQuery() {
      this.showMoreFeatured = false
    }
  },
  async mounted() {
    await this.loadFeaturedProducts()
    await this.loadCategories()
  },
  methods: {
    async loadFeaturedProducts() {
      try {
        const response = await axios.get('/api/products/featured')
        this.featuredProducts = response.data.data
      } catch (error) {
        console.error('Error loading featured products:', error)
      } finally {
        this.loading = false
      }
    },
    async loadCategories() {
      try {
        const response = await axios.get('/api/categories')
        const list = response.data.data || []
        this.categories = list.slice().sort((a, b) => {
          const an = (a.name || '').toString().trim()
          const bn = (b.name || '').toString().trim()
          return an.localeCompare(bn, 'sq', { sensitivity: 'base' })
        })
      } catch (error) {
        console.error('Error loading categories:', error)
      } finally {
        this.categoriesLoading = false
      }
    },
    goToCategory(slug) {
      this.$router.push(`/produktet?kategoria=${slug}`)
    },
    logoutClient() {
      cartStore.logoutSession()
    },
    getCategoryIcon(slug) {
      const icons = {
        'pipeza': '🥤',
        'perzieres': '🥄',
        'alumin-flete': '📄',
        'peceta-leter': '🧻',
        'mbeturina': '🗑️',
        'te-lagura': '💧',
        'te-tjera': '📦'
      }
      return icons[slug] || '📦'
    }
  }
}
</script>

