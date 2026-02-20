<template>
  <div class="card overflow-hidden hover:scale-105 transition-transform duration-300">
    <!-- Imazhi me çmim mbi të (overlay) -->
    <div class="relative aspect-w-16 aspect-h-12 bg-gray-200">
      <img 
        :src="getProductImage()" 
        :alt="product.name"
        class="w-full h-48 object-cover"
        @error="handleImageError"
      />
      <div
        v-if="displayPrice != null"
        class="absolute top-3 left-3 z-10 flex flex-wrap items-center gap-2 max-w-[90%]"
      >
        <div class="inline-flex items-center gap-1.5 font-bold text-white bg-red-600 rounded-lg px-3 py-1.5 text-sm shadow-lg ring-1 ring-red-700/40 backdrop-blur-sm shrink-0">
          <span>{{ formatPrice(displayPrice) }}</span>
          <button
            v-if="hasPackageInfo"
            @mouseenter="showInfoTooltip = true"
            @mouseleave="showInfoTooltip = false"
            @click.stop="showInfoTooltip = !showInfoTooltip"
            class="relative shrink-0 w-5 h-5 rounded-full bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white flex items-center justify-center text-xs font-bold shadow-md hover:shadow-lg ring-2 ring-blue-400/50 hover:ring-blue-300 transition-all duration-200 hover:scale-110 active:scale-95"
            title="Informacion për çmimin"
          >
            i
          </button>
          <!-- Tooltip me llogaritjen -->
          <div
            v-if="showInfoTooltip && hasPackageInfo"
            class="absolute top-full left-0 mt-2 w-64 p-3 bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-300 text-blue-900 text-xs rounded-xl shadow-2xl z-20 whitespace-normal backdrop-blur-sm animate-in fade-in slide-in-from-top-2 duration-200"
            @mouseenter="showInfoTooltip = true"
            @mouseleave="showInfoTooltip = false"
          >
            <div class="flex items-start gap-2">
              <div class="shrink-0 w-5 h-5 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-bold mt-0.5">
                i
              </div>
              <div class="flex-1">
                <p class="font-bold mb-1.5 text-blue-900">Total Kompleti:</p>
                <p class="text-blue-800 font-medium mb-2">{{ packageCalculationText }}</p>
                <p class="text-blue-700 text-[10px] leading-relaxed">{{ packageTotalPrice }} është çmimi për një kompleti.</p>
              </div>
            </div>
          </div>
        </div>
        <span 
          v-if="cartStore.client"
          class="text-[10px] sm:text-xs font-medium text-white drop-shadow-md whitespace-nowrap" 
          style="text-shadow: 0 0 2px rgba(0,0,0,0.8), 0 1px 2px rgba(0,0,0,0.9);"
        >
          Çmimet vetëm për {{ clientDisplayName }}
        </span>
      </div>
    </div>
    <div class="p-6 relative">
      <div class="flex justify-between items-start gap-4 mb-2">
        <h3 class="text-xl font-semibold text-gray-900 flex-1">
          {{ product.name }}
        </h3>
        <div class="text-xs md:text-[9px] lg:text-[8px] text-gray-600 text-right whitespace-nowrap flex-shrink-0">
          📞 048 75 66 46<br>
          📞 044 82 43 14<br>
          💬 Viber: +383 48 75 66 46<br>
          💬 Viber: +383 44 82 43 14
        </div>
      </div>
      <p v-if="productSpecsText" class="text-gray-600 mb-4 line-clamp-2 text-sm">
        {{ productSpecsText }}
      </p>
      <p v-else-if="product.description" class="text-gray-600 mb-4 line-clamp-1 text-sm">
        {{ product.description }}
      </p>
      <!-- Barcode gjithmonë aty; butoni djathtas -->
      <div class="flex justify-between items-center">
        <div class="flex flex-col items-center gap-1 min-w-0 max-w-[140px]">
          <span class="text-xs text-gray-500 uppercase tracking-wide">Barcode</span>
          <BarcodeDisplay :value="product.barcode" compact />
        </div>
        <button 
          @click="onMainButtonClick"
          class="shrink-0 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 active:scale-[0.98] text-white font-semibold py-2.5 px-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 text-sm md:py-2.5 md:px-4 lg:py-3 lg:px-5 lg:text-base flex items-center justify-center gap-2 min-h-[44px] min-w-0 max-w-full"
          title="Bëj porosi"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span class="truncate">Bëj Porosi</span>
        </button>
      </div>
    </div>

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
                <p class="text-xs text-gray-500 mt-1">1 kompleti = {{ product.pieces_per_package }} copa</p>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Sasia</label>
                <input
                  v-model.number="orderPackages"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                <p v-if="product.sold_by_package && product.pieces_per_package" class="text-xs text-gray-500 mt-1">
                  1 kompleti = {{ product.pieces_per_package }} copa
                </p>
                <p v-if="totalPiecesFromPackages !== null" class="text-sm font-semibold text-gray-700 mt-2">
                  Gjithsej: {{ totalPiecesFromPackages }} copa
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

export default {
  name: 'ProductCard',
  components: { BarcodeDisplay },
  props: {
    product: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      cartStore: cartStore,
      showInfoTooltip: false,
      showOrderModal: false,
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
    // Emri i klientit për të shfaqur poshtë çmimit
    clientDisplayName() {
      if (!this.cartStore.client) return ''
      return this.cartStore.client.store_name || this.cartStore.client.name || 'Klient'
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
  methods: {
    formatPrice(price) {
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },
    getProductImage() {
      // Rregullo fotot për kese mbeturinash 300L, 270L, 240L, 200L, 170L, 150L, 120L, 70L dhe 40L
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
      }
      
      return this.product.image_path || '/images/placeholder.jpg'
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
      } else {
        event.target.src = '/images/placeholder.jpg'
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
