<template>
  <AdminLayout>
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-2">Menaxhimi i Produkteve</h1>
          <p class="text-gray-600 text-sm">Shto, përditëso ose fshi produktet tuaja.</p>
        </div>
        <button
          @click="openCreateModal"
          class="btn-primary w-full sm:w-auto"
        >
          + Shto Produkt të Ri
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Kërko sipas emrit</label>
          <input
            v-model.trim="filters.search"
            type="text"
            placeholder="p.sh. Pipëza"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Kategoria</label>
          <select
            v-model="filters.category"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">Të gjitha</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Lloj shitjeje</label>
          <select
            v-model="filters.packageType"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">Të gjitha</option>
            <option value="package">Shitet me komplete</option>
            <option value="piece">Shitet me copë</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Featured</label>
          <select
            v-model="filters.featured"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">Të gjitha</option>
            <option value="1">Vetëm featured</option>
            <option value="0">Jo featured</option>
          </select>
        </div>
      </div>

      <!-- Products table -->
      <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produkt</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategoria</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Çmimi</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Komplete</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Featured</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Veprime</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="product in filteredProducts"
              :key="product.id"
            >
              <td class="px-4 py-4">
                <div class="flex items-center gap-4">
                  <img
                    v-if="product.image_path"
                    :src="product.image_path"
                    :alt="product.name"
                    class="w-16 h-16 object-cover rounded-md border"
                  >
                  <div>
                    <p class="font-semibold text-gray-900">{{ product.name }}</p>
                    <p class="text-sm text-gray-500 line-clamp-2">{{ product.description || 'Pa përshkrim' }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 text-sm text-gray-600">
                {{ product.category?.name || '—' }}
              </td>
              <td class="px-4 py-4 text-sm text-gray-900">
                {{ formatPrice(product.price) }}
              </td>
              <td class="px-4 py-4 text-sm text-gray-900">
                <span
                  :class="product.sold_by_package ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800'"
                  class="px-2 py-1 rounded-full text-xs font-semibold"
                >
                  {{ product.sold_by_package ? `Komplete (${product.pieces_per_package || 0} cp)` : 'Copë' }}
                </span>
              </td>
              <td class="px-4 py-4 text-sm text-gray-900">
                <span
                  :class="product.is_featured ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                  class="px-2 py-1 rounded-full text-xs font-semibold"
                >
                  {{ product.is_featured ? 'Po' : 'Jo' }}
                </span>
              </td>
              <td class="px-4 py-4 text-sm font-medium space-x-2">
                <button
                  @click="openEditModal(product)"
                  class="text-primary-600 hover:text-primary-900"
                >
                  Edit
                </button>
                <button
                  @click="deleteProduct(product)"
                  class="text-red-600 hover:text-red-900"
                >
                  Fshi
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile cards -->
      <div class="lg:hidden space-y-4">
        <div
          v-for="product in filteredProducts"
          :key="product.id"
          class="bg-white rounded-lg shadow p-4"
        >
          <div class="flex gap-3 mb-3">
            <img
              v-if="product.image_path"
              :src="product.image_path"
              :alt="product.name"
              class="w-20 h-20 object-cover rounded-md border"
            >
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ product.name }}</h3>
              <p class="text-sm text-gray-600">{{ product.category?.name || '—' }}</p>
            </div>
          </div>
          <p class="text-sm text-gray-600 mb-2">{{ product.description || 'Pa përshkrim.' }}</p>
          <div class="flex flex-wrap gap-2 text-xs mb-3">
            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full">Çmimi: {{ formatPrice(product.price) }}</span>
            <span
              class="px-2 py-1 rounded-full"
              :class="product.sold_by_package ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700'"
            >
              {{ product.sold_by_package ? `Komplete (${product.pieces_per_package || 0} cp)` : 'Copë' }}
            </span>
            <span
              class="px-2 py-1 rounded-full"
              :class="product.is_featured ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
            >
              {{ product.is_featured ? 'Featured' : 'Standard' }}
            </span>
          </div>
          <div class="flex gap-3">
            <button
              @click="openEditModal(product)"
              class="flex-1 px-3 py-2 text-sm font-medium text-primary-600 border border-primary-300 rounded-md hover:bg-primary-50"
            >
              Edit
            </button>
            <button
              @click="deleteProduct(product)"
              class="flex-1 px-3 py-2 text-sm font-medium text-red-600 border border-red-300 rounded-md hover:bg-red-50"
            >
              Fshi
            </button>
          </div>
        </div>
      </div>

      <!-- Product modal -->
      <div
        v-if="showModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      >
        <div class="relative top-4 sm:top-12 mx-auto max-w-3xl w-full p-3 sm:p-6">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="flex justify-between items-center px-4 sm:px-6 py-4 border-b border-gray-200">
              <div>
                <h3 class="text-xl font-bold text-gray-900">
                  {{ editingProduct ? 'Edito Produktin' : 'Shto Produkt të Ri' }}
                </h3>
                <p class="text-sm text-gray-500">
                  Plotësoni të dhënat e produktit dhe ruajeni menjëherë në platformë.
                </p>
              </div>
              <button @click="closeModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>

            <div class="px-4 sm:px-6 py-5 space-y-6 max-h-[70vh] overflow-y-auto">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Emri i Produktit *</label>
                  <input
                    v-model="productForm.name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Kategoria *</label>
                  <select
                    v-model="productForm.category_id"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  >
                    <option value="">Zgjidh kategorinë</option>
                    <option
                      v-for="category in categories"
                      :key="category.id"
                      :value="category.id"
                    >
                      {{ category.name }}
                    </option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Size (për specifikat te produkti)</label>
                  <input
                    v-model="productForm.size"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="p.sh. 70x115 ose 4oz"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Litra (për specifikat te produkti)</label>
                  <input
                    v-model="productForm.liters"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="p.sh. 150L ose 170L"
                  >
                </div>
              </div>
              <p class="text-xs text-gray-500 mb-3">Nëse plotësoni Size dhe/ose Litra, te produkti do të shfaqen këto specifikat në vend të përshkrimit të gjatë.</p>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Përshkrimi (opsional)</label>
                <textarea
                  v-model="productForm.description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="Përshkruani produktin..."
                ></textarea>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Çmimi (opsional)</label>
                  <input
                    v-model.number="productForm.price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="p.sh. 1.50"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                  <input
                    v-model="productForm.barcode"
                    type="text"
                    inputmode="numeric"
                    maxlength="20"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="p.sh. 3904481760132"
                  >
                </div>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Pozicioni në listë</label>
                  <input
                    v-model.number="productForm.sort_order"
                    type="number"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="p.sh. 10"
                  >
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="flex items-center gap-2">
                  <input
                    v-model="productForm.is_featured"
                    type="checkbox"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                  >
                  <span class="text-sm text-gray-700">Shfaq në produktet kryesore</span>
                </label>
                <label class="flex items-center gap-2">
                  <input
                    v-model="productForm.sold_by_package"
                    type="checkbox"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                  >
                  <span class="text-sm text-gray-700">Shitet me komplete/pako</span>
                </label>
              </div>

              <div v-if="productForm.sold_by_package">
                <label class="block text-sm font-medium text-gray-700 mb-1">Copa për komplet *</label>
                <input
                  v-model.number="productForm.pieces_per_package"
                  type="number"
                  min="1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="p.sh. 20"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto e produktit</label>
                <div class="flex items-center gap-4">
                  <img
                    v-if="productForm.preview"
                    :src="productForm.preview"
                    alt="Foto aktuale"
                    class="w-24 h-24 object-cover rounded-lg border"
                  >
                  <label class="flex-1">
                    <span class="sr-only">Ngarko foto</span>
                    <input
                      type="file"
                      accept="image/*"
                      @change="handleImageUpload"
                      class="block w-full text-sm text-gray-500
                             file:me-4 file:py-2 file:px-4
                             file:rounded-full file:border-0
                             file:text-sm file:font-semibold
                             file:bg-primary-50 file:text-primary-700
                             hover:file:bg-primary-100"
                    >
                  </label>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                  Formate të lejuara: JPG, PNG, WEBP. Maksimumi 4MB.
                </p>
              </div>
            </div>

            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-end gap-3">
              <button
                @click="closeModal"
                class="btn-secondary"
                type="button"
              >
                Anulo
              </button>
              <button
                @click="saveProduct"
                :disabled="savingProduct"
                class="btn-primary disabled:opacity-50"
                type="button"
              >
                {{ savingProduct ? 'Duke ruajtur...' : (editingProduct ? 'Ruaj ndryshimet' : 'Shto produktin') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import axios from 'axios'
import { adminStore } from '../../stores/adminStore'
import AdminLayout from '../../components/admin/AdminLayout.vue'

export default {
  name: 'AdminProducts',
  components: {
    AdminLayout
  },
  data() {
    return {
      products: [],
      categories: [],
      filters: {
        search: '',
        category: '',
        packageType: '',
        featured: ''
      },
      showModal: false,
      editingProduct: null,
      productForm: this.defaultForm(),
      savingProduct: false
    }
  },
  computed: {
    canManage() {
      return adminStore.canManage()
    },
    filteredProducts() {
      return this.products.filter(product => {
        const matchesSearch = !this.filters.search ||
          product.name.toLowerCase().includes(this.filters.search.toLowerCase()) ||
          (product.description || '').toLowerCase().includes(this.filters.search.toLowerCase())

        const matchesCategory = !this.filters.category ||
          Number(product.category_id) === Number(this.filters.category)

        const matchesPackage = !this.filters.packageType ||
          (this.filters.packageType === 'package' ? product.sold_by_package : !product.sold_by_package)

        const matchesFeatured = this.filters.featured === ''
          ? true
          : Boolean(product.is_featured) === (this.filters.featured === '1')

        return matchesSearch && matchesCategory && matchesPackage && matchesFeatured
      })
    }
  },
  async mounted() {
    // Scroll to top when component mounts
    window.scrollTo({ top: 0, behavior: 'smooth' })
    await this.checkAuth()
    await Promise.all([this.loadCategories(), this.loadProducts()])
  },
  methods: {
    defaultForm() {
      return {
        name: '',
        category_id: '',
        description: '',
        size: '',
        liters: '',
        barcode: '',
        price: '',
        sort_order: '',
        is_featured: false,
        sold_by_package: false,
        pieces_per_package: '',
        image: null,
        preview: null
      }
    },
    async checkAuth() {
      try {
        const token = localStorage.getItem('admin_token')
        if (!token) {
          this.$router.push('/admin/login')
          return
        }
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
        await axios.get('/api/admin/check')
      } catch (error) {
        localStorage.removeItem('admin_token')
        delete axios.defaults.headers.common['Authorization']
        this.$router.push('/admin/login')
      }
    },
    async loadProducts() {
      try {
        const response = await axios.get('/api/admin/products')
        this.products = response.data.data || []
      } catch (error) {
        console.error('Error loading products:', error)
        alert('Gabim në ngarkimin e produkteve')
      }
    },
    async loadCategories() {
      try {
        const response = await axios.get('/api/categories')
        this.categories = response.data.data || []
      } catch (error) {
        console.error('Error loading categories:', error)
        alert('Gabim në ngarkimin e kategorive')
      }
    },
    openCreateModal() {
      this.editingProduct = null
      this.productForm = this.defaultForm()
      this.showModal = true
    },
    openEditModal(product) {
      this.editingProduct = product
      this.productForm = {
        name: product.name,
        category_id: product.category_id,
        description: product.description,
        size: product.size || '',
        liters: product.liters || '',
        barcode: product.barcode || '',
        price: product.price,
        sort_order: product.sort_order,
        is_featured: Boolean(product.is_featured),
        sold_by_package: Boolean(product.sold_by_package),
        pieces_per_package: product.pieces_per_package,
        image: null,
        preview: product.image_path
      }
      this.showModal = true
    },
    generateBarcode() {
      // EAN-13: 13 shifra, shifra e 13-të është checksum (format standard p.sh. 3904481760132)
      const ean13Checksum = (digits12) => {
        const str = String(digits12).replace(/\D/g, '').slice(0, 12).padStart(12, '0')
        if (str.length !== 12) return ''
        const s = str.split('').map(Number)
        const sum = s.reduce((acc, d, i) => acc + (i % 2 === 0 ? d : d * 3), 0)
        const check = (10 - (sum % 10)) % 10
        return str + check
      }
      if (this.editingProduct && this.editingProduct.id) {
        const base = ('390' + String(this.editingProduct.id).padStart(9, '0')).slice(0, 12)
        this.productForm.barcode = ean13Checksum(base)
      } else {
        const random9 = String(Math.floor(100000000 + Math.random() * 900000000))
        this.productForm.barcode = ean13Checksum(('390' + random9).slice(0, 12))
      }
    },
    closeModal() {
      this.showModal = false
      this.editingProduct = null
      this.productForm = this.defaultForm()
      this.savingProduct = false
    },
    handleImageUpload(event) {
      const file = event.target.files[0]
      if (!file) return

      this.productForm.image = file
      const reader = new FileReader()
      reader.onload = e => {
        this.productForm.preview = e.target.result
      }
      reader.readAsDataURL(file)
    },
    async saveProduct() {
      if (!this.productForm.name || !this.productForm.category_id) {
        alert('Ju lutem plotësoni emrin dhe kategorinë!')
        return
      }
      if (this.productForm.sold_by_package && !this.productForm.pieces_per_package) {
        alert('Ju lutem vendosni numrin e copave për paketë!')
        return
      }

      this.savingProduct = true
      try {
        const formData = new FormData()
        formData.append('name', this.productForm.name)
        formData.append('category_id', this.productForm.category_id)
        if (this.productForm.description) formData.append('description', this.productForm.description)
        formData.append('size', this.productForm.size || '')
        formData.append('liters', this.productForm.liters || '')
        formData.append('barcode', this.productForm.barcode || '')
        // Gjithmonë dërgo çmimin kur përditësohet produkti, që backend ta ruajë (edhe kur bosh = null)
        const priceVal = this.productForm.price
        const hasPrice = priceVal !== '' && priceVal !== null && priceVal !== undefined && !Number.isNaN(Number(priceVal))
        formData.append('price', hasPrice ? String(priceVal) : '')
        if (this.productForm.sort_order !== '' && this.productForm.sort_order !== null && this.productForm.sort_order !== undefined) {
          formData.append('sort_order', this.productForm.sort_order)
        }
        formData.append('is_featured', this.productForm.is_featured ? '1' : '0')
        formData.append('sold_by_package', this.productForm.sold_by_package ? '1' : '0')
        if (this.productForm.sold_by_package && this.productForm.pieces_per_package) {
          formData.append('pieces_per_package', this.productForm.pieces_per_package)
        }
        if (this.productForm.image) {
          formData.append('image', this.productForm.image)
        }

        if (this.editingProduct) {
          formData.append('_method', 'PUT')
          await axios.post(`/api/admin/products/${this.editingProduct.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
          alert('Produkti u përditësua me sukses!')
        } else {
          await axios.post('/api/admin/products', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
          alert('Produkti u shtua me sukses!')
        }

        await this.loadProducts()
        this.closeModal()
      } catch (error) {
        console.error('Error saving product:', error)
        let message = 'Gabim në ruajtjen e produktit.'
        if (error.response && error.response.data) {
          if (error.response.data.message) {
            message = error.response.data.message
          } else if (error.response.data.errors) {
            const errors = Object.values(error.response.data.errors).flat()
            if (errors.length) {
              message = errors.join('\n')
            }
          }
        }
        alert(message)
      } finally {
        this.savingProduct = false
      }
    },
    async deleteProduct(product) {
      if (!confirm(`A jeni të sigurt që dëshironi të fshini produktin "${product.name}"?`)) {
        return
      }
      try {
        await axios.delete(`/api/admin/products/${product.id}`)
        this.products = this.products.filter(p => p.id !== product.id)
        alert('Produkti u fshi me sukses!')
      } catch (error) {
        console.error('Error deleting product:', error)
        alert('Gabim në fshirjen e produktit')
      }
    },
    formatPrice(price) {
      if (price === null || price === undefined || price === '') {
        return 'Sipas kërkesës'
      }
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    }
  }
}
</script>

<style scoped>
.btn-primary {
  @apply inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500;
}
.btn-secondary {
  @apply inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500;
}
</style>

