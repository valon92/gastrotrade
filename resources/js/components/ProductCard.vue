<template>
  <div class="card overflow-hidden hover:scale-105 transition-transform duration-300">
    <div class="aspect-w-16 aspect-h-12 bg-gray-200">
      <img 
        :src="product.image_path" 
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
      <p v-if="product.description" class="text-gray-600 mb-4 line-clamp-1 text-sm">
        {{ product.description }}
      </p>
      <div class="flex justify-between items-center">
        <span v-if="cartStore.client && product.price" class="text-lg font-bold text-primary-600">
          {{ formatPrice(product.price) }}
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
  methods: {
    formatPrice(price) {
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },
    handleImageError(event) {
      event.target.src = '/images/placeholder.jpg'
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
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
