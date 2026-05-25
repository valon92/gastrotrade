<template>
  <div class="min-h-screen overflow-x-hidden bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.12),transparent_32%),linear-gradient(180deg,#f8fafc_0%,#ffffff_44%,#f1f5f9_100%)]">
    <section class="sticky top-16 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div class="relative w-full md:max-w-xl">
            <span class="absolute inset-y-0 left-4 flex items-center text-slate-400 pointer-events-none">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
            <input
              v-model.trim="searchQuery"
              type="search"
              placeholder="Kërko në katalog..."
              class="w-full rounded-2xl border border-slate-200 bg-slate-50/80 py-3 pl-12 pr-11 text-slate-900 outline-none transition focus:border-primary-500 focus:bg-white focus:ring-2 focus:ring-primary-500"
            />
            <button
              v-if="searchQuery"
              type="button"
              class="absolute inset-y-0 right-3 flex items-center rounded-lg px-2 text-slate-400 hover:text-slate-700"
              aria-label="Pastro kërkimin"
              @click="searchQuery = ''"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
      <div v-if="loading" class="flex flex-col items-center justify-center rounded-3xl border border-slate-200 bg-white py-20 shadow-sm">
        <div class="h-12 w-12 rounded-full border-4 border-primary-100 border-t-primary-600 animate-spin"></div>
        <p class="mt-4 font-semibold text-slate-600">Duke ngarkuar katalogun...</p>
      </div>

      <div v-else-if="errorMessage" class="rounded-3xl border border-rose-200 bg-rose-50 p-8 text-center text-rose-700">
        <p class="font-semibold">{{ errorMessage }}</p>
      </div>

      <div v-else-if="visibleProducts.length === 0" class="rounded-3xl border border-slate-200 bg-white p-10 text-center shadow-sm">
        <p class="text-2xl font-extrabold text-slate-900">Nuk u gjet asnjë produkt</p>
        <p class="mt-2 text-slate-500">Provo një fjalë tjetër kërkimi ose pastro filtrin.</p>
      </div>

      <div v-else class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
        <div class="grid grid-cols-1 lg:grid-cols-4 xl:grid-cols-5 gap-px bg-slate-200">
          <router-link
            v-for="product in visibleProducts"
            :key="product.id"
            :to="product.slug ? `/produktet/${product.slug}` : '/produktet'"
            class="group relative flex items-center gap-4 bg-white p-3 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 lg:block lg:p-4"
          >
            <div class="h-28 w-36 shrink-0 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-100 lg:h-auto lg:w-full lg:aspect-[4/3]">
              <img
                :src="getProductImage(product)"
                :alt="product.name"
                class="h-full w-full object-contain object-center p-2 transition duration-500 group-hover:scale-[1.03]"
                loading="lazy"
                @error="handleImageError($event, product)"
              />
            </div>
            <div class="min-w-0 flex-1 lg:pt-3">
              <h3 class="text-left text-sm font-extrabold leading-snug tracking-tight text-slate-900 transition-colors group-hover:text-primary-700 sm:text-base lg:min-h-[2.5rem] lg:text-center lg:text-[0.95rem]">
                {{ product.name }}
              </h3>
            </div>
          </router-link>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import axios from 'axios'

const PLACEHOLDER_IMAGE_DATA_URL = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='400' viewBox='0 0 400 400'%3E%3Crect fill='%23e2e8f0' width='400' height='400'/%3E%3Cpath fill='%2394a3b8' d='M116 270h168l-48-64-36 43-27-34-57 55Zm30-140h108a18 18 0 0 1 18 18v104a18 18 0 0 1-18 18H146a18 18 0 0 1-18-18V148a18 18 0 0 1 18-18Zm0 16a2 2 0 0 0-2 2v104a2 2 0 0 0 2 2h108a2 2 0 0 0 2-2V148a2 2 0 0 0-2-2H146Z'/%3E%3C/svg%3E"

export default {
  name: 'Catalog',
  data() {
    return {
      products: [],
      loading: true,
      errorMessage: '',
      searchQuery: ''
    }
  },
  computed: {
    visibleProducts() {
      const query = this.searchQuery.toLowerCase()
      const list = query
        ? this.products.filter((product) => product.name?.toLowerCase().includes(query))
        : this.products.slice()

      return list.sort(this.compareProductsByWeight)
    }
  },
  mounted() {
    this.loadCatalog()
  },
  methods: {
    async loadCatalog() {
      this.loading = true
      this.errorMessage = ''
      try {
        const productsResponse = await axios.get('/api/products')
        this.products = productsResponse.data?.data || []
      } catch (error) {
        console.error('Catalog load failed:', error)
        this.errorMessage = 'Katalogu nuk u ngarkua. Ju lutem provoni përsëri.'
      } finally {
        this.loading = false
      }
    },
    compareProductsByWeight(a, b) {
      const wa = this.extractWeightRank(a)
      const wb = this.extractWeightRank(b)
      if (wa !== null && wb !== null && wa !== wb) return wb - wa
      if (wa !== null && wb === null) return -1
      if (wa === null && wb !== null) return 1

      const sa = Number(a.sort_order) || 0
      const sb = Number(b.sort_order) || 0
      if (sa !== sb) return sa - sb

      const nameCompare = (a.name || '').localeCompare(b.name || '', 'sq', { sensitivity: 'base' })
      if (nameCompare !== 0) return nameCompare
      return (a.id || 0) - (b.id || 0)
    },
    extractWeightRank(product) {
      const text = [product.liters, product.size, product.name]
        .filter(Boolean)
        .join(' ')
        .toLowerCase()
        .replace(/,/g, '.')

      const patterns = [
        { re: /(\d+(?:\.\d+)?)\s*(?:kg|kilogram)\b/, factor: 1000 },
        { re: /(\d+(?:\.\d+)?)\s*(?:g|gr|gram)\b/, factor: 1 },
        { re: /(\d+(?:\.\d+)?)\s*(?:l|lt|liter|litra)\b/, factor: 1000 },
        { re: /(\d+(?:\.\d+)?)\s*ml\b/, factor: 1 },
        { re: /(\d+(?:\.\d+)?)\s*oz\b/, factor: 28.35 },
        { re: /(\d+(?:\.\d+)?)\s*(?:cp|copa|cope)\b/, factor: 1 },
        { re: /(\d+(?:\.\d+)?)\s*(?:micron|mikron)\b/, factor: 1 }
      ]

      for (const pattern of patterns) {
        const match = text.match(pattern.re)
        if (match) return Number(match[1]) * pattern.factor
      }

      const numeric = text.match(/\b(\d+(?:\.\d+)?)\b/)
      return numeric ? Number(numeric[1]) : null
    },
    getProductImage(product) {
      const slug = product.slug || ''
      const name = (product.name || '').toLowerCase()

      const catalogImage = this.getCatalogImageOverride(slug, name)
      if (catalogImage) return catalogImage

      if (product?.image_url && String(product.image_url).trim()) return product.image_url
      if (product?.image_path && String(product.image_path).trim()) return product.image_path

      return PLACEHOLDER_IMAGE_DATA_URL
    },
    getCatalogImageOverride(slug, name) {
      const trashBagImage = this.getTrashBagImage(slug, name)
      if (trashBagImage) return trashBagImage

      if (slug === 'leter-kuzhine-nush-2-shtresa' || name.includes('leter kuzhine nush 2 shtresa')) return '/images/Pallomat/XL/foto1.png'
      if (slug === 'leter-salvete' || name.includes('leter salvete')) return '/images/Pallomat/Salvete/foto1.jpg'
      if (slug === 'palloma-toilet' || name.includes('palloma toilet')) return '/images/Pallomat/Toilet/foto1.jpg'
      if (slug === 'luga-kafe-e-bardh-10cp-kompleti' || name.includes('luga kafe')) return '/images/lugas1.jpeg'
      if (slug === 'gota-plastike' || name.includes('gota plastike')) return '/images/Gota Plastike/foto1.jpg'
      if (slug === 'gota-leter-per-kafe-4oz' || name.includes('4oz')) return '/images/4oz.jpeg'
      if (slug.includes('gota-leter-per-kafe') || name.includes('gota letre per kafe')) return '/images/IMG_6821.jpeg'
      if (slug === 'foli-alumini-10m' || name.includes('foli alumini')) return '/images/Foli/10m/foto1.jpg'
      if (slug === 'foli-najlloni' || name.includes('foli najlloni')) return '/images/IMG_6814.jpeg'
      if (slug === 'mbulese-tavoline-nush' || name.includes('mbulesë tavoline') || name.includes('mbulese tavoline')) return '/images/Mbulesa Tavoline/foto1.jpg'
      if (slug === 'piruna-eko' || name.includes('piruna eko')) return '/images/Elementet Plastike/Piruna/Pirun Plastike Eko/foto1.jpg'
      if (slug === 'pipeza-te-zi-per-pije-100cp-kompleti-20cp' || (name.includes('pipëza') && name.includes('zi') && name.includes('100cp'))) return '/images/Pipat/Pipa-100cp/Black/foto1.jpg'
      if (slug === 'pipeza-color-per-pije-100cp-kompleti-20cp' || slug === 'pipeza-transparente-per-pije-100cp-kompleti-20cp' || (name.includes('pipëza') && (name.includes('color') || name.includes('transparente')) && name.includes('100cp'))) return '/images/Pipat/Pipa-100cp/Color/foto1.jpg'
      if (name.includes('pipëza') && name.includes('zi')) return '/images/IMG_6822.jpeg'
      if (name.includes('pipëza') && name.includes('transparente')) return '/images/IMG_6819.jpeg'
      if (name.includes('pipëza') && name.includes('color')) return '/images/IMG_6816.jpeg'

      return null
    },
    getTrashBagImage(slug, name) {
      if (!slug.includes('kese-mbeturinash') && !name.includes('kese mbeturinash')) return null

      if (slug.includes('40l') || name.includes('40l')) return '/images/Kese Mbeturina/40L/foto1.png'
      if (slug.includes('70l') || name.includes('70l')) return '/images/Kese Mbeturina/70L/foto1.png'
      if (slug.includes('120l') || name.includes('120l')) return '/images/Kese Mbeturina/120L/foto1.png'
      if (slug.includes('150l') || name.includes('150l')) return '/images/Kese Mbeturina/150L/foto1.png'
      if (slug.includes('170l') || name.includes('170l')) return '/images/Kese Mbeturina/170L/foto1.png'
      if (slug.includes('200l') || name.includes('200l')) return '/images/Kese Mbeturina/200L/foto1.png'
      if (slug.includes('220l') || name.includes('220l')) return '/images/170lnush.jpeg'
      if (slug.includes('240l') || name.includes('240l')) return '/images/Kese Mbeturina/240L/foto1.png'
      if (slug.includes('270l') || name.includes('270l')) return '/images/Kese Mbeturina/270L/foto1.png'
      if (slug.includes('300l') || name.includes('300l')) return '/images/Kese Mbeturina/300L/foto1.png'

      return null
    },
    handleImageError(event, product) {
      const slug = product.slug || ''
      const name = (product.name || '').toLowerCase()

      if (this.getTrashBagImage(slug, name)) {
        event.target.src = PLACEHOLDER_IMAGE_DATA_URL
      } else {
        event.target.src = PLACEHOLDER_IMAGE_DATA_URL
      }
    }
  }
}
</script>
