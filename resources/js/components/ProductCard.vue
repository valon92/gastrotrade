<template>
  <div class="card overflow-hidden hover:scale-105 transition-transform duration-300">
    <div class="aspect-w-16 aspect-h-12 bg-gray-200">
      <img 
        :src="getProductImage()" 
        :alt="product.name"
        class="w-full h-48 object-cover"
        @error="handleImageError"
      />
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
      <div class="flex justify-between items-center">
        <span v-if="cartStore.client && product.price" class="text-lg font-bold text-primary-600">
          {{ formatPrice(product.price) }}
        </span>
        <span v-else-if="product.barcode" class="text-sm text-gray-600 font-medium">
          Barkod: {{ product.barcode }}
        </span>
        <span v-else class="text-sm text-gray-500">
          Çmimi sipas kërkesës
        </span>
        <button 
          @click="addToCart"
          class="bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 text-sm flex items-center justify-center gap-2"
          title="Shto në shportë"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          Shto në Shportë
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import cartStore from '../store/cart'

export default {
  name: 'ProductCard',
  props: {
    product: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      cartStore: cartStore
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
