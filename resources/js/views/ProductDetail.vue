<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="loading" class="text-center py-20">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      <p class="mt-4 text-gray-600">Duke ngarkuar produktin...</p>
    </div>
    
    <div v-else-if="product" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Breadcrumb -->
      <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
          <li>
            <router-link to="/" class="hover:text-primary-600">Ballina</router-link>
          </li>
          <li>/</li>
          <li>
            <router-link to="/produktet" class="hover:text-primary-600">Produktet</router-link>
          </li>
          <li>/</li>
          <li class="text-gray-900">{{ product.name }}</li>
        </ol>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Product Image -->
        <div class="space-y-4">
          <div class="aspect-w-16 aspect-h-12 bg-white rounded-lg shadow-lg overflow-hidden">
            <img 
              :src="getProductImage()" 
              :alt="product.name"
              class="w-full h-96 object-cover"
              @error="handleImageError"
            />
          </div>
          
          <!-- Additional Images -->
          <div v-if="product.images && product.images.length > 1" class="grid grid-cols-4 gap-2">
            <div 
              v-for="image in product.images.slice(1)" 
              :key="image.id"
              class="aspect-w-1 aspect-h-1 bg-white rounded-lg shadow-sm overflow-hidden cursor-pointer hover:shadow-md transition-shadow duration-200"
              @click="changeMainImage(image.file_path)"
            >
              <img 
                :src="image.file_path" 
                :alt="product.name"
                class="w-full h-20 object-cover"
              />
            </div>
          </div>
        </div>

        <!-- Product Info -->
        <div class="space-y-6">
          <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              {{ product.name }}
            </h1>
            <div class="flex items-center space-x-4 mb-6">
              <span 
                v-if="cartStore.client && product.price" 
                class="text-2xl font-bold text-primary-600"
              >
                {{ formatPrice(product.price) }}
              </span>
              <span v-else-if="product.barcode" class="text-lg text-gray-600 font-medium">
                Barkod: {{ product.barcode }}
              </span>
              <span v-else class="text-lg text-gray-600">
                Çmimi sipas kërkesës
              </span>
            </div>
          </div>

          <div class="prose max-w-none" v-if="productSpecsText || product.description">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ productSpecsText ? 'Specifikat' : 'Përshkrimi' }}</h3>
            <p v-if="productSpecsText" class="text-gray-700 leading-relaxed font-medium">
              {{ productSpecsText }}
            </p>
            <p v-if="product.description" class="text-gray-700 leading-relaxed" :class="{ 'mt-3': productSpecsText }">
              {{ product.description }}
            </p>
          </div>

          <div class="bg-gray-100 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informacion shtesë</h3>
            <div class="space-y-2 text-sm">
              <div v-if="product.size" class="flex justify-between">
                <span class="text-gray-600">Size:</span>
                <span class="font-medium">{{ product.size }}{{ String(product.size).includes('x') ? ' cm' : '' }}</span>
              </div>
              <div v-if="product.liters" class="flex justify-between">
                <span class="text-gray-600">Litra:</span>
                <span class="font-medium">{{ product.liters }}</span>
              </div>
              <div v-if="product.barcode" class="flex justify-between">
                <span class="text-gray-600">Barkod:</span>
                <span class="font-medium">{{ product.barcode }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Kategoria:</span>
                <span class="font-medium">{{ product.category.name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Kodi i produktit:</span>
                <span class="font-medium">{{ product.slug }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Statusi:</span>
                <span class="font-medium text-green-600">Në dispozicion</span>
              </div>
              <div v-if="product.sold_by_package && product.pieces_per_package" class="flex justify-between">
                <span class="text-gray-600">Paketimi:</span>
                <span class="font-medium">Shitet në komplete (1 kompleti = {{ product.pieces_per_package }} copa)</span>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <button 
              @click="addToCart"
              class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 text-lg flex items-center justify-center gap-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Shto në Shportë
            </button>
            <button class="w-full btn-primary text-lg py-3">
              📞 048 75 66 46
            </button>
            <button class="w-full btn-secondary text-lg py-3">
              📞 044 82 43 14
            </button>
                <button class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 text-base md:text-xs lg:text-[10px]">
                  💬 Viber: +383 48 75 66 46
                </button>
                <button class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 text-base md:text-xs lg:text-[10px]">
                  💬 Viber: +383 44 82 43 14
                </button>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <div v-if="relatedProducts.length > 0" class="mt-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">Produkte të Ngjashme</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <ProductCard 
            v-for="relatedProduct in relatedProducts" 
            :key="relatedProduct.id" 
            :product="relatedProduct"
          />
        </div>
      </div>
    </div>
    
    <div v-else class="text-center py-20">
      <div class="text-gray-400 text-6xl mb-4">❌</div>
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Produkti nuk u gjet</h2>
      <p class="text-gray-600 mb-8">Produkti që po kërkoni nuk ekziston ose është hequr.</p>
      <router-link to="/produktet" class="btn-primary">
        Kthehu te Produktet
      </router-link>
    </div>
  </div>
</template>

<script>
import ProductCard from '../components/ProductCard.vue'
import axios from 'axios'
import cartStore from '../store/cart'

export default {
  name: 'ProductDetail',
  components: {
    ProductCard
  },
  props: {
    slug: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      product: null,
      relatedProducts: [],
      loading: true,
      mainImage: null,
      cartStore: cartStore
    }
  },
  async   mounted() {
    await this.loadProduct()
  },
  computed: {
    productSpecsText() {
      if (!this.product) return ''
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
    async loadProduct() {
      this.loading = true
      try {
        const response = await axios.get(`/api/products/${this.slug}`)
        this.product = response.data.data
        this.mainImage = this.getProductImage()
        
        // Load related products from the same category
        await this.loadRelatedProducts()
      } catch (error) {
        console.error('Error loading product:', error)
        this.product = null
      } finally {
        this.loading = false
      }
    },
    async loadRelatedProducts() {
      if (!this.product) return
      
      try {
        const response = await axios.get('/api/products', {
          params: { category: this.product.category.slug }
        })
        // Filter out current product and limit to 4
        this.relatedProducts = response.data.data
          .filter(p => p.id !== this.product.id)
          .slice(0, 4)
      } catch (error) {
        console.error('Error loading related products:', error)
      }
    },
    formatPrice(price) {
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },
    getProductImage() {
      if (!this.product) return '/images/placeholder.jpg'
      
      // Rregullo fotot për kese mbeturinash 300L, 270L, 240L, 200L, 170L, 150L dhe 120L
      const slug = this.product.slug || ''
      
      if (slug === 'kese-mbeturinash-300l') {
        return '/Images/Kese Mbeturina/300L/foto1.png'
      } else if (slug === 'kese-mbeturinash-270l') {
        return '/Images/Kese Mbeturina/270L/foto1.png'
      } else if (slug === 'kese-mbeturinash-240l') {
        return '/Images/Kese Mbeturina/240L/foto1.png'
      } else if (slug === 'kese-mbeturinash-200l') {
        return '/Images/Kese Mbeturina/200L/foto1.png'
      } else if (slug === 'kese-mbeturinash-170l') {
        return '/Images/Kese Mbeturina/170L/foto1.png'
      } else if (slug === 'kese-mbeturinash-150l') {
        return '/Images/Kese Mbeturina/150L/foto1.png'
      } else if (slug === 'kese-mbeturinash-120l') {
        return '/Images/Kese Mbeturina/120L/foto1.png'
      }
      
      return this.product.image_path || '/images/placeholder.jpg'
    },
    handleImageError(event) {
      // Nëse është një nga këto tre produkte, provoni rrugët alternative
      const slug = this.product?.slug || ''
      if (slug === 'kese-mbeturinash-300l') {
        const currentSrc = event.target.src
        if (currentSrc.includes('foto1')) {
          event.target.src = '/Images/Kese Mbeturina/300L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/300L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-270l') {
        const currentSrc = event.target.src
        // Provoni të gjitha variantet për 270L
        if (currentSrc.includes('foto1.png.png')) {
          event.target.src = '/Images/Kese Mbeturina/270L/foto1.png'
        } else if (currentSrc.includes('foto1')) {
          event.target.src = '/Images/Kese Mbeturina/270L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/270L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-240l') {
        const currentSrc = event.target.src
        if (currentSrc.includes('foto1')) {
          event.target.src = '/Images/Kese Mbeturina/240L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/240L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-200l') {
        const currentSrc = event.target.src
        if (currentSrc.includes('foto1')) {
          event.target.src = '/Images/Kese Mbeturina/200L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/200L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-170l') {
        const currentSrc = event.target.src
        if (currentSrc.includes('foto1')) {
          event.target.src = '/Images/Kese Mbeturina/170L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/170L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-150l') {
        const currentSrc = event.target.src
        if (currentSrc.includes('foto1')) {
          event.target.src = '/Images/Kese Mbeturina/150L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/150L/foto1.png'
        }
      } else if (slug === 'kese-mbeturinash-120l') {
        const currentSrc = event.target.src
        if (currentSrc.includes('foto1')) {
          event.target.src = '/Images/Kese Mbeturina/120L/foto1.jpg'
        } else {
          event.target.src = '/Images/Kese Mbeturina/120L/foto1.png'
        }
      } else {
        event.target.src = '/images/placeholder.jpg'
      }
    },
    changeMainImage(imagePath) {
      this.mainImage = imagePath
    },
    addToCart() {
      if (this.product) {
        this.cartStore.addItem(this.product, 1)
        alert(`${this.product.name} u shtua në shportë!`)
      }
    }
  }
}
</script>
