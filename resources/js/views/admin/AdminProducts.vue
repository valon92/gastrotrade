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
                  <div class="flex flex-col gap-2">
                    <select
                      v-model="productForm.category_id"
                      required
                      :disabled="showAddCategory"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-100"
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
                    <button
                      v-if="!showAddCategory"
                      type="button"
                      class="text-sm text-primary-600 hover:text-primary-800 font-medium flex items-center gap-1"
                      @click="showAddCategory = true; addCategoryError = null; newCategoryName = ''"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                      Shto kategori të re
                    </button>
                    <div v-else class="flex flex-wrap items-center gap-2 p-2 bg-gray-50 rounded-lg border border-gray-200">
                      <input
                        v-model.trim="newCategoryName"
                        type="text"
                        placeholder="Emri i kategorisë së re"
                        class="flex-1 min-w-[140px] px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        @keydown.enter.prevent="addNewCategory"
                      >
                      <button
                        type="button"
                        class="px-3 py-1.5 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 disabled:opacity-50"
                        :disabled="addingCategory || !newCategoryName"
                        @click="addNewCategory"
                      >
                        {{ addingCategory ? 'Duke shtuar...' : 'Shto' }}
                      </button>
                      <button
                        type="button"
                        class="px-2 py-1.5 text-sm text-gray-600 hover:text-gray-800"
                        @click="showAddCategory = false; newCategoryName = ''; addCategoryError = null"
                      >
                        Anulo
                      </button>
                      <p v-if="addCategoryError" class="w-full text-xs text-red-600 mt-1">{{ addCategoryError }}</p>
                    </div>
                  </div>
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
                <p class="text-xs text-gray-500 mb-2">Importoni foto nga telefoni (iPhone/Android) ose kompjuteri — formatet HEIC nga iPhone konvertohen automatikisht në JPEG.</p>
                <div class="flex flex-col sm:flex-row items-start gap-4 flex-wrap">
                  <div
                    v-if="productForm.preview"
                    class="relative flex-shrink-0"
                  >
                    <img
                      :src="productForm.preview"
                      alt="Foto e zgjedhur"
                      class="w-28 h-28 sm:w-32 sm:h-32 object-cover rounded-xl border-2 border-gray-200 shadow-sm"
                    >
                    <button
                      type="button"
                      aria-label="Hiq foton"
                      class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center shadow hover:bg-red-600 transition-colors"
                      @click="clearProductImage"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div
                      class="border-2 border-dashed rounded-xl p-6 text-center transition-colors"
                      :class="dragOver ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-400 hover:bg-gray-50'"
                      @dragover.prevent="dragOver = true"
                      @dragleave.prevent="dragOver = false"
                      @drop.prevent="handleDrop"
                    >
                      <input
                        id="product-image-file"
                        ref="productImageInput"
                        type="file"
                        accept="image/*"
                        class="sr-only"
                        @change="handleImageUpload"
                      >
                      <p class="text-sm font-medium text-gray-700 mb-1">Ngarko skedar të ri</p>
                      <p class="text-xs text-gray-500 mb-3">Hidhni këtu ose zgjidhni nga pajisja (telefon/PC) — foto ruhen automatikisht në sistem</p>
                      <label
                        for="product-image-file"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-primary-50 hover:border-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 cursor-pointer transition-colors"
                      >
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Zgjidhni skedar
                      </label>
                      <p v-if="productForm.image" class="text-xs text-primary-600 mt-2 font-medium">{{ productForm.image.name }}</p>
                      <p v-else class="text-xs text-gray-500 mt-2">JPG, PNG, GIF, WEBP — max 10 MB</p>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">ose zgjidhni nga fotot e projektit më poshtë.</p>
                  </div>
                </div>
                <div v-if="imageConverting" class="mt-3 p-2 rounded-lg bg-primary-50 border border-primary-200">
                  <p class="text-sm text-primary-700">Duke konvertuar foton nga telefoni (HEIC → JPEG)...</p>
                </div>
                <div v-if="uploadProgress >= 0 && uploadProgress < 100" class="mt-3">
                  <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-primary-600 transition-all duration-300" :style="{ width: uploadProgress + '%' }" />
                  </div>
                  <p class="text-xs text-gray-600 mt-1">Duke ruajtur... {{ uploadProgress }}%</p>
                </div>
                <div v-if="saveError" class="mt-3 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                  {{ saveError }}
                </div>
                <div class="mt-4">
                  <p class="text-sm font-medium text-gray-700 mb-2">Foto nga projekti (public/images)</p>
                  <div v-if="projectImagesLoading" class="text-sm text-gray-500">Duke ngarkuar...</div>
                  <div v-else-if="projectImages.length" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2 max-h-48 overflow-y-auto">
                    <button
                      v-for="path in projectImages"
                      :key="path"
                      type="button"
                      :class="['rounded-lg border-2 overflow-hidden flex-shrink-0 focus:outline-none focus:ring-2 focus:ring-primary-500', productForm.existingImagePath === path ? 'border-primary-600 ring-2 ring-primary-400' : 'border-gray-200 hover:border-primary-300']"
                      @click="selectProjectImage(path)"
                    >
                      <img :src="path" :alt="path" class="w-full aspect-square object-cover" @error="$event.target.style.display='none'">
                    </button>
                  </div>
                  <p v-else class="text-xs text-gray-500">Nuk u gjetën foto në public/images.</p>
                </div>
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

/** Returns true if the file is HEIC/HEIF (iPhone default format). */
function isHeic(file) {
  if (!file || !file.type) return false
  const t = file.type.toLowerCase()
  const name = (file.name || '').toLowerCase()
  return t === 'image/heic' || t === 'image/heif' || name.endsWith('.heic') || name.endsWith('.heif')
}

/** Convert HEIC/HEIF file to JPEG (loads heic2any only when needed). */
async function convertHeicToJpeg(file) {
  const heic2any = (await import('heic2any')).default
  const blob = await heic2any({
    blob: file,
    toType: 'image/jpeg',
    quality: 0.92
  })
  const outBlob = Array.isArray(blob) ? blob[0] : blob
  const name = (file.name || 'image').replace(/\.(heic|heif)$/i, '.jpg')
  return new File([outBlob], name, { type: 'image/jpeg', lastModified: Date.now() })
}

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
      savingProduct: false,
      projectImages: [],
      projectImagesLoading: false,
      dragOver: false,
      uploadProgress: -1,
      saveError: null,
      showAddCategory: false,
      newCategoryName: '',
      addingCategory: false,
      addCategoryError: null,
      imageConverting: false
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
        preview: null,
        existingImagePath: null
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
    async addNewCategory() {
      const name = this.newCategoryName.trim()
      if (!name) return
      this.addingCategory = true
      this.addCategoryError = null
      try {
        const res = await axios.post('/api/admin/categories', { name })
        const category = res.data.data
        this.categories.push(category)
        this.productForm.category_id = category.id
        this.showAddCategory = false
        this.newCategoryName = ''
      } catch (err) {
        const msg = err.response?.data?.message || err.response?.data?.errors?.name?.[0] || 'Gabim në shtimin e kategorisë.'
        this.addCategoryError = msg
      } finally {
        this.addingCategory = false
      }
    },
    openCreateModal() {
      this.editingProduct = null
      this.productForm = this.defaultForm()
      this.saveError = null
      this.uploadProgress = -1
      this.showAddCategory = false
      this.newCategoryName = ''
      this.addCategoryError = null
      this.showModal = true
      this.loadProjectImages()
      this.$nextTick(() => {
        if (this.$refs.productImageInput) this.$refs.productImageInput.value = ''
      })
    },
    openEditModal(product) {
      this.editingProduct = product
      const isProjectOrUploadPath = product.image_path && (product.image_path.startsWith('/images/') || product.image_path.startsWith('/uploads/'))
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
        preview: product.image_path,
        existingImagePath: isProjectOrUploadPath ? product.image_path : null
      }
      this.saveError = null
      this.uploadProgress = -1
      this.showModal = true
      this.loadProjectImages()
      this.$nextTick(() => {
        if (this.$refs.productImageInput) this.$refs.productImageInput.value = ''
      })
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
      this.saveError = null
      this.uploadProgress = -1
      this.showAddCategory = false
      this.newCategoryName = ''
      this.addCategoryError = null
    },
    async loadProjectImages() {
      this.projectImagesLoading = true
      try {
        const res = await axios.get('/api/admin/project-images')
        this.projectImages = res.data.data || []
      } catch (e) {
        this.projectImages = []
      } finally {
        this.projectImagesLoading = false
      }
    },
    selectProjectImage(path) {
      this.productForm.existingImagePath = path
      this.productForm.preview = path
      this.productForm.image = null
      if (this.$refs.productImageInput) this.$refs.productImageInput.value = ''
    },
    async handleImageUpload(event) {
      const file = event.target?.files?.[0]
      if (!file) return
      if (file.size > 10 * 1024 * 1024) {
        this.saveError = 'Foto nuk duhet të kalojë 10 MB.'
        return
      }
      this.saveError = null
      this.productForm.existingImagePath = null
      let fileToUse = file
      if (isHeic(file)) {
        this.imageConverting = true
        try {
          fileToUse = await convertHeicToJpeg(file)
        } catch (err) {
          console.error('HEIC conversion failed:', err)
          this.saveError = 'Fota nga telefoni nuk u konvertua. Provoni një foto tjetër ose formatin JPG.'
          this.imageConverting = false
          return
        }
        this.imageConverting = false
      }
      this.productForm.image = fileToUse
      const reader = new FileReader()
      reader.onload = e => {
        this.productForm.preview = e.target.result
      }
      reader.readAsDataURL(fileToUse)
    },
    async handleDrop(event) {
      this.dragOver = false
      const file = event.dataTransfer?.files?.[0]
      if (!file || !file.type.startsWith('image/')) return
      if (file.size > 10 * 1024 * 1024) {
        this.saveError = 'Foto nuk duhet të kalojë 10 MB.'
        return
      }
      this.saveError = null
      this.productForm.existingImagePath = null
      let fileToUse = file
      if (isHeic(file)) {
        this.imageConverting = true
        try {
          fileToUse = await convertHeicToJpeg(file)
        } catch (err) {
          console.error('HEIC conversion failed:', err)
          this.saveError = 'Fota nga telefoni nuk u konvertua. Provoni një foto tjetër ose formatin JPG.'
          this.imageConverting = false
          return
        }
        this.imageConverting = false
      }
      this.productForm.image = fileToUse
      const reader = new FileReader()
      reader.onload = e => {
        this.productForm.preview = e.target.result
      }
      reader.readAsDataURL(fileToUse)
      if (this.$refs.productImageInput) this.$refs.productImageInput.value = ''
    },
    clearProductImage() {
      this.productForm.image = null
      this.productForm.preview = null
      this.productForm.existingImagePath = null
      if (this.$refs.productImageInput) this.$refs.productImageInput.value = ''
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
      this.saveError = null
      this.uploadProgress = 0
      try {
        if (this.productForm.image && isHeic(this.productForm.image)) {
          this.imageConverting = true
          this.productForm.image = await convertHeicToJpeg(this.productForm.image)
          this.imageConverting = false
        }
        const formData = new FormData()
        formData.append('name', this.productForm.name)
        formData.append('category_id', String(this.productForm.category_id))
        formData.append('description', this.productForm.description || '')
        formData.append('size', this.productForm.size || '')
        formData.append('liters', this.productForm.liters || '')
        formData.append('barcode', this.productForm.barcode || '')
        const priceVal = this.productForm.price
        const hasPrice = priceVal !== '' && priceVal !== null && priceVal !== undefined && !Number.isNaN(Number(priceVal))
        formData.append('price', hasPrice ? String(priceVal) : '')
        formData.append('sort_order', this.productForm.sort_order !== '' && this.productForm.sort_order != null ? String(this.productForm.sort_order) : '')
        formData.append('is_featured', this.productForm.is_featured ? '1' : '0')
        formData.append('sold_by_package', this.productForm.sold_by_package ? '1' : '0')
        formData.append('pieces_per_package', this.productForm.sold_by_package && this.productForm.pieces_per_package ? String(this.productForm.pieces_per_package) : '')
        if (this.productForm.image) {
          formData.append('image', this.productForm.image, this.productForm.image.name || 'image.jpg')
        } else if (this.productForm.existingImagePath) {
          formData.append('image_path', this.productForm.existingImagePath)
        }

        const config = {
          headers: { 'Accept': 'application/json' },
          onUploadProgress: (e) => {
            if (e.total) this.uploadProgress = Math.round((e.loaded / e.total) * 100)
            else this.uploadProgress = 50
          }
        }

        if (this.editingProduct) {
          await axios.post(`/api/admin/products/${this.editingProduct.id}`, formData, config)
          alert('Produkti u përditësua me sukses!')
        } else {
          await axios.post('/api/admin/products', formData, config)
          alert('Produkti u shtua me sukses!')
        }

        await this.loadProducts()
        this.closeModal()
      } catch (error) {
        console.error('Error saving product:', error)
        let message = 'Gabim në ruajtjen e produktit.'
        if (error.response?.data) {
          if (error.response.data.message) message = error.response.data.message
          else if (error.response.data.errors) {
            const errors = Object.values(error.response.data.errors).flat()
            if (errors.length) message = errors.join('\n')
          }
        }
        this.saveError = message
      } finally {
        this.savingProduct = false
        this.uploadProgress = -1
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

