<template>
  <div class="group relative rounded-2xl border border-slate-200 bg-white/80 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 h-full flex flex-col backdrop-blur-sm">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-primary-50/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    <!-- Imazhi me çmim mbi të (overlay) – i klikueshëm për zmadhim -->
    <div class="relative w-full aspect-square bg-slate-100 xl:aspect-[4/3] xl:min-h-[220px]">
      <button
        type="button"
        @click="showImageLightbox = true"
        class="block w-full h-full focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-inset rounded-t-2xl overflow-hidden cursor-zoom-in"
        :aria-label="'Zmadho imazhin: ' + product.name"
      >
        <img 
          :src="getProductImage()" 
          :alt="product.name"
          class="h-full w-full min-h-0 object-cover object-center group-hover:scale-[1.04] transition-transform duration-500"
          @error="handleImageError"
        />
      </button>
      <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/35 via-black/10 to-transparent xl:h-24 xl:from-black/45"></div>
      <div
        v-if="displayPrice != null"
        class="absolute left-1 top-1 z-10 flex max-w-[min(58%,7rem)] flex-nowrap items-start gap-0 xl:left-3 xl:top-3 xl:max-w-[92%] xl:items-center xl:gap-2"
      >
        <div class="inline-flex shrink-0 items-center gap-0.5 rounded-md bg-gradient-to-r from-rose-600 to-red-600 px-1.5 py-0.5 text-[9px] font-bold tabular-nums leading-tight text-white shadow-sm ring-1 ring-red-900/30 backdrop-blur-sm xl:gap-1.5 xl:rounded-xl xl:px-3 xl:py-1.5 xl:text-sm xl:shadow-lg xl:ring-1 xl:ring-red-800/40">
          <span class="max-xl:truncate">{{ formatPrice(displayPrice) }}</span>
          <button
            v-if="hasPackageInfo"
            @click.stop="showInfoTooltip = !showInfoTooltip"
            type="button"
            class="relative shrink-0 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-blue-600 text-[7px] font-bold leading-none text-white shadow ring-1 ring-blue-400/60 active:scale-95 xl:h-5 xl:w-5 xl:text-xs xl:shadow-md xl:ring-2 xl:ring-blue-400/50 xl:hover:scale-110"
            title="Informacion për çmimin"
            aria-label="Shfaq llogaritjen e kompletit"
            :aria-expanded="showInfoTooltip"
            :aria-controls="'package-info-panel-' + product.id"
          >
            i
          </button>
        </div>
      </div>
      <div class="absolute bottom-1.5 right-1.5 z-10 xl:bottom-3 xl:right-3">
        <span class="inline-flex max-w-[calc(100%-0.5rem)] items-center truncate rounded-full bg-black/60 px-1.5 py-0.5 text-[9px] font-medium text-slate-100 backdrop-blur-sm xl:px-2.5 xl:py-1 xl:text-[11px]">
          {{ product.category?.name || 'Produkt' }}
        </span>
      </div>
    </div>
    <div class="flex flex-1 flex-col p-2.5 xl:p-4 2xl:p-5 relative">
      <div class="mb-1.5 xl:mb-2">
        <h3 class="min-w-0 text-[11px] font-bold leading-snug tracking-tight text-slate-900 antialiased line-clamp-2 xl:text-sm xl:leading-snug 2xl:text-lg 2xl:font-extrabold">
          {{ product.name }}
        </h3>
      </div>
      <div class="mb-2 min-h-0 flex-1 xl:mb-4 hidden xl:block">
        <p v-if="productSpecsText" class="line-clamp-2 text-xs leading-relaxed text-slate-600 antialiased xl:text-sm">
          {{ productSpecsText }}
        </p>
        <p v-else-if="product.description" class="line-clamp-2 text-xs leading-relaxed text-slate-600 antialiased xl:text-sm">
          {{ product.description }}
        </p>
        <p v-else class="line-clamp-2 text-xs leading-relaxed text-slate-400 antialiased xl:text-sm">Produkti i gatshëm për porosi profesionale.</p>
      </div>
      <!-- Barcode dhe butoni -->
      <div class="mt-auto flex flex-col gap-2 xl:gap-3">
        <div class="flex w-full flex-col items-center gap-0.5 rounded-lg border border-slate-100 bg-slate-50 px-1.5 py-1.5 xl:gap-1 xl:rounded-xl xl:px-2 xl:py-2">
          <span class="hidden text-[9px] font-semibold uppercase tracking-wider text-slate-500 xl:inline xl:text-[10px]">Barcode</span>
          <!-- Telefon & tablet & iPad (deri xl): vetëm numri; desktop i gjerë: grafiku -->
          <p class="w-full text-center font-mono text-[11px] font-semibold tabular-nums tracking-wide text-slate-800 break-all xl:hidden">
            {{ product.barcode != null && String(product.barcode).trim() !== '' ? product.barcode : '—' }}
          </p>
          <BarcodeDisplay :value="product.barcode" compact class="hidden xl:inline-flex" />
        </div>
        <button 
          @click="onMainButtonClick"
          :class="[
            'flex min-h-[40px] w-full items-center justify-center gap-0 rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 font-semibold text-white shadow-md ring-1 ring-primary-700/40 transition-all duration-200 hover:from-primary-700 hover:to-primary-800 hover:shadow-lg active:scale-[0.98] xl:min-h-[44px] xl:gap-2 xl:rounded-xl',
            compactButton
              ? 'px-2 py-2 text-[10px] leading-tight xl:px-2.5 xl:py-2 xl:text-xs 2xl:text-sm'
              : 'px-2 py-2 text-xs xl:px-4 xl:py-2.5 xl:text-sm 2xl:text-base 2xl:py-3 2xl:px-5'
          ]"
          title="Bëj porosi"
        >
          <svg xmlns="http://www.w3.org/2000/svg" :class="compactButton ? 'hidden h-3.5 w-3.5 shrink-0 text-white xl:block xl:h-4 xl:w-4 2xl:h-5 2xl:w-5' : 'hidden h-5 w-5 shrink-0 text-white xl:block'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
          <span class="max-xl:w-full max-xl:text-center max-xl:text-[11px] max-xl:font-semibold max-xl:leading-snug max-xl:whitespace-normal xl:truncate xl:leading-tight">Porosit Tani</span>
        </button>
      </div>
    </div>

    <!-- Lightbox: imazhi i zmadhuar për analizë -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showImageLightbox"
          class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
          role="dialog"
          aria-modal="true"
          aria-labelledby="lightbox-product-name"
          @click.self="showImageLightbox = false"
        >
          <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center">
            <button
              type="button"
              @click="showImageLightbox = false"
              class="absolute -top-2 -right-2 z-10 w-10 h-10 rounded-full bg-white/90 hover:bg-white text-gray-800 shadow-lg flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
              aria-label="Mbyll"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <img 
              :src="getProductImage()" 
              :alt="product.name"
              class="max-w-full max-h-[80vh] w-auto h-auto object-contain rounded-lg shadow-2xl"
              @error="handleImageError"
            />
            <p id="lightbox-product-name" class="mt-3 text-white text-center font-medium text-lg drop-shadow-lg">
              {{ product.name }}
            </p>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Informacioni i kompletit: jashtë kartës (fixed) — nuk pritet nga overflow-hidden -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showInfoTooltip && hasPackageInfo"
          class="fixed inset-0 z-[105] flex items-end justify-center xl:items-center xl:justify-center xl:p-4"
          role="dialog"
          aria-modal="true"
          :aria-labelledby="'package-info-title-' + product.id"
        >
          <div
            class="absolute inset-0 bg-slate-900/45 backdrop-blur-[2px] xl:bg-slate-900/40"
            aria-hidden="true"
            @click="showInfoTooltip = false"
          />
          <div
            :id="'package-info-panel-' + product.id"
            class="relative z-10 w-full max-w-lg px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-2 xl:max-w-md xl:px-0 xl:pb-0 xl:pt-0"
            @click.stop
          >
            <div
              class="max-h-[min(72vh,24rem)] overflow-y-auto overscroll-contain rounded-2xl border border-blue-200/90 bg-gradient-to-br from-white via-blue-50/50 to-white p-4 text-slate-800 shadow-2xl ring-1 ring-slate-200/70 backdrop-blur-md xl:max-h-[min(85vh,26rem)] xl:p-5"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="flex min-w-0 flex-1 items-start gap-3">
                  <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-sm font-bold text-white shadow-md ring-1 ring-blue-400/40">
                    i
                  </div>
                  <div class="min-w-0 flex-1">
                    <p
                      :id="'package-info-title-' + product.id"
                      class="text-base font-bold leading-snug tracking-tight text-blue-950 xl:text-lg"
                    >
                      Total kompleti
                    </p>
                    <p class="mt-2 text-sm font-semibold tabular-nums text-blue-900 xl:text-base">
                      {{ packageCalculationText }}
                    </p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 xl:text-[0.9375rem]">
                      {{ packageTotalPrice }} është çmimi për një komplet.
                    </p>
                  </div>
                </div>
                <button
                  type="button"
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200/80 bg-white/90 text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  aria-label="Mbyll"
                  @click="showInfoTooltip = false"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Modal: zgjedhja e sasisë për bizneset (komplete ose copa) -->
    <Teleport to="body">
      <div
        v-if="showOrderModal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.self="showOrderModal = false"
      >
        <div
          class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden"
          role="dialog"
          aria-labelledby="order-modal-title"
        >
          <div class="p-5 border-b border-gray-100">
            <h3 id="order-modal-title" class="text-lg font-bold text-gray-900">Bëj porosi</h3>
            <p class="text-sm text-gray-600 mt-0.5">{{ product.name }}</p>
          </div>
          <div class="p-5 space-y-4">
            <template v-if="canBuyByPieces">
              <div class="flex rounded-xl bg-gray-100 p-1">
                <button
                  type="button"
                  :class="orderMode === 'packages' ? 'bg-white shadow text-primary-600' : 'text-gray-600'"
                  class="flex-1 py-2 text-sm font-medium rounded-lg transition-all"
                  @click="orderMode = 'packages'"
                >
                  Komplete
                </button>
                <button
                  type="button"
                  :class="orderMode === 'pieces' ? 'bg-white shadow text-primary-600' : 'text-gray-600'"
                  class="flex-1 py-2 text-sm font-medium rounded-lg transition-all"
                  @click="orderMode = 'pieces'"
                >
                  Copa
                </button>
              </div>
              <div v-if="orderMode === 'packages'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Numri i kompleteve</label>
                <input
                  v-model.number="orderPackages"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                <p class="text-xs text-gray-500 mt-1">1 Komplete = {{ product.pieces_per_package }} copa</p>
              </div>
              <div v-else>
                <label class="block text-sm font-medium text-gray-700 mb-1">Numri i copave</label>
                <input
                  v-model.number="orderPieces"
                  type="number"
                  :min="1"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                <p class="text-xs text-gray-500 mt-1">E lejuar për këtë produkt</p>
              </div>
            </template>
            <template v-else>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  {{ product.sold_by_package && product.pieces_per_package ? 'Numri i kompleteve' : 'Sasia' }}
                </label>
                <input
                  v-model.number="orderPackages"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                <p v-if="product.sold_by_package && product.pieces_per_package" class="text-xs text-gray-500 mt-1">
                  1 Komplete = {{ product.pieces_per_package }} copa
                </p>
              </div>
            </template>
          </div>
          <div class="p-5 flex gap-3 border-t border-gray-100">
            <button
              type="button"
              @click="showOrderModal = false"
              class="flex-1 py-2.5 px-4 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors"
            >
              Anulo
            </button>
            <button
              type="button"
              @click="submitOrderModal"
              class="flex-1 py-2.5 px-4 rounded-xl bg-primary-600 text-white font-medium hover:bg-primary-700 transition-colors"
            >
              Shto në shportë
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import cartStore from '../store/cart'
import BarcodeDisplay from './BarcodeDisplay.vue'

// Placeholder si data URL që të mos bëhen kërkesa HTTP (nuk mbushet terminali i PHP)
const PLACEHOLDER_IMAGE_DATA_URL = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Crect fill='%23e5e7eb' width='200' height='200'/%3E%3C/svg%3E"

export default {
  name: 'ProductCard',
  components: { BarcodeDisplay },
  props: {
    product: {
      type: Object,
      required: true
    },
    compactButton: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      cartStore: cartStore,
      showInfoTooltip: false,
      showOrderModal: false,
      showImageLightbox: false,
      orderMode: 'packages',
      orderPackages: 1,
      orderPieces: 1
    }
  },
  computed: {
    isBusinessClient() {
      return !!this.cartStore.client
    },
    canBuyByPieces() {
      // Mundësia për "Copa" është vetëm për klientët e regjistruar me leje të veçantë nga menaxhmenti
      // Klientët e pa regjistruar bëjnë shitje vetëm në shumicë (komplete)
      if (!this.cartStore.client) {
        return false
      }
      // Për klientët e regjistruar: kontrollo lejen e menaxhmentit
      return !!(
        this.cartStore.clientPieceSales[this.product.id] &&
        this.product.sold_by_package &&
        this.product.pieces_per_package
      )
    },
    productSpecsText() {
      const size = this.product.size
      const liters = this.product.liters
      if (!size && !liters) return ''

      const sizeStr = size ? (String(size).includes('x') ? `Size: ${size} cm` : `Size: ${size}`) : ''
      const litersStr = liters ? `Litra: ${liters}` : ''
      if (sizeStr && litersStr) return `${sizeStr} · ${litersStr}`
      if (sizeStr) return sizeStr
      if (litersStr) return litersStr
      return ''
    },
    // Çmimi për klientin e identifikuar: nga menaxhmenti GT ose nga produkti
    displayPrice() {
      if (!this.cartStore.client) return this.product.price ?? null
      const clientPrice = this.cartStore.clientPrices[this.product.id]
      return clientPrice != null ? clientPrice : (this.product.price ?? null)
    },
    // Kontrollon nëse produkti ka informacion për paketim
    hasPackageInfo() {
      return this.product.sold_by_package && 
             this.product.pieces_per_package && 
             this.displayPrice != null &&
             this.displayPrice > 0
    },
    // Teksti i llogaritjes për produktet me paketim
    packageCalculationText() {
      if (!this.hasPackageInfo) return ''
      const pieces = this.product.pieces_per_package
      const price = this.displayPrice
      const total = pieces * price
      return `${pieces}cp × ${this.formatPrice(price)} = ${this.formatPrice(total)}`
    },
    // Çmimi total për një kompleti
    packageTotalPrice() {
      if (!this.hasPackageInfo) return ''
      const pieces = this.product.pieces_per_package
      const price = this.displayPrice
      return this.formatPrice(pieces * price)
    },
    // Numri total i copave bazuar në numrin e kompleteve
    totalPiecesFromPackages() {
      if (!this.product.sold_by_package || !this.product.pieces_per_package) return null
      const packages = Math.max(1, parseInt(this.orderPackages, 10) || 1)
      return packages * this.product.pieces_per_package
    }
  },
  watch: {
    showImageLightbox(open) {
      if (open) this.showInfoTooltip = false
      this._syncBodyScrollLock()
    },
    showInfoTooltip() {
      this._syncBodyScrollLock()
    }
  },
  mounted() {
    this._onEscapeKey = (e) => {
      if (e.key !== 'Escape') return
      if (this.showImageLightbox) this.showImageLightbox = false
      else if (this.showInfoTooltip) this.showInfoTooltip = false
    }
    document.addEventListener('keydown', this._onEscapeKey)
  },
  beforeUnmount() {
    if (this._onEscapeKey) document.removeEventListener('keydown', this._onEscapeKey)
    document.body.classList.remove('overflow-hidden')
  },
  methods: {
    _syncBodyScrollLock() {
      if (this.showImageLightbox || this.showInfoTooltip) {
        document.body.classList.add('overflow-hidden')
      } else {
        document.body.classList.remove('overflow-hidden')
      }
    },
    formatPrice(price) {
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },
    getProductImage() {
      if (this.product?.image_url && String(this.product.image_url).trim()) {
        return this.product.image_url
      }
      // Foto e regjistruar në admin ka përparësi – e njëjta foto shfaqet në ballinë dhe kudo
      const path = this.product.image_path
      if (path && String(path).trim()) {
        return path
      }
      // Fallback: rrugë të fiksuara për produkte pa image_path në bazë
      const slug = this.product.slug || ''
      const name = (this.product.name || '').toLowerCase()
      
      if (slug === 'kese-mbeturinash-300l' || name.includes('kese mbeturinash 300l')) {
        return '/Images/Kese Mbeturina/300L/foto1.png'
      } else if (slug === 'kese-mbeturinash-270l' || name.includes('kese mbeturinash 270l')) {
        return '/Images/Kese Mbeturina/270L/foto1.png'
      } else if (slug === 'kese-mbeturinash-240l' || name.includes('kese mbeturinash 240l')) {
        return '/Images/Kese Mbeturina/240L/foto1.png'
      } else if (slug === 'kese-mbeturinash-200l' || name.includes('kese mbeturinash 200l')) {
        return '/Images/Kese Mbeturina/200L/foto1.png'
      } else if (slug === 'kese-mbeturinash-170l' || name.includes('kese mbeturinash 170l')) {
        return '/Images/Kese Mbeturina/170L/foto1.png'
      } else if (slug === 'kese-mbeturinash-150l' || name.includes('kese mbeturinash 150l')) {
        return '/Images/Kese Mbeturina/150L/foto1.png'
      } else if (slug === 'kese-mbeturinash-120l' || name.includes('kese mbeturinash 120l')) {
        return '/Images/Kese Mbeturina/120L/foto1.png'
      } else if (slug === 'kese-mbeturinash-70l' || name.includes('kese mbeturinash 70l')) {
        return '/Images/Kese Mbeturina/70L/foto1.png'
      } else if (slug === 'kese-mbeturinash-40l' || name.includes('kese mbeturinash 40l')) {
        return '/Images/Kese Mbeturina/40L/foto1.png'
      } else if (slug === 'leter-kuzhine-nush-2-shtresa' || name.includes('leter kuzhine nush 2 shtresa')) {
        return '/images/Pallomat/XL/foto1.png'
      } else if (slug === 'leter-salvete' || name.includes('leter salvete')) {
        return '/images/Pallomat/Salvete/foto1.jpg'
      } else if (slug === 'gota-plastike' || name.includes('gota plastike')) {
        return '/images/Gota Plastike/foto1.jpg'
      } else if (slug === 'pipeza-te-zi-per-pije-100cp-kompleti-20cp' || (name.includes('pipëza') && name.includes('zi') && name.includes('100cp'))) {
        return '/images/Pipat/Pipa-100cp/Black/foto1.jpg'
      } else if (slug === 'pipeza-color-per-pije-100cp-kompleti-20cp' || slug === 'pipeza-transparente-per-pije-100cp-kompleti-20cp' || (name.includes('pipëza') && (name.includes('color') || name.includes('transparente')) && name.includes('100cp'))) {
        return '/images/Pipat/Pipa-100cp/Color/foto1.jpg'
      }
      
      return this.product.image_url || this.product.image_path || PLACEHOLDER_IMAGE_DATA_URL
    },
    handleImageError(event) {
      // Nëse është një nga këto tre produkte, provoni rrugët alternative
      const slug = this.product.slug || ''
      const name = (this.product.name || '').toLowerCase()
      
      if (slug === 'kese-mbeturinash-300l' || name.includes('kese mbeturinash 300l')) {
        const currentSrc = event.target.src
        if (currentSrc.includes('.png')) {
          event.target.src = '/Images/Kese Mbeturina/300L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/300L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-270l' || name.includes('kese mbeturinash 270l')) {
        const currentSrc = event.target.src
        // Provoni të gjitha variantet për 270L
        if (currentSrc.includes('foto1.png.png')) {
          event.target.src = '/Images/Kese Mbeturina/270L/foto1.png'
        } else if (currentSrc.includes('.png')) {
          event.target.src = '/Images/Kese Mbeturina/270L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/270L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-240l' || name.includes('kese mbeturinash 240l')) {
        const currentSrc = event.target.src
        if (currentSrc.includes('.png')) {
          event.target.src = '/Images/Kese Mbeturina/240L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/240L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-200l' || name.includes('kese mbeturinash 200l')) {
        const currentSrc = event.target.src
        if (currentSrc.includes('.png')) {
          event.target.src = '/Images/Kese Mbeturina/200L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/200L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-170l' || name.includes('kese mbeturinash 170l')) {
        const currentSrc = event.target.src
        if (currentSrc.includes('.png')) {
          event.target.src = '/Images/Kese Mbeturina/170L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/170L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-150l' || name.includes('kese mbeturinash 150l')) {
        const currentSrc = event.target.src
        if (currentSrc.includes('.png')) {
          event.target.src = '/Images/Kese Mbeturina/150L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/150L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-120l' || name.includes('kese mbeturinash 120l')) {
        const currentSrc = event.target.src
        if (currentSrc.includes('.png')) {
          event.target.src = '/Images/Kese Mbeturina/120L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/120L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-70l' || name.includes('kese mbeturinash 70l')) {
        const currentSrc = event.target.src
        if (currentSrc.includes('.png')) {
          event.target.src = '/Images/Kese Mbeturina/70L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/70L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-40l' || name.includes('kese mbeturinash 40l')) {
        const currentSrc = event.target.src
        if (currentSrc.includes('.png')) {
          event.target.src = '/Images/Kese Mbeturina/40L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/40L/foto1.png'
        }
      } else if (slug === 'leter-kuzhine-nush-2-shtresa' || name.includes('leter kuzhine nush 2 shtresa')) {
        const currentSrc = event.target.src
        if (currentSrc.includes('.png')) {
          event.target.src = '/images/Pallomat/XL/foto1.jpg'
        } else {
          event.target.src = '/images/Pallomat/XL/foto1.png'
        }
      } else if (slug === 'leter-salvete' || name.includes('leter salvete')) {
        event.target.src = '/images/Pallomat/Salvete/foto1.jpg'
      } else if (slug === 'gota-plastike' || name.includes('gota plastike')) {
        event.target.src = '/images/Gota Plastike/foto1.jpg'
      } else if (slug === 'pipeza-te-zi-per-pije-100cp-kompleti-20cp' || (name.includes('zi') && name.includes('100cp'))) {
        event.target.src = '/images/Pipat/Pipa-100cp/Black/foto1.jpg'
      } else if (slug === 'pipeza-color-per-pije-100cp-kompleti-20cp' || slug === 'pipeza-transparente-per-pije-100cp-kompleti-20cp') {
        event.target.src = '/images/Pipat/Pipa-100cp/Color/foto1.jpg'
      } else {
        event.target.src = PLACEHOLDER_IMAGE_DATA_URL
      }
    },
    onMainButtonClick() {
      // Hap modal-in për të gjithë klientët
      this.orderPackages = 1
      this.orderPieces = this.product.pieces_per_package || 1
      this.orderMode = 'packages'
      this.showOrderModal = true
    },
    submitOrderModal() {
      if (this.canBuyByPieces && this.orderMode === 'pieces') {
        const pieces = Math.max(1, parseInt(this.orderPieces, 10) || 1)
        cartStore.addItem(this.product, 1, pieces)
      } else {
        const qty = Math.max(1, parseInt(this.orderPackages, 10) || 1)
        cartStore.addItem(this.product, qty)
      }
      this.showOrderModal = false
      this.$emit('added-to-cart', this.product.name)
    },
    addToCart() {
      cartStore.addItem(this.product, 1)
      this.$emit('added-to-cart', this.product.name)
    }
  }
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
