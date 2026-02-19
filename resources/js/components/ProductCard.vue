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
        v-if="cartStore.client && displayPrice != null"
        class="absolute top-3 left-3 z-10 inline-flex items-center gap-1.5 font-bold text-white bg-red-600 rounded-lg px-3 py-1.5 text-sm shadow-lg ring-1 ring-red-700/40 backdrop-blur-sm"
      >
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
          @click="addToCart"
          class="shrink-0 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 active:scale-[0.98] text-white font-semibold py-2.5 px-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 text-sm md:py-2.5 md:px-4 lg:py-3 lg:px-5 lg:text-base flex items-center justify-center gap-2 min-h-[44px] min-w-0 max-w-full"
          title="Shto në shportë"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span class="truncate">Shto në Shportë</span>
        </button>
      </div>
    </div>
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
      showInfoTooltip: false
    }
  },
  computed: {
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
      // Rregullo fotot për kese mbeturinash 300L, 270L, 240L, 200L, 170L, 150L dhe 120L
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
      } else {
        event.target.src = '/images/placeholder.jpg'
      }
    },
    addToCart() {
      cartStore.addItem(this.product, 1)
      // Show notification
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
