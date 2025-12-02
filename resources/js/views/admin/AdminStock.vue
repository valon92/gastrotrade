<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="flex-1">
          <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-4">Menaxhimi i Stokut</h1>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
          <button 
            @click="showReceiptModal = true"
            class="btn-primary w-full sm:w-auto"
          >
            + Pranim i Ri i Mallit
          </button>
          <router-link
            to="/admin/clients"
            class="btn-secondary text-center"
          >
            👥 Klientët
          </router-link>
          <button 
            @click="logout"
            class="btn-secondary w-full sm:w-auto"
          >
            🚪 Dil
          </button>
        </div>
      </div>

      <!-- Stock Statistics -->
      <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-3 sm:p-5 border border-blue-200 shadow-sm">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-blue-800">Total Produkte</h3>
            <span class="text-xl sm:text-2xl">📦</span>
          </div>
          <p class="text-xl sm:text-3xl font-bold text-blue-900">{{ stockReport.total_products || 0 }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-3 sm:p-5 border border-green-200 shadow-sm">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-green-800">Vlera e Stokut</h3>
            <span class="text-xl sm:text-2xl">💰</span>
          </div>
          <p class="text-lg sm:text-3xl font-bold text-green-900">{{ formatPrice(stockReport.total_stock_value || 0) }}</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-3 sm:p-5 border border-yellow-200 shadow-sm">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-yellow-800">Stok i Ulët</h3>
            <span class="text-xl sm:text-2xl">⚠️</span>
          </div>
          <p class="text-xl sm:text-3xl font-bold text-yellow-900">{{ stockReport.low_stock_products || 0 }}</p>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-3 sm:p-5 border border-red-200 shadow-sm">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-red-800">Pa Stok</h3>
            <span class="text-xl sm:text-2xl">🔴</span>
          </div>
          <p class="text-xl sm:text-3xl font-bold text-red-900">{{ stockReport.out_of_stock_products || 0 }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Kërko Produkte</label>
          <input 
            v-model.trim="filters.search"
            type="text"
            placeholder="Emri i produktit..."
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Prodhuesi</label>
          <select 
            v-model="filters.supplier_id"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">Të gjithë</option>
            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
              {{ supplier.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Filtro</label>
          <select 
            v-model="filters.stockFilter"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">Të gjitha</option>
            <option value="low_stock">Stok i Ulët</option>
            <option value="out_of_stock">Pa Stok</option>
          </select>
        </div>
        <div class="flex items-end">
          <button 
            @click="loadStock"
            class="w-full btn-primary text-sm py-2"
          >
            🔍 Kërko
          </button>
        </div>
      </div>

      <!-- Tabs for Stock and Invoices -->
      <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'stock'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'stock'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            📦 Stoku
          </button>
          <button
            @click="activeTab = 'invoices'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'invoices'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            📄 Faturat e Prodhuesve
          </button>
          <router-link
            to="/admin/supplier-invoices"
            class="py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
          >
            📋 Menaxho Faturat
          </router-link>
        </nav>
      </div>

      <!-- Stock Section -->
      <div v-if="activeTab === 'stock'">
      <!-- Stock Table - Desktop -->
      <div class="hidden md:block bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkti</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prodhuesi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stoku / Porosit / Mbetja</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Çmimi Blerje</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Çmimi Shitje</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fitimi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vlera Stok</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statusi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(product, index) in filteredProducts" :key="product && product.id ? product.id : `product-${index}`">
                <td class="px-4 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ product && product.name ? product.name : '' }}</div>
                  <div class="text-xs text-gray-500">{{ product && product.category ? product.category.name : '' }}</div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ product && product.supplier ? product.supplier.name : '-' }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-2">
                    <!-- Stoku: Sasia nga pranimi i mallit -->
                    <div class="flex items-center gap-2">
                      <span class="text-xs font-semibold text-gray-700">Stoku:</span>
                      <span 
                        :class="[
                          'px-2 py-1 text-xs font-semibold rounded-full',
                          (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.stock_quantity <= 0) ? 'bg-red-100 text-red-800' :
                          (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.min_stock_level !== undefined && product.min_stock_level !== null && product.stock_quantity <= product.min_stock_level) ? 'bg-yellow-100 text-yellow-800' :
                          'bg-green-100 text-green-800'
                        ]"
                      >
                        <template v-if="product && product.sold_by_package && product.pieces_per_package && product.stock_packages && product.stock_packages > 0">
                          {{ product.stock_packages.toFixed(0) }} komplete = {{ (product.stock_quantity || 0) }}cp
                        </template>
                        <template v-else>
                          {{ (product && product.stock_quantity !== undefined && product.stock_quantity !== null) ? product.stock_quantity : 0 }}cp
                        </template>
                      </span>
                      <button 
                        @click.stop="product && openStockAdjustmentModal(product)"
                        type="button"
                        class="text-primary-600 hover:text-primary-800 hover:bg-primary-50 p-1 rounded transition-colors cursor-pointer"
                        title="Rregullo Stokun"
                      >
                        ✏️
                      </button>
                    </div>
                    
                    <!-- Porosit: Numri i porosive nga klientët -->
                    <div v-if="product && product.ordered_quantity_pieces && product.ordered_quantity_pieces > 0" class="flex items-center gap-2">
                      <span class="text-xs font-semibold text-blue-700">Porosit:</span>
                      <span class="text-xs text-blue-600 font-medium">
                        <template v-if="product.sold_by_package && product.pieces_per_package && product.ordered_quantity_packages">
                          {{ product.ordered_quantity_packages.toFixed(0) }} komplete = {{ product.ordered_quantity_pieces }}cp
                        </template>
                        <template v-else>
                          {{ product.ordered_quantity_pieces }}cp
                        </template>
                      </span>
                    </div>
                    
                    <!-- Mbetja: Llogaritja e mbetjes (mungesë ose tepricë) -->
                    <div v-if="product && product.ordered_quantity_pieces && product.ordered_quantity_pieces > 0 && product.remaining_stock_pieces !== undefined" class="flex items-center gap-2">
                      <span class="text-xs font-semibold" :class="(product.remaining_stock_pieces || 0) < 0 ? 'text-red-700' : 'text-green-700'">
                        Mbetja:
                      </span>
                      <span 
                        class="text-xs font-semibold"
                        :class="(product.remaining_stock_pieces || 0) < 0 ? 'text-red-600' : 'text-green-600'"
                      >
                        <template v-if="(product.remaining_stock_pieces || 0) < 0">
                          <!-- Mungesë -->
                          <template v-if="product.sold_by_package && product.pieces_per_package && product.remaining_stock_packages">
                            Mungesa {{ Math.abs(product.remaining_stock_packages).toFixed(0) }} Komplete = {{ Math.abs(product.remaining_stock_pieces) }}cp
                          </template>
                          <template v-else>
                            Mungesa {{ Math.abs(product.remaining_stock_pieces) }}cp
                          </template>
                        </template>
                        <template v-else>
                          <!-- Tepricë -->
                          <template v-if="product.sold_by_package && product.pieces_per_package && product.remaining_stock_packages">
                            {{ product.remaining_stock_packages.toFixed(0) }} Komplete = {{ product.remaining_stock_pieces }}cp
                          </template>
                          <template v-else>
                            {{ product.remaining_stock_pieces }}cp
                          </template>
                        </template>
                      </span>
                    </div>
                    
                    <!-- Min Stock Level -->
                    <div class="text-xs text-gray-500 mt-1" v-if="product && product.min_stock_level !== undefined && product.min_stock_level !== null">
                      Min: {{ product.min_stock_level }}cp
                      <span v-if="product.sold_by_package && product.pieces_per_package">
                        ({{ Math.floor(product.min_stock_level / product.pieces_per_package) }} komplete)
                      </span>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm">
                  <div v-if="product && product.id && editingProductId === product.id && editingField === 'cost_price'" class="flex items-center gap-2">
                    <input 
                      v-model.number="editingProduct.cost_price"
                      @blur="product && saveProductPrice(product, 'cost_price')"
                      @keyup.enter="product && saveProductPrice(product, 'cost_price')"
                      @keyup.esc="cancelEdit"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-24 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      autofocus
                    >
                    <button 
                      @click="product && saveProductPrice(product, 'cost_price')"
                      class="text-green-600 hover:text-green-800"
                      title="Ruaj"
                    >
                      ✓
                    </button>
                    <button 
                      @click="cancelEdit"
                      class="text-red-600 hover:text-red-800"
                      title="Anulo"
                    >
                      ✕
                    </button>
                  </div>
                  <div v-else @click="product && startEdit(product, 'cost_price')" class="cursor-pointer hover:bg-gray-50 px-2 py-1 rounded">
                    {{ product && product.cost_price ? formatPrice(product.cost_price) : '-' }}
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm">
                  <div v-if="product && product.id && editingProductId === product.id && editingField === 'price'" class="flex items-center gap-2">
                    <input 
                      v-model.number="editingProduct.price"
                      @blur="product && saveProductPrice(product, 'price')"
                      @keyup.enter="product && saveProductPrice(product, 'price')"
                      @keyup.esc="cancelEdit"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-24 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      autofocus
                    >
                    <button 
                      @click="product && saveProductPrice(product, 'price')"
                      class="text-green-600 hover:text-green-800"
                      title="Ruaj"
                    >
                      ✓
                    </button>
                    <button 
                      @click="cancelEdit"
                      class="text-red-600 hover:text-red-800"
                      title="Anulo"
                    >
                      ✕
                    </button>
                  </div>
                  <div v-else @click="product && startEdit(product, 'price')" class="cursor-pointer hover:bg-gray-50 px-2 py-1 rounded">
                    {{ product && product.price ? formatPrice(product.price) : 'Sipas kërkesës' }}
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm">
                  <div v-if="product && product.profit_margin !== null && product.profit_margin !== undefined" class="text-green-600 font-semibold">
                    {{ formatPrice(product.profit_margin) }}
                  </div>
                  <div v-if="product && product.profit_percentage !== null && product.profit_percentage !== undefined" class="text-xs text-gray-500">
                    ({{ product.profit_percentage.toFixed(1) }}%)
                  </div>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatPrice((product && product.total_stock_value) ? product.total_stock_value : 0) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span 
                    :class="[
                      'px-2 py-1 text-xs font-semibold rounded-full',
                      (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.stock_quantity <= 0) ? 'bg-red-100 text-red-800' :
                      (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.min_stock_level !== undefined && product.min_stock_level !== null && product.stock_quantity <= product.min_stock_level) ? 'bg-yellow-100 text-yellow-800' :
                      'bg-green-100 text-green-800'
                    ]"
                  >
                    {{ (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.stock_quantity <= 0) ? 'Pa Stok' :
                       (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.min_stock_level !== undefined && product.min_stock_level !== null && product.stock_quantity <= product.min_stock_level) ? 'Stok i Ulët' :
                       'Në Stok' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Stock Cards - Mobile -->
      <div class="md:hidden space-y-4">
        <div 
          v-for="(product, index) in filteredProducts" 
          :key="product && product.id ? product.id : `product-mobile-${index}`"
          class="bg-white rounded-lg shadow-lg p-4 border border-gray-200"
        >
          <div class="flex justify-between items-start mb-3">
            <div class="flex-1">
              <h3 class="text-base font-semibold text-gray-900">{{ product && product.name ? product.name : '' }}</h3>
              <p class="text-xs text-gray-500 mt-1">{{ product && product.category ? product.category.name : '' }}</p>
            </div>
            <button 
              @click.stop="product && openStockAdjustmentModal(product)"
              type="button"
              class="text-primary-600 hover:text-primary-800 hover:bg-primary-50 p-2 rounded transition-colors cursor-pointer"
              title="Rregullo Stokun"
            >
              ✏️
            </button>
          </div>

          <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
              <p class="text-xs text-gray-500 mb-1">Prodhuesi</p>
              <p class="text-sm font-medium text-gray-900">{{ product && product.supplier ? product.supplier.name : '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Statusi</p>
              <span 
                :class="[
                  'inline-block px-2 py-1 text-xs font-semibold rounded-full',
                  (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.stock_quantity <= 0) ? 'bg-red-100 text-red-800' :
                  (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.min_stock_level !== undefined && product.min_stock_level !== null && product.stock_quantity <= product.min_stock_level) ? 'bg-yellow-100 text-yellow-800' :
                  'bg-green-100 text-green-800'
                ]"
              >
                {{ (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.stock_quantity <= 0) ? 'Pa Stok' :
                   (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.min_stock_level !== undefined && product.min_stock_level !== null && product.stock_quantity <= product.min_stock_level) ? 'Stok i Ulët' :
                   'Në Stok' }}
              </span>
            </div>
          </div>

          <div class="border-t border-gray-200 pt-3 mb-3 space-y-2">
            <!-- Stoku: Sasia nga pranimi i mallit -->
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-700">Stoku:</span>
              <div class="flex items-center gap-2">
                <span 
                  :class="[
                    'px-2 py-1 text-xs font-semibold rounded-full',
                    (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.stock_quantity <= 0) ? 'bg-red-100 text-red-800' :
                    (product && product.stock_quantity !== undefined && product.stock_quantity !== null && product.min_stock_level !== undefined && product.min_stock_level !== null && product.stock_quantity <= product.min_stock_level) ? 'bg-yellow-100 text-yellow-800' :
                    'bg-green-100 text-green-800'
                  ]"
                >
                  <template v-if="product && product.sold_by_package && product.pieces_per_package && product.stock_packages && product.stock_packages > 0">
                    {{ product.stock_packages.toFixed(0) }} komplete = {{ (product.stock_quantity || 0) }}cp
                  </template>
                  <template v-else>
                    {{ (product && product.stock_quantity !== undefined && product.stock_quantity !== null) ? product.stock_quantity : 0 }}cp
                  </template>
                </span>
              </div>
            </div>
            
            <!-- Porosit: Numri i porosive nga klientët -->
            <div v-if="product && product.ordered_quantity_pieces && product.ordered_quantity_pieces > 0" class="flex items-center justify-between">
              <span class="text-xs font-semibold text-blue-700">Porosit:</span>
              <span class="text-xs text-blue-600 font-medium">
                <template v-if="product.sold_by_package && product.pieces_per_package && product.ordered_quantity_packages">
                  {{ product.ordered_quantity_packages.toFixed(0) }} komplete = {{ product.ordered_quantity_pieces }}cp
                </template>
                <template v-else>
                  {{ product.ordered_quantity_pieces }}cp
                </template>
              </span>
            </div>
            
            <!-- Mbetja: Llogaritja e mbetjes (mungesë ose tepricë) -->
            <div v-if="product && product.ordered_quantity_pieces && product.ordered_quantity_pieces > 0 && product.remaining_stock_pieces !== undefined" class="flex items-center justify-between">
              <span class="text-xs font-semibold" :class="(product.remaining_stock_pieces || 0) < 0 ? 'text-red-700' : 'text-green-700'">
                Mbetja:
              </span>
              <span 
                class="text-xs font-semibold"
                :class="(product.remaining_stock_pieces || 0) < 0 ? 'text-red-600' : 'text-green-600'"
              >
                <template v-if="(product.remaining_stock_pieces || 0) < 0">
                  <!-- Mungesë -->
                  <template v-if="product.sold_by_package && product.pieces_per_package && product.remaining_stock_packages">
                    Mungesa {{ Math.abs(product.remaining_stock_packages).toFixed(0) }} Komplete = {{ Math.abs(product.remaining_stock_pieces) }}cp
                  </template>
                  <template v-else>
                    Mungesa {{ Math.abs(product.remaining_stock_pieces) }}cp
                  </template>
                </template>
                <template v-else>
                  <!-- Tepricë -->
                  <template v-if="product.sold_by_package && product.pieces_per_package && product.remaining_stock_packages">
                    {{ product.remaining_stock_packages.toFixed(0) }} Komplete = {{ product.remaining_stock_pieces }}cp
                  </template>
                  <template v-else>
                    {{ product.remaining_stock_pieces }}cp
                  </template>
                </template>
              </span>
            </div>
            
            <!-- Min Stock Level -->
            <div class="flex items-center justify-between" v-if="product && product.min_stock_level !== undefined && product.min_stock_level !== null">
              <span class="text-xs text-gray-500">Min:</span>
              <span class="text-xs text-gray-500">
                {{ product.min_stock_level }}cp
                <span v-if="product.sold_by_package && product.pieces_per_package">
                  ({{ Math.floor(product.min_stock_level / product.pieces_per_package) }} komplete)
                </span>
              </span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-xs text-gray-500 mb-1">Çmimi Blerje</p>
              <div v-if="product && product.id && editingProductId === product.id && editingField === 'cost_price'" class="flex items-center gap-2">
                <input 
                  v-model.number="editingProduct.cost_price"
                  @blur="product && saveProductPrice(product, 'cost_price')"
                  @keyup.enter="product && saveProductPrice(product, 'cost_price')"
                  @keyup.esc="cancelEdit"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  autofocus
                >
                <button 
                  @click="product && saveProductPrice(product, 'cost_price')"
                  class="text-green-600 hover:text-green-800 text-sm"
                  title="Ruaj"
                >
                  ✓
                </button>
                <button 
                  @click="cancelEdit"
                  class="text-red-600 hover:text-red-800 text-sm"
                  title="Anulo"
                >
                  ✕
                </button>
              </div>
              <div v-else @click="product && startEdit(product, 'cost_price')" class="cursor-pointer hover:bg-gray-50 px-2 py-1 rounded">
                <p class="font-medium text-gray-900">{{ product && product.cost_price ? formatPrice(product.cost_price) : '-' }}</p>
              </div>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Çmimi Shitje</p>
              <div v-if="product && product.id && editingProductId === product.id && editingField === 'price'" class="flex items-center gap-2">
                <input 
                  v-model.number="editingProduct.price"
                  @blur="product && saveProductPrice(product, 'price')"
                  @keyup.enter="product && saveProductPrice(product, 'price')"
                  @keyup.esc="cancelEdit"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  autofocus
                >
                <button 
                  @click="product && saveProductPrice(product, 'price')"
                  class="text-green-600 hover:text-green-800 text-sm"
                  title="Ruaj"
                >
                  ✓
                </button>
                <button 
                  @click="cancelEdit"
                  class="text-red-600 hover:text-red-800 text-sm"
                  title="Anulo"
                >
                  ✕
                </button>
              </div>
              <div v-else @click="product && startEdit(product, 'price')" class="cursor-pointer hover:bg-gray-50 px-2 py-1 rounded">
                <p class="font-medium text-gray-900">{{ product && product.price ? formatPrice(product.price) : 'Sipas kërkesës' }}</p>
              </div>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Fitimi</p>
              <div v-if="product && product.profit_margin !== null && product.profit_margin !== undefined">
                <p class="font-semibold text-green-600">{{ formatPrice(product.profit_margin) }}</p>
                <p v-if="product.profit_percentage !== null && product.profit_percentage !== undefined" class="text-xs text-gray-500">
                  ({{ product.profit_percentage.toFixed(1) }}%)
                </p>
              </div>
              <p v-else class="text-gray-400">-</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Vlera Stok</p>
              <p class="font-medium text-gray-900">{{ formatPrice((product && product.total_stock_value) ? product.total_stock_value : 0) }}</p>
            </div>
          </div>
        </div>
      </div>
      </div>

      <!-- Supplier Invoices Section -->
      <div v-if="activeTab === 'invoices'" class="space-y-6">
        <!-- Invoice Filters -->
        <div class="bg-white rounded-lg shadow p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Prodhuesi</label>
            <select 
              v-model="invoiceFilters.supplier_id"
              @change="loadInvoices"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Të gjithë</option>
              <option v-for="supplier in suppliers" :key="supplier && supplier.id ? supplier.id : `supplier-${Math.random()}`" :value="supplier && supplier.id ? supplier.id : ''">
                {{ supplier && supplier.name ? supplier.name : '' }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Statusi</label>
            <select 
              v-model="invoiceFilters.status"
              @change="loadInvoices"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Të gjitha</option>
              <option value="pending">Në Pritje</option>
              <option value="paid">E Paguar</option>
              <option value="overdue">Vonuar</option>
              <option value="cancelled">Anuluar</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Nga Data</label>
            <input 
              v-model="invoiceFilters.date_from"
              type="date"
              @change="loadInvoices"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            >
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Deri në Data</label>
            <input 
              v-model="invoiceFilters.date_to"
              type="date"
              @change="loadInvoices"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            >
          </div>
        </div>

        <!-- Invoices Table - Desktop -->
        <div class="hidden md:block bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nr. Faturës</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prodhuesi</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nëntotali</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">TVSH</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Totali</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statusi</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Veprime</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(invoice, index) in invoices" :key="invoice && invoice.id ? invoice.id : `invoice-${index}`">
                  <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ invoice.invoice_number }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ invoice.supplier?.name }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(invoice.invoice_date) }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatPrice(invoice.subtotal) }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span v-if="invoice.has_vat">
                      {{ formatPrice(invoice.vat_amount) }} ({{ invoice.vat_rate }}%)
                    </span>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                    {{ formatPrice(invoice.total_amount) }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <span 
                      :class="[
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        invoice.status === 'paid' ? 'bg-green-100 text-green-800' :
                        invoice.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                        invoice.status === 'overdue' ? 'bg-red-100 text-red-800' :
                        'bg-gray-100 text-gray-800'
                      ]"
                    >
                      {{ getStatusLabel(invoice.status) }}
                    </span>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm">
                    <button 
                      @click="viewInvoice(invoice)"
                      class="text-primary-600 hover:text-primary-800 mr-3"
                      title="Shiko Detajet"
                    >
                      👁️ Shiko
                    </button>
                    <button 
                      @click="editInvoice(invoice)"
                      class="text-blue-600 hover:text-blue-800 mr-3"
                      title="Edito"
                    >
                      ✏️ Edito
                    </button>
                    <button 
                      @click="printInvoice(invoice)"
                      class="text-green-600 hover:text-green-800 mr-3"
                      title="Printo"
                    >
                      🖨️ Printo
                    </button>
                    <button 
                      @click.stop="deleteInvoice(invoice)"
                      type="button"
                      class="text-red-600 hover:text-red-800"
                      title="Fshi Faturën"
                    >
                      🗑️ Fshi
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Invoices Cards - Mobile -->
        <div class="md:hidden space-y-4">
          <div 
            v-for="(invoice, index) in invoices" 
            :key="invoice && invoice.id ? invoice.id : `invoice-mobile-${index}`"
            class="bg-white rounded-lg shadow-lg p-4 border border-gray-200"
          >
            <div class="flex justify-between items-start mb-3">
              <div>
                <h3 class="text-base font-semibold text-gray-900">{{ invoice.invoice_number }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ invoice.supplier?.name }}</p>
              </div>
              <span 
                :class="[
                  'px-2 py-1 text-xs font-semibold rounded-full',
                  invoice.status === 'paid' ? 'bg-green-100 text-green-800' :
                  invoice.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                  invoice.status === 'overdue' ? 'bg-red-100 text-red-800' :
                  'bg-gray-100 text-gray-800'
                ]"
              >
                {{ getStatusLabel(invoice.status) }}
              </span>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
              <div>
                <p class="text-xs text-gray-500 mb-1">Data</p>
                <p class="font-medium text-gray-900">{{ formatDate(invoice.invoice_date) }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-1">Nëntotali</p>
                <p class="font-medium text-gray-900">{{ formatPrice(invoice.subtotal) }}</p>
              </div>
              <div v-if="invoice.has_vat">
                <p class="text-xs text-gray-500 mb-1">TVSH</p>
                <p class="font-medium text-gray-900">{{ formatPrice(invoice.vat_amount) }} ({{ invoice.vat_rate }}%)</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-1">Totali</p>
                <p class="font-semibold text-gray-900">{{ formatPrice(invoice.total_amount) }}</p>
              </div>
            </div>
            <div class="flex gap-2 pt-3 border-t border-gray-200">
              <button 
                @click="viewInvoice(invoice)"
                class="flex-1 px-3 py-2 text-sm bg-primary-600 text-white rounded-md hover:bg-primary-700"
              >
                👁️ Shiko
              </button>
              <button 
                @click="editInvoice(invoice)"
                class="flex-1 px-3 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700"
              >
                ✏️ Edito
              </button>
              <button 
                @click="printInvoice(invoice)"
                class="flex-1 px-3 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700"
              >
                🖨️ Printo
              </button>
              <button 
                @click.stop="deleteInvoice(invoice)"
                type="button"
                class="px-3 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700"
                title="Fshi Faturën"
              >
                🗑️
              </button>
            </div>
          </div>
        </div>

        <div v-if="invoices.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
          <p class="text-gray-500">Nuk ka faturë për të shfaqur.</p>
        </div>
      </div>

      <!-- Stock Receipt Modal -->
      <div v-if="showReceiptModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-0 sm:top-4 mx-auto max-w-4xl w-full p-3 sm:p-6">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 border-b border-gray-200 gap-3">
              <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Pranim i Ri i Mallit</h3>
              <button @click="closeReceiptModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none self-start sm:self-auto">
                &times;
              </button>
            </div>

            <div class="px-4 sm:px-6 py-4 sm:py-5 max-h-[90vh] sm:max-h-[80vh] overflow-y-auto">
              <form @submit.prevent="saveReceipt">
                <!-- Supplier Selection -->
                <div class="mb-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Prodhuesi *</label>
                  <select 
                    v-model="receiptForm.supplier_id"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  >
                    <option value="">Zgjidhni prodhuesin</option>
                    <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                      {{ supplier.name }}
                    </option>
                  </select>
                  <button 
                    type="button"
                    @click="showSupplierModal = true"
                    class="mt-2 text-sm text-primary-600 hover:text-primary-800"
                  >
                    + Shto Prodhues të Ri
                  </button>
                </div>

                <!-- Receipt Date -->
                <div class="mb-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Data e Pranimit *</label>
                  <input 
                    v-model="receiptForm.receipt_date"
                    type="date"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  >
                </div>

                <!-- Receipt Items -->
                <div class="mb-6">
                  <div class="flex justify-between items-center mb-3">
                    <h4 class="font-semibold text-gray-900">Produktet</h4>
                    <button 
                      type="button"
                      @click="addReceiptItem"
                      class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-md hover:bg-green-700"
                    >
                      + Shto Produkt
                    </button>
                  </div>
                  <div class="space-y-3">
                    <div 
                      v-for="(item, index) in receiptForm.items" 
                      :key="`receipt-item-${index}-${item.product_id || 'empty'}`"
                      class="border border-gray-200 rounded-lg p-4 bg-white"
                    >
                      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                        <!-- Produkti -->
                        <div class="lg:col-span-3">
                          <label class="block text-xs font-medium text-gray-700 mb-2">Produkti *</label>
                          <select 
                            v-model="item.product_id"
                            @change="onProductChange(item, index)"
                            required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          >
                            <option value="">Zgjidhni produktin</option>
                            <option v-for="product in allProducts" :key="product && product.id ? product.id : `product-option-${Math.random()}`" :value="product && product.id ? product.id : ''">
                              {{ product && product.name ? product.name : '' }}
                            </option>
                          </select>
                        </div>

                        <!-- Njësia (vetëm për Kese Mbeturinash) -->
                        <div class="lg:col-span-2" v-if="item.product && isKeseMbeturinash(item.product)">
                          <label class="block text-xs font-medium text-gray-700 mb-2">Njësia *</label>
                            <select 
                              v-model="item.unit_type"
                              @change="onUnitTypeChange($event, item, index)"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            >
                            <option value="package">Paketim (20cp/kompleti)</option>
                            <option value="kg">Kilogram (kg)</option>
                          </select>
                        </div>
                        <div v-else class="lg:col-span-2 hidden lg:block">
                          <!-- Empty space for alignment when unit selector is not shown -->
                        </div>
                        
                        <!-- Sasia -->
                        <div class="lg:col-span-2">
                          <label class="block text-sm font-medium text-gray-700 mb-2">
                            Sasia *
                            <span v-if="item.product && item.product.sold_by_package && item.product.pieces_per_package && (!isKeseMbeturinash(item.product) || (isKeseMbeturinash(item.product) && item.unit_type === 'package'))" class="text-gray-500 font-normal text-xs">
                              (në kompleti)
                            </span>
                            <span v-else-if="isKeseMbeturinash(item.product) && item.unit_type === 'kg'" class="text-gray-500 font-normal text-xs">
                              (në kg)
                            </span>
                          </label>
                          <input 
                            v-model.number="item.quantity"
                            @input="calculateItemTotal(item, index)"
                            type="number"
                            :min="isKeseMbeturinash(item.product) && item.unit_type === 'kg' ? 0.01 : 1"
                            :step="isKeseMbeturinash(item.product) && item.unit_type === 'kg' ? 0.01 : 1"
                            required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          >
                          <p v-if="item.product && item.product.sold_by_package && item.product.pieces_per_package && (!isKeseMbeturinash(item.product) || (isKeseMbeturinash(item.product) && item.unit_type === 'package'))" class="text-xs text-gray-500 mt-1.5">
                            {{ item.quantity || 0 }} kompleti × {{ item.product.pieces_per_package }}cp = <span class="text-primary-600 font-semibold">{{ (item.quantity || 0) * item.product.pieces_per_package }}cp</span>
                          </p>
                          <p v-else-if="isKeseMbeturinash(item.product) && item.unit_type === 'kg'" class="text-xs text-gray-500 mt-1.5">
                            Sasia: <span class="text-primary-600 font-semibold">{{ (item.quantity || 0).toFixed(2) }} kg</span>
                          </p>
                        </div>

                        <!-- Çmimi Blerje -->
                        <div class="lg:col-span-2">
                          <label class="block text-sm font-medium text-gray-700 mb-2">
                            Çmimi Blerje *
                            <span v-if="item.product && item.product.sold_by_package && item.product.pieces_per_package && (!isKeseMbeturinash(item.product) || (isKeseMbeturinash(item.product) && item.unit_type === 'package'))" class="text-gray-500 font-normal text-xs">
                              (për copë)
                            </span>
                            <span v-else-if="isKeseMbeturinash(item.product) && item.unit_type === 'kg'" class="text-gray-500 font-normal text-xs">
                              (për kg)
                            </span>
                          </label>
                          <input 
                            v-model.number="item.unit_cost"
                            @input="calculateItemTotal(item, index)"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          >
                        </div>

                        <!-- Totali -->
                        <div class="lg:col-span-2">
                          <label class="block text-sm font-medium text-gray-700 mb-2">Totali</label>
                          <input 
                            :value="formatPrice(item.total_cost || 0)"
                            type="text"
                            readonly
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm bg-gray-50 font-semibold text-gray-900"
                          >
                        </div>

                        <!-- Butoni i fshirjes -->
                        <div class="lg:col-span-1 flex items-end">
                          <button 
                            type="button"
                            @click="removeReceiptItem(index)"
                            class="w-full px-3 py-2.5 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 flex items-center justify-center"
                            title="Fshi produktin"
                          >
                            🗑️
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Invoice Options -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                  <div class="flex items-center mb-3">
                    <input 
                      type="checkbox"
                      v-model="receiptForm.create_invoice"
                      id="create_invoice"
                      class="mr-2 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                    >
                    <label for="create_invoice" class="text-sm font-medium text-gray-700">
                      Krijo Faturë për Prodhuesin
                    </label>
                  </div>
                  <div v-if="receiptForm.create_invoice" class="space-y-3 mt-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Data e Faturës</label>
                        <input 
                          v-model="receiptForm.invoice_date"
                          type="date"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        >
                      </div>
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Data e Maturimit</label>
                        <input 
                          v-model="receiptForm.due_date"
                          type="date"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        >
                      </div>
                    </div>
                    <div class="flex items-center">
                      <input 
                        type="checkbox"
                        v-model="receiptForm.has_vat"
                        id="has_vat"
                        class="mr-2 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                      >
                      <label for="has_vat" class="text-sm font-medium text-gray-700 mr-3">
                        Me TVSH
                      </label>
                      <div v-if="receiptForm.has_vat" class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Përqindja e TVSH (%)</label>
                        <input 
                          v-model.number="receiptForm.vat_rate"
                          type="number"
                          step="0.01"
                          min="0"
                          max="100"
                          value="18"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        >
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Shënime</label>
                  <textarea 
                    v-model="receiptForm.notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  ></textarea>
                </div>

                <!-- Receipt Summary -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                  <div class="mb-2">
                    <span class="font-semibold text-gray-900 block mb-1">Total Produkte:</span>
                    <div class="space-y-1">
                      <div v-if="calculateTotalItemsByUnit().kg > 0" class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Në kg:</span>
                        <span class="font-bold text-gray-900">
                          {{ calculateTotalItemsByUnit().kg.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} kg
                        </span>
                      </div>
                      <div v-if="calculateTotalItemsByUnit().cp > 0" class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Në copa:</span>
                        <span class="font-bold text-gray-900">
                          {{ calculateTotalItemsByUnit().cp.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) }} cp
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-900">Vlera Totale:</span>
                    <span class="font-bold text-primary-600 text-lg">{{ formatPrice(calculateReceiptTotal()) }}</span>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row justify-end gap-3">
                  <button 
                    type="button"
                    @click="closeReceiptModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                  >
                    Anulo
                  </button>
                  <button 
                    type="submit"
                    :disabled="savingReceipt || receiptForm.items.length === 0"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 disabled:opacity-50"
                  >
                    {{ savingReceipt ? 'Duke ruajtur...' : 'Ruaj Pranimin' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Supplier Modal -->
      <div v-if="showSupplierModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-0 sm:top-4 mx-auto p-4 sm:p-5 border w-full sm:w-96 max-w-md shadow-lg rounded-md bg-white m-2 sm:m-4">
          <h3 class="text-lg font-bold text-gray-900 mb-4">Shto Prodhues të Ri</h3>
          <form @submit.prevent="saveSupplier">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Emri *</label>
                <input v-model="supplierForm.name" type="text" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Kontakti</label>
                <input v-model="supplierForm.contact_person" type="text" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input v-model="supplierForm.email" type="email" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Telefon</label>
                <input v-model="supplierForm.phone" type="text" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Adresa</label>
                <input v-model="supplierForm.address" type="text" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Shënime</label>
                <textarea v-model="supplierForm.notes" rows="3" 
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
              </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
              <button type="button" @click="closeSupplierModal" class="btn-secondary w-full sm:w-auto">Anulo</button>
              <button type="submit" class="btn-primary w-full sm:w-auto">Ruaj</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Stock Adjustment Modal -->
      <div v-if="showStockAdjustmentModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-0 sm:top-4 mx-auto p-4 sm:p-5 border w-full sm:w-[500px] max-w-[95vw] shadow-lg rounded-md bg-white m-2 sm:m-4">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900">Rregullo Stokun</h3>
            <button @click="closeStockAdjustmentModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">
              &times;
            </button>
          </div>
          <div v-if="adjustingProduct" class="mb-4">
            <p class="text-sm text-gray-600 mb-2"><strong>Produkti:</strong> {{ adjustingProduct.name }}</p>
            <p class="text-sm text-gray-600"><strong>Stoku Aktual:</strong> {{ adjustingProduct.stock_quantity }}</p>
          </div>
          <form @submit.prevent="saveStockAdjustment">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lloji i Rregullimit</label>
                <select 
                  v-model="stockAdjustmentForm.adjustment_type"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md"
                >
                  <option value="increase">Rritje</option>
                  <option value="decrease">Ulje</option>
                  <option value="set">Vendos Vlerë</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ stockAdjustmentForm.adjustment_type === 'set' ? 'Vlera e Re' : 'Sasia' }}
                </label>
                <div class="space-y-2">
                  <div class="flex flex-col sm:flex-row gap-2">
                    <input 
                      v-model.number="stockAdjustmentForm.quantity"
                      type="number"
                      :min="getMinValueForUnit(stockAdjustmentForm.unit || 'cp')"
                      :step="getStepForUnit(stockAdjustmentForm.unit || 'cp')"
                      required
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      :placeholder="getPlaceholderForUnit(stockAdjustmentForm.unit || 'cp')"
                    >
                    <select 
                      v-model="stockAdjustmentForm.unit"
                      @change="onUnitChange"
                      class="w-full sm:w-48 px-3 py-2.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white appearance-none cursor-pointer"
                      style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27currentColor%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 1.5em 1.5em; padding-right: 2.5rem;"
                    >
                      <option value="cp">Copa (Cp)</option>
                      <option value="kg">Kilogram (kg)</option>
                      <option value="g">Gram (g)</option>
                      <option value="L">Liter (L)</option>
                      <option value="ml">Mililitër (ml)</option>
                      <option value="m">Metër (m)</option>
                      <option value="cm">Centimetër (cm)</option>
                      <option value="pcs">Pjesë (pcs)</option>
                    </select>
                  </div>
                  <p class="text-xs text-gray-400">
                    Njësia e zgjedhur: <strong>{{ getUnitLabel(stockAdjustmentForm.unit || 'cp') }}</strong>
                  </p>
                </div>
                <p v-if="adjustingProduct" class="text-xs text-gray-500 mt-2">
                  <span v-if="stockAdjustmentForm.adjustment_type === 'set'">
                    Stoku do të bëhet: {{ formatQuantityWithUnit(convertToBaseUnit(stockAdjustmentForm.quantity, stockAdjustmentForm.unit, adjustingProduct), adjustingProduct) }}
                  </span>
                  <span v-else-if="stockAdjustmentForm.adjustment_type === 'increase'">
                    Stoku aktual: {{ formatQuantityWithUnit(adjustingProduct.stock_quantity, adjustingProduct) }} → 
                    Stoku i ri: {{ formatQuantityWithUnit(calculateNewStock('increase'), adjustingProduct) }}
                  </span>
                  <span v-else-if="stockAdjustmentForm.adjustment_type === 'decrease'">
                    Stoku aktual: {{ formatQuantityWithUnit(adjustingProduct.stock_quantity, adjustingProduct) }} → 
                    Stoku i ri: {{ formatQuantityWithUnit(calculateNewStock('decrease'), adjustingProduct) }}
                  </span>
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Shënime</label>
                <textarea 
                  v-model="stockAdjustmentForm.notes"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md"
                ></textarea>
              </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
              <button type="button" @click="closeStockAdjustmentModal" class="btn-secondary w-full sm:w-auto">Anulo</button>
              <button type="submit" :disabled="adjustingStock" class="btn-primary w-full sm:w-auto">
                {{ adjustingStock ? 'Duke rregulluar...' : 'Rregullo' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Invoice View Modal -->
      <div v-if="showInvoiceViewModal && selectedInvoice" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-0 sm:top-4 mx-auto max-w-5xl w-full p-3 sm:p-6">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200 gap-3 bg-gradient-to-r from-primary-50 to-primary-100">
              <div>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Detajet e Faturës</h3>
                <p class="text-sm text-gray-600 mt-1 font-mono">{{ selectedInvoice.invoice_number }}</p>
              </div>
              <button @click="closeInvoiceViewModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none self-start sm:self-auto transition-colors">
                &times;
              </button>
            </div>
            <div class="px-4 sm:px-6 py-4 sm:py-6 max-h-[90vh] sm:max-h-[80vh] overflow-y-auto bg-gray-50">
              <div class="space-y-6">
                <!-- Invoice Info -->
                <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm border border-gray-200">
                  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                      <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Prodhuesi</p>
                      <p class="text-base sm:text-lg font-semibold text-gray-900">{{ selectedInvoice.supplier?.name || '-' }}</p>
                    </div>
                    <div>
                      <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Data e Faturës</p>
                      <p class="text-base sm:text-lg font-semibold text-gray-900">{{ formatDate(selectedInvoice.invoice_date) }}</p>
                    </div>
                    <div v-if="selectedInvoice.due_date">
                      <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Data e Maturimit</p>
                      <p class="text-base sm:text-lg font-semibold text-gray-900">{{ formatDate(selectedInvoice.due_date) }}</p>
                    </div>
                    <div>
                      <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Statusi</p>
                      <span 
                        :class="[
                          'inline-block px-3 py-1.5 text-xs font-semibold rounded-full',
                          selectedInvoice.status === 'paid' ? 'bg-green-100 text-green-800' :
                          selectedInvoice.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                          selectedInvoice.status === 'overdue' ? 'bg-red-100 text-red-800' :
                          'bg-gray-100 text-gray-800'
                        ]"
                      >
                        {{ getStatusLabel(selectedInvoice.status) }}
                      </span>
                    </div>
                    <div v-if="selectedInvoice.status === 'paid' && selectedInvoice.paid_date">
                      <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Data e Pagesës</p>
                      <p class="text-base sm:text-lg font-semibold text-green-600">{{ formatDate(selectedInvoice.paid_date) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Invoice Items -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                  <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
                    <h4 class="text-base sm:text-lg font-semibold text-gray-900">Produktet</h4>
                  </div>
                  <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Produkti</th>
                          <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Sasia</th>
                          <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Çmimi Njësi</th>
                          <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Totali</th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in selectedInvoice.items" :key="item.id" class="hover:bg-gray-50 transition-colors">
                          <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-gray-900">
                            <div class="font-medium">{{ item.product_name }}</div>
                            <!-- For Kese Mbeturinash: check unit_type or infer from quantity -->
                            <template v-if="isKeseMbeturinash({ name: item.product_name })">
                              <!-- If item is in kg (either unit_type='kg' or inferred), show (në kg) -->
                              <span v-if="isItemInKg(item)" class="text-xs text-gray-500 mt-1 block">
                                (në kg)
                              </span>
                              <!-- Otherwise, show packaging info -->
                              <span v-else-if="getItemProductForInvoice(item) && getItemProductForInvoice(item).pieces_per_package" class="text-xs text-gray-500 mt-1 block">
                                ({{ getItemProductForInvoice(item).pieces_per_package }}cp/kompleti)
                              </span>
                            </template>
                            <!-- For other products sold by package - only show if not in cp -->
                            <span v-else-if="!isItemQuantityInPieces(item) && getItemProductForInvoice(item) && getItemProductForInvoice(item).sold_by_package && getItemProductForInvoice(item).pieces_per_package" class="text-xs text-gray-500 mt-1 block">
                              ({{ getItemProductForInvoice(item).pieces_per_package }}cp/kompleti)
                            </span>
                            <!-- If item is in cp, show (në copa) -->
                            <span v-else-if="isItemQuantityInPieces(item)" class="text-xs text-gray-500 mt-1 block">
                              (në copa)
                            </span>
                          </td>
                          <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-gray-700">
                            <!-- For Kese Mbeturinash -->
                            <template v-if="isKeseMbeturinash({ name: item.product_name })">
                              <!-- If item is in kg, show quantity in kg with 2 decimals -->
                              <div class="font-medium" v-if="isItemInKg(item)">
                                {{ parseFloat(item.quantity).toFixed(2) }}
                              </div>
                              <!-- Otherwise, show quantity as is (packages) -->
                              <div class="font-medium" v-else>{{ item.quantity }}</div>
                              <!-- Show kg label if in kg -->
                              <span v-if="isItemInKg(item)" class="text-xs text-gray-500 mt-1 block">
                                kg
                              </span>
                              <!-- Show copa conversion if in packages -->
                              <span v-else-if="getItemProductForInvoice(item) && getItemProductForInvoice(item).pieces_per_package" class="text-xs text-gray-500 mt-1 block">
                                = {{ item.quantity * getItemProductForInvoice(item).pieces_per_package }} copa
                              </span>
                            </template>
                            <!-- For other products -->
                            <template v-else>
                              <!-- Check if quantity is in pieces or packages -->
                              <div class="font-medium" v-if="isItemQuantityInPieces(item)">
                                {{ parseFloat(item.quantity).toFixed(0) }} copa
                              </div>
                              <!-- If quantity is in packages -->
                              <div class="font-medium" v-else-if="getItemProductForInvoice(item) && getItemProductForInvoice(item).sold_by_package && getItemProductForInvoice(item).pieces_per_package">
                                {{ parseFloat(item.quantity).toFixed(0) }} kompleti
                              </div>
                              <div class="font-medium" v-else>
                                {{ item.quantity }}
                              </div>
                              <!-- Show conversion only if item is in packages (not cp) -->
                              <span v-if="!isItemQuantityInPieces(item) && getItemProductForInvoice(item) && getItemProductForInvoice(item).sold_by_package && getItemProductForInvoice(item).pieces_per_package" class="text-xs text-gray-500 mt-1 block">
                                = {{ (item.quantity * getItemProductForInvoice(item).pieces_per_package).toFixed(0) }} copa
                              </span>
                            </template>
                          </td>
                          <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-gray-700 text-right">
                            <!-- For products sold by package where quantity is in packages, show unit price per piece -->
                            <template v-if="getItemProductForInvoice(item) && getItemProductForInvoice(item).sold_by_package && getItemProductForInvoice(item).pieces_per_package && !isItemQuantityInPieces(item)">
                              <div class="font-medium">{{ formatPrice(item.unit_price / getItemProductForInvoice(item).pieces_per_package) }}</div>
                              <span class="text-xs text-gray-500">(për copë)</span>
                            </template>
                            <!-- For items in pieces or products not sold by package -->
                            <template v-else>
                              {{ formatPrice(item.unit_price) }}
                            </template>
                          </td>
                          <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-semibold text-gray-900 text-right">{{ formatPrice(item.total_price) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Invoice Summary -->
                <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm border border-gray-200">
                  <div class="space-y-3">
                    <div class="flex justify-between items-center">
                      <span class="text-sm font-medium text-gray-700">Nëntotali:</span>
                      <span class="text-sm font-semibold text-gray-900">{{ formatPrice(selectedInvoice.subtotal) }}</span>
                    </div>
                    <div v-if="selectedInvoice.has_vat" class="flex justify-between items-center">
                      <span class="text-sm font-medium text-gray-700">TVSH ({{ selectedInvoice.vat_rate }}%):</span>
                      <span class="text-sm font-semibold text-gray-900">{{ formatPrice(selectedInvoice.vat_amount) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300">
                      <span class="text-lg sm:text-xl font-bold text-gray-900">Totali:</span>
                      <span class="text-lg sm:text-xl font-bold text-primary-600">{{ formatPrice(selectedInvoice.total_amount) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Notes -->
                <div v-if="selectedInvoice.notes" class="bg-white rounded-lg p-4 sm:p-6 shadow-sm border border-gray-200">
                  <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Shënime</p>
                  <p class="text-sm text-gray-900 leading-relaxed">{{ selectedInvoice.notes }}</p>
                </div>
              </div>
            </div>
            <div class="px-4 sm:px-6 py-4 sm:py-5 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-end gap-3">
              <button 
                @click="closeInvoiceViewModal"
                class="px-4 sm:px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
              >
                Mbyll
              </button>
              <button 
                v-if="selectedInvoice && selectedInvoice.status !== 'paid'"
                @click.stop="deleteInvoice(selectedInvoice)"
                type="button"
                class="px-4 sm:px-6 py-2.5 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors"
              >
                🗑️ Fshi Faturën
              </button>
              <button 
                @click="printInvoice(selectedInvoice)"
                class="px-4 sm:px-6 py-2.5 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 transition-colors"
              >
                🖨️ Printo Faturën
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Invoice Modal -->
      <div v-if="showInvoiceEditModal && editingInvoice" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-0 sm:top-4 mx-auto max-w-4xl w-full p-3 sm:p-6">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 border-b border-gray-200 gap-3">
              <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Edito Faturën</h3>
              <button @click="closeInvoiceEditModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none self-start sm:self-auto">
                &times;
              </button>
            </div>
            <div class="px-4 sm:px-6 py-4 sm:py-5 max-h-[90vh] sm:max-h-[80vh] overflow-y-auto">
              <form @submit.prevent="saveEditedInvoice">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data e Faturës</label>
                    <input 
                      v-model="editingInvoice.invoice_date"
                      type="date"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data e Maturimit</label>
                    <input 
                      v-model="editingInvoice.due_date"
                      type="date"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statusi</label>
                    <select 
                      v-model="editingInvoice.status"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                      <option value="pending">Në Pritje</option>
                      <option value="paid">E Paguar</option>
                      <option value="overdue">Vonuar</option>
                      <option value="cancelled">Anuluar</option>
                    </select>
                  </div>
                  <div v-if="editingInvoice.status === 'paid'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data e Pagesës</label>
                    <input 
                      v-model="editingInvoice.paid_date"
                      type="date"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                  </div>
                </div>

                <div class="mb-6">
                  <div class="flex items-center mb-3">
                    <input 
                      type="checkbox"
                      v-model="editingInvoice.has_vat"
                      id="edit_has_vat"
                      class="mr-2 h-4 w-4 text-primary-600"
                    >
                    <label for="edit_has_vat" class="text-sm font-medium text-gray-700">
                      Me TVSH
                    </label>
                  </div>
                  <div v-if="editingInvoice.has_vat" class="mt-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Përqindja e TVSH (%)</label>
                    <input 
                      v-model.number="editingInvoice.vat_rate"
                      type="number"
                      step="0.01"
                      min="0"
                      max="100"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                  </div>
                </div>

                <!-- Invoice Items -->
                <div class="mb-6">
                  <div class="flex justify-between items-center mb-3">
                    <h4 class="font-semibold text-gray-900">Produktet</h4>
                    <button 
                      type="button"
                      @click="addInvoiceItem"
                      class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-md hover:bg-green-700"
                    >
                      + Shto Produkt
                    </button>
                  </div>
                  <div class="space-y-3">
                    <div 
                      v-for="(item, index) in editingInvoice.items" 
                      :key="index"
                      class="border border-gray-200 rounded-lg p-4 bg-white"
                    >
                      <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-4">
                          <label class="block text-xs font-medium text-gray-700 mb-1">Produkti *</label>
                          <select 
                            v-model="item.product_id"
                            @change="onInvoiceProductChange(item, index)"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                          >
                            <option value="">Zgjidhni produktin</option>
                            <option v-for="product in allProducts" :key="product && product.id ? product.id : `product-option-${Math.random()}`" :value="product && product.id ? product.id : ''">
                              {{ product && product.name ? product.name : '' }}
                            </option>
                          </select>
                        </div>
                        <div class="md:col-span-2">
                          <label class="block text-xs font-medium text-gray-700 mb-1">
                            Sasia *
                            <span v-if="isKeseMbeturinash({ name: item.product_name }) && item.unit_type === 'kg'" class="text-gray-500 font-normal">
                              (në kg)
                            </span>
                            <span v-else-if="item.product && item.product.sold_by_package && item.product.pieces_per_package" class="text-gray-500 font-normal">
                              (në kompleti)
                            </span>
                          </label>
                          <input 
                            v-model.number="item.quantity"
                            @input="calculateInvoiceItemTotal(item, index)"
                            type="number"
                            :min="isKeseMbeturinash({ name: item.product_name }) && item.unit_type === 'kg' ? 0.01 : 1"
                            :step="isKeseMbeturinash({ name: item.product_name }) && item.unit_type === 'kg' ? 0.01 : 1"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                          >
                          <p v-if="isKeseMbeturinash({ name: item.product_name }) && item.unit_type === 'kg'" class="text-xs text-gray-500 mt-1">
                            Sasia: <span class="text-primary-600 font-semibold">{{ parseFloat(item.quantity || 0).toFixed(2) }} kg</span>
                          </p>
                          <p v-else-if="item.product && item.product.sold_by_package && item.product.pieces_per_package" class="text-xs text-gray-500 mt-1">
                            {{ item.quantity }} kompleti × {{ item.product.pieces_per_package }}cp = {{ item.quantity * item.product.pieces_per_package }} copa
                          </p>
                        </div>
                        <div class="md:col-span-2">
                          <label class="block text-xs font-medium text-gray-700 mb-1">
                            Çmimi Njësi *
                            <span v-if="isKeseMbeturinash({ name: item.product_name }) && item.unit_type === 'kg'" class="text-gray-500 font-normal text-xs">
                              (për kg)
                            </span>
                            <span v-else-if="item.product && item.product.sold_by_package && item.product.pieces_per_package" class="text-gray-500 font-normal text-xs">
                              (për kompleti)
                            </span>
                          </label>
                          <input 
                            v-model.number="item.unit_price"
                            @input="calculateInvoiceItemTotal(item, index)"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                          >
                        </div>
                        <div class="md:col-span-2">
                          <label class="block text-xs font-medium text-gray-700 mb-1">Totali</label>
                          <input 
                            :value="formatPrice(item.total_price || 0)"
                            type="text"
                            readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50"
                          >
                        </div>
                        <div class="md:col-span-2 flex gap-2">
                          <button 
                            type="button"
                            @click="removeInvoiceItem(index)"
                            class="px-3 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 w-full"
                          >
                            🗑️
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mb-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Shënime</label>
                  <textarea 
                    v-model="editingInvoice.notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md"
                  ></textarea>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3">
                  <button 
                    type="button"
                    @click="closeInvoiceEditModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                  >
                    Anulo
                  </button>
                  <button 
                    type="submit"
                    :disabled="savingInvoice"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 disabled:opacity-50"
                  >
                    {{ savingInvoice ? 'Duke ruajtur...' : 'Ruaj Ndryshimet' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

// Ensure token is set from localStorage on import
const adminToken = localStorage.getItem('admin_token')
if (adminToken) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${adminToken}`
}

// Add request interceptor to ensure token is always included
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('admin_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

export default {
  name: 'AdminStock',
  data() {
    return {
      products: [],
      allProducts: [], // For dropdown in receipt modal
      suppliers: [],
      stockReport: {},
      invoices: [],
      activeTab: 'stock',
      filters: {
        search: '',
        supplier_id: '',
        stockFilter: ''
      },
      invoiceFilters: {
        supplier_id: '',
        status: '',
        date_from: '',
        date_to: ''
      },
      showReceiptModal: false,
      showSupplierModal: false,
      showStockAdjustmentModal: false,
      showInvoiceViewModal: false,
      showInvoiceEditModal: false,
      editingInvoice: null,
      savingInvoice: false,
      savingReceipt: false,
      adjustingStock: false,
      adjustingProduct: null,
      selectedInvoice: null,
      editingProductId: null,
      editingField: null,
      editingProduct: {},
      stockAdjustmentForm: {
        adjustment_type: 'increase',
        quantity: 0,
        unit: 'cp', // Default unit: Copa
        notes: ''
      },
      receiptForm: {
        supplier_id: '',
        receipt_date: new Date().toISOString().split('T')[0],
        notes: '',
        items: [],
        create_invoice: false,
        invoice_date: new Date().toISOString().split('T')[0],
        due_date: '',
        has_vat: false,
        vat_rate: 18.00
      },
      supplierForm: {
        name: '',
        contact_person: '',
        email: '',
        phone: '',
        address: '',
        notes: ''
      }
    }
  },
  computed: {
    filteredProducts() {
      // Ensure products is an array and filter out any undefined/null/invalid products
      if (!Array.isArray(this.products)) {
        return []
      }
      
      // Filter out any undefined/null products or products without id
      let filtered = this.products.filter(p => {
        return p !== null && 
               p !== undefined && 
               typeof p === 'object' && 
               p.id !== undefined && 
               p.id !== null &&
               p.id !== ''
      })

      if (this.filters.search) {
        const search = this.filters.search.toLowerCase()
        filtered = filtered.filter(p => {
          if (!p || !p.name) return false
          return p.name.toLowerCase().includes(search) ||
                 (p.description && typeof p.description === 'string' && p.description.toLowerCase().includes(search))
        })
      }

      if (this.filters.supplier_id) {
        filtered = filtered.filter(p => p && p.supplier_id == this.filters.supplier_id)
      }

      if (this.filters.stockFilter === 'low_stock') {
        filtered = filtered.filter(p => {
          return p && 
                 p.stock_quantity !== undefined && 
                 p.stock_quantity !== null &&
                 p.min_stock_level !== undefined &&
                 p.min_stock_level !== null &&
                 p.stock_quantity <= p.min_stock_level && 
                 p.stock_quantity > 0
        })
      } else if (this.filters.stockFilter === 'out_of_stock') {
        filtered = filtered.filter(p => {
          return p && 
                 p.stock_quantity !== undefined && 
                 p.stock_quantity !== null &&
                 p.stock_quantity <= 0
        })
      }

      // Final safety check - ensure all products have valid id
      return filtered.filter(p => p && p.id !== undefined && p.id !== null)
    }
  },
  async mounted() {
    await this.checkAuth()
    await Promise.all([
      this.loadStock(),
      this.loadSuppliers(),
      this.loadStockReport(),
      this.loadProducts(),
      this.loadInvoices()
    ])
    
    // Listen for order restore events from trash page
    window.addEventListener('order-restored', this.handleOrderRestored)
  },
  beforeUnmount() {
    // Clean up event listener
    window.removeEventListener('order-restored', this.handleOrderRestored)
  },
  // Refresh data when component is activated (e.g., when navigating back from clients page)
  async activated() {
    await this.loadStock()
    await this.loadStockReport()
  },
  // Refresh data when route is updated (e.g., when navigating to this page)
  async beforeRouteUpdate(to, from, next) {
    await this.loadStock()
    await this.loadStockReport()
    next()
  },
  methods: {
    async handleOrderRestored(event) {
      // When an order is restored from trash, refresh stock to recalculate shortages
      console.log('Order restored, refreshing stock...', event.detail)
      await this.loadStock()
      await this.loadStockReport()
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
    async logout() {
      try {
        await axios.post('/api/admin/logout')
      } catch (error) {
        // Continue with logout even if API call fails
      }
      localStorage.removeItem('admin_token')
      delete axios.defaults.headers.common['Authorization']
      this.$router.push('/admin/login')
    },
    async loadStock() {
      try {
        const params = {}
        if (this.filters.stockFilter === 'low_stock') {
          params.low_stock = true
        } else if (this.filters.stockFilter === 'out_of_stock') {
          params.out_of_stock = true
        }
        if (this.filters.supplier_id) {
          params.supplier_id = this.filters.supplier_id
        }
        if (this.filters.search) {
          params.search = this.filters.search
        }

        const response = await axios.get('/api/stock', { params })
        // Filter out any undefined/null products and ensure all have required properties
        const rawProducts = response.data.data || []
        this.products = rawProducts.filter(p => {
          return p !== null && 
                 p !== undefined && 
                 typeof p === 'object' && 
                 p.id !== undefined && 
                 p.id !== null &&
                 p.id !== ''
        })
        // Debug: Check if shortage data is present
        console.log('Loaded products:', this.products.length)
        const foliNajlloni = this.products.find(p => p.name && p.name.includes('Foli Najlloni'))
        if (foliNajlloni) {
          console.log('🔍 Foli Najlloni data:', {
            id: foliNajlloni.id,
            name: foliNajlloni.name,
            stock_quantity: foliNajlloni.stock_quantity,
            shortage_quantity: foliNajlloni.shortage_quantity,
            shortage_packages: foliNajlloni.shortage_packages,
            sold_by_package: foliNajlloni.sold_by_package,
            pieces_per_package: foliNajlloni.pieces_per_package,
            hasShortage: foliNajlloni.shortage_quantity && foliNajlloni.shortage_quantity > 0
          })
        }
        const productWithShortage = this.products.find(p => p.shortage_quantity && p.shortage_quantity > 0)
        if (productWithShortage) {
          console.log('✅ Product with shortage found:', productWithShortage.name, {
            shortage_quantity: productWithShortage.shortage_quantity,
            shortage_packages: productWithShortage.shortage_packages,
            sold_by_package: productWithShortage.sold_by_package,
            pieces_per_package: productWithShortage.pieces_per_package
          })
        } else {
          console.log('❌ No products with shortage found')
        }
      } catch (error) {
        console.error('Error loading stock:', error)
        alert('Gabim në ngarkimin e stokut')
      }
    },
    async loadSuppliers() {
      try {
        const response = await axios.get('/api/suppliers', { params: { active_only: true } })
        const rawSuppliers = response.data.data || []
        // Filter out any undefined/null suppliers and ensure all have required properties
        this.suppliers = rawSuppliers.filter(s => {
          return s !== null && 
                 s !== undefined && 
                 typeof s === 'object' && 
                 s.id !== undefined && 
                 s.id !== null &&
                 s.id !== ''
        })
      } catch (error) {
        console.error('Error loading suppliers:', error)
        this.suppliers = []
      }
    },
    async loadStockReport() {
      try {
        const response = await axios.get('/api/stock/report')
        this.stockReport = response.data.data || {}
      } catch (error) {
        console.error('Error loading stock report:', error)
      }
    },
    async loadProducts() {
      try {
        const response = await axios.get('/api/products')
        const rawProducts = response.data.data || []
        // Filter out any undefined/null products and ensure all have required properties
        this.allProducts = rawProducts.filter(p => {
          return p !== null && 
                 p !== undefined && 
                 typeof p === 'object' && 
                 p.id !== undefined && 
                 p.id !== null &&
                 p.id !== ''
        })
      } catch (error) {
        console.error('Error loading products:', error)
        this.allProducts = []
      }
    },
    formatPrice(price) {
      if (price === null || price === undefined) {
        return '€0.00'
      }
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },
    addReceiptItem() {
      this.receiptForm.items.push({
        product_id: '',
        product: null, // Initialize product object
        quantity: 1,
        unit_cost: 0,
        total_cost: 0,
        unit_type: null // Will be set when product is selected
      })
    },
    removeReceiptItem(index) {
      this.receiptForm.items.splice(index, 1)
    },
    onProductChange(item, index) {
      const product = this.allProducts.find(p => p.id == item.product_id)
      if (product) {
        // Set product object directly - use index to ensure reactivity
        this.receiptForm.items[index].product = product
        // Set default unit_type for Kese Mbeturinash
        if (this.isKeseMbeturinash(product)) {
          if (!this.receiptForm.items[index].unit_type) {
            this.receiptForm.items[index].unit_type = 'package'
          }
        } else {
          this.receiptForm.items[index].unit_type = null
        }
        if (product.cost_price) {
          // For all products, use cost_price as is (it's already per piece or per unit)
          // For products sold by package, unit_cost will be per piece
          // For Kese Mbeturinash with kg unit, unit_cost will be per kg
          this.receiptForm.items[index].unit_cost = product.cost_price
        }
        this.calculateItemTotal(this.receiptForm.items[index], index)
      } else {
        // Clear product if not found
        this.receiptForm.items[index].product = null
        this.receiptForm.items[index].unit_type = null
      }
    },
    onUnitTypeChange(event, item, index) {
      // Get the new unit_type value from the select element
      const newUnitType = event.target.value
      
      // Reset quantity when unit type changes
      const currentItem = index !== undefined ? this.receiptForm.items[index] : item
      
      // Ensure unit_type is set correctly
      if (index !== undefined) {
        this.receiptForm.items[index].unit_type = newUnitType
      } else {
        currentItem.unit_type = newUnitType
      }
      
      // Reset quantity based on unit type
      const unitType = index !== undefined ? this.receiptForm.items[index].unit_type : currentItem.unit_type
      if (index !== undefined) {
        this.receiptForm.items[index].quantity = unitType === 'kg' ? 0.01 : 1
      } else {
        currentItem.quantity = unitType === 'kg' ? 0.01 : 1
      }
      
      this.calculateItemTotal(currentItem, index)
    },
    isKeseMbeturinash(product) {
      if (!product) return false
      const name = product.name || product.product_name || ''
      return name.toLowerCase().includes('kese mbeturinash')
    },
    // Helper method to determine if item was received in kg based on quantity pattern
    // For Kese Mbeturinash, if quantity is a decimal (e.g., 0.5, 1.2) it's likely kg
    // If quantity is a whole number (e.g., 200, 300) it's likely packages
    isItemInKg(item) {
      if (!this.isKeseMbeturinash({ name: item.product_name })) return false
      
      // If unit_type is explicitly set, use it
      if (item.unit_type === 'kg') return true
      if (item.unit_type === 'package') return false
      
      // If unit_type is null/undefined, try to get it from stock receipt items
      if (this.selectedInvoice && this.selectedInvoice.stock_receipt && this.selectedInvoice.stock_receipt.items) {
        const receiptItem = this.selectedInvoice.stock_receipt.items.find(
          ri => ri.product_id === item.product_id
        )
        if (receiptItem && receiptItem.unit_type === 'kg') return true
        if (receiptItem && receiptItem.unit_type === 'package') return false
      }
      
      // Fallback: try to infer from quantity (if quantity has decimal places, it's likely kg)
      const quantity = parseFloat(item.quantity)
      return quantity % 1 !== 0 // Has decimal places
    },
    calculateItemTotal(item, index) {
      if (!item) return
      
      // Use index to get the item from receiptForm.items to ensure we have the latest data
      const currentItem = index !== undefined ? this.receiptForm.items[index] : item
      if (!currentItem) return
      
      // Get product - try multiple ways to ensure we get it
      let product = currentItem.product
      if (!product && currentItem.product_id) {
        product = this.allProducts.find(p => p.id == currentItem.product_id)
      }
      
      if (!product) {
        currentItem.total_cost = 0
        return
      }
      
      let total = 0
      const quantity = parseFloat(currentItem.quantity) || 0
      const unitCost = parseFloat(currentItem.unit_cost) || 0
      
      // For Kese Mbeturinash with package unit
      if (this.isKeseMbeturinash(product) && currentItem.unit_type === 'package' && product.sold_by_package && product.pieces_per_package) {
        // (quantity in kompleti × pieces_per_package) × unit_cost (per piece)
        const totalPieces = quantity * product.pieces_per_package
        total = totalPieces * unitCost
      } 
      // For Kese Mbeturinash with kg unit
      else if (this.isKeseMbeturinash(product) && currentItem.unit_type === 'kg') {
        // quantity (kg) × unit_cost (per kg)
        total = quantity * unitCost
      } 
      // For other products sold by package
      else if (product.sold_by_package && product.pieces_per_package) {
        // (quantity in kompleti × pieces_per_package) × unit_cost (per piece)
        const totalPieces = quantity * product.pieces_per_package
        total = totalPieces * unitCost
      } 
      // For products not sold by package
      else {
        // quantity × unit_cost
        total = quantity * unitCost
      }
      
      currentItem.total_cost = Math.round(total * 100) / 100 // Round to 2 decimal places
      
      // Force Vue reactivity by updating the item in the array
      if (index !== undefined && this.receiptForm.items[index]) {
        this.receiptForm.items[index].total_cost = currentItem.total_cost
      }
    },
    calculateTotalItems() {
      return this.receiptForm.items.reduce((sum, item) => sum + (item.quantity || 0), 0)
    },
    calculateTotalItemsByUnit() {
      // Calculate totals separately for kg and cp
      const items = this.receiptForm.items.filter(item => item.product_id)
      let totalKg = 0
      let totalCp = 0
      
      items.forEach(item => {
        const product = this.allProducts.find(p => p.id == item.product_id)
        if (!product) return
        
        const quantity = parseFloat(item.quantity || 0)
        
        // Check if this is Kese Mbeturinash with kg unit
        if (this.isKeseMbeturinash(product) && item.unit_type === 'kg') {
          totalKg += quantity
        } else {
          // For package-based products, calculate total pieces
          if (product.sold_by_package && product.pieces_per_package) {
            if (this.isKeseMbeturinash(product) && item.unit_type === 'package') {
              // Kese Mbeturinash in packages: quantity (packages) * pieces_per_package
              totalCp += quantity * product.pieces_per_package
            } else if (!this.isKeseMbeturinash(product)) {
              // Other package-based products: quantity (packages) * pieces_per_package
              totalCp += quantity * product.pieces_per_package
            } else {
              // Fallback: treat as pieces
              totalCp += quantity
            }
          } else {
            // Products not sold by package: treat as pieces
            totalCp += quantity
          }
        }
      })
      
      return {
        kg: totalKg,
        cp: totalCp
      }
    },
    calculateInvoiceItemsByUnit(invoiceItems) {
      // Calculate totals separately for kg and cp from invoice items
      if (!invoiceItems || invoiceItems.length === 0) {
        return { kg: 0, cp: 0 }
      }
      
      let totalKg = 0
      let totalCp = 0
      
      invoiceItems.forEach(item => {
        const product = this.allProducts.find(p => p.id == item.product_id) || item.product
        if (!product) return
        
        const quantity = parseFloat(item.quantity || 0)
        
        // Check if this is Kese Mbeturinash with kg unit
        if (this.isKeseMbeturinash({ name: item.product_name }) && item.unit_type === 'kg') {
          totalKg += quantity
        } else {
          // For package-based products, calculate total pieces
          if (product.sold_by_package && product.pieces_per_package) {
            // If unit_type is package or null (default), calculate pieces
            if (item.unit_type === 'package' || !item.unit_type) {
              totalCp += quantity * product.pieces_per_package
            } else {
              // If already in pieces, just add
              totalCp += quantity
            }
          } else {
            // Products not sold by package: treat as pieces
            totalCp += quantity
          }
        }
      })
      
      return {
        kg: totalKg,
        cp: totalCp
      }
    },
    getTotalItemsUnit() {
      // Check if all items are in kg or if there's a mix
      const items = this.receiptForm.items.filter(item => item.product_id)
      if (items.length === 0) return ''
      
      // Check if all items are Kese Mbeturinash with kg unit
      const allKg = items.every(item => {
        const product = this.allProducts.find(p => p.id == item.product_id)
        return this.isKeseMbeturinash(product) && item.unit_type === 'kg'
      })
      
      if (allKg) return 'kg'
      
      // Check if all items are in pieces (not kg)
      const allPieces = items.every(item => {
        const product = this.allProducts.find(p => p.id == item.product_id)
        if (!product) return false
        if (this.isKeseMbeturinash(product) && item.unit_type === 'kg') return false
        return true
      })
      
      if (allPieces) return 'pc'
      
      // Mixed units
      return 'pc'
    },
    calculateReceiptTotal() {
      return this.receiptForm.items.reduce((sum, item) => sum + (item.total_cost || 0), 0)
    },
    async saveReceipt() {
      if (this.receiptForm.items.length === 0) {
        alert('Ju lutem shtoni të paktën një produkt!')
        return
      }

      this.savingReceipt = true
      try {
        // Prepare items: if product is sold by package, convert quantity to pieces
        const itemsToSend = this.receiptForm.items.map(item => {
          const product = this.allProducts.find(p => p.id == item.product_id)
          let quantity = item.quantity || 0
          let unitCost = item.unit_cost || 0
          
          // For Kese Mbeturinash with package unit, convert quantity to pieces
          // unit_cost is already per piece, so no conversion needed
          if (this.isKeseMbeturinash(product)) {
            if (item.unit_type === 'package' && product.sold_by_package && product.pieces_per_package) {
              // Convert quantity from packages to pieces
              quantity = quantity * product.pieces_per_package
              // unit_cost is already per piece, no conversion needed
            }
            // If unit_type is 'kg', quantity stays as kg and unit_cost stays as per kg (no conversion)
          } else if (product && product.sold_by_package && product.pieces_per_package) {
            // For other products sold by package, convert quantity to pieces
            // unit_cost is already per piece, so no conversion needed
            quantity = quantity * product.pieces_per_package
          }
          
          // Determine unit_type: use item.unit_type if set, otherwise infer from product
          let finalUnitType = item.unit_type
          
          // If unit_type is not set and this is Kese Mbeturinash, try to infer it
          if (!finalUnitType && this.isKeseMbeturinash(product)) {
            // Check the original quantity (before conversion) to infer unit type
            const originalQty = parseFloat(item.quantity || 0)
            
            // If quantity has decimal places (e.g., 200.00, 12.50), it's likely kg
            // If quantity is a whole number and relatively small (e.g., 10, 20, 50), it's likely packages
            if (originalQty > 0) {
              // If quantity has decimal places OR is very large (> 100), assume kg
              if (originalQty % 1 !== 0 || originalQty > 100) {
                finalUnitType = 'kg'
              } else {
                // Otherwise, assume package
                finalUnitType = 'package'
              }
            } else {
              // Default to package if quantity is 0
              finalUnitType = 'package'
            }
          }
          
          // Ensure unit_type is set for Kese Mbeturinash
          if (this.isKeseMbeturinash(product) && !finalUnitType) {
            finalUnitType = 'package' // Default fallback
          }
          
          return {
            product_id: item.product_id,
            quantity: quantity, // Send quantity in pieces (or kg for Kese Mbeturinash)
            unit_cost: unitCost, // Send unit_cost per piece (or per kg for Kese Mbeturinash)
            notes: item.notes || null,
            unit_type: finalUnitType // Include unit_type for reference
          }
        })
        
        const receiptData = {
          ...this.receiptForm,
          items: itemsToSend
        }
        
        // Debug: log items to send
        console.log('Items to send:', itemsToSend.map(item => ({
          product_id: item.product_id,
          quantity: item.quantity,
          unit_type: item.unit_type,
          unit_cost: item.unit_cost
        })))
        console.log('Receipt form items before mapping:', this.receiptForm.items.map(item => ({
          product_id: item.product_id,
          quantity: item.quantity,
          unit_type: item.unit_type,
          product: item.product ? item.product.name : null
        })))
        
        const response = await axios.post('/api/stock-receipts', receiptData)
        alert('Pranimi u ruajt me sukses!')
        this.closeReceiptModal()
        await Promise.all([
          this.loadStock(),
          this.loadStockReport(),
          this.loadInvoices()
        ])
      } catch (error) {
        console.error('Error saving receipt:', error)
        alert('Gabim në ruajtjen e pranimit')
      } finally {
        this.savingReceipt = false
      }
    },
    closeReceiptModal() {
      this.showReceiptModal = false
      this.receiptForm = {
        supplier_id: '',
        receipt_date: new Date().toISOString().split('T')[0],
        notes: '',
        items: []
      }
    },
    async saveSupplier() {
      try {
        await axios.post('/api/suppliers', this.supplierForm)
        alert('Prodhuesi u ruajt me sukses!')
        this.closeSupplierModal()
        await this.loadSuppliers()
      } catch (error) {
        console.error('Error saving supplier:', error)
        alert('Gabim në ruajtjen e prodhuesit')
      }
    },
    closeSupplierModal() {
      this.showSupplierModal = false
      this.supplierForm = {
        name: '',
        contact_person: '',
        email: '',
        phone: '',
        address: '',
        notes: ''
      }
    },
    openStockAdjustmentModal(product) {
      this.adjustingProduct = product
      // Determine default unit based on product type
      const defaultUnit = this.getDefaultUnitForProduct(product)
      this.stockAdjustmentForm = {
        adjustment_type: 'increase',
        quantity: 0,
        unit: defaultUnit,
        notes: ''
      }
      this.showStockAdjustmentModal = true
    },
    closeStockAdjustmentModal() {
      this.showStockAdjustmentModal = false
      this.adjustingProduct = null
      this.stockAdjustmentForm = {
        adjustment_type: 'increase',
        quantity: 0,
        unit: 'cp',
        notes: ''
      }
    },
    // Get default unit for a product based on its characteristics
    getDefaultUnitForProduct(product) {
      if (!product) return 'cp'
      
      const name = (product.name || '').toLowerCase()
      
      // Kese Mbeturinash - use kg
      if (name.includes('kese mbeturinash')) {
        return 'kg'
      }
      
      // Liquid products - use L or ml
      if (name.includes('ujë') || name.includes('lëng') || name.includes('vaj') || name.includes('qumësht')) {
        return 'L'
      }
      
      // Weight-based products - use kg or g
      if (name.includes('sheqer') || name.includes('miell') || name.includes('oriz') || name.includes('fasule')) {
        return 'kg'
      }
      
      // Default to copa
      return 'cp'
    },
    calculateNewStock(type) {
      if (!this.adjustingProduct) return 0
      
      const currentStock = this.adjustingProduct.stock_quantity || 0
      const inputQuantity = this.stockAdjustmentForm.quantity || 0
      const inputUnit = this.stockAdjustmentForm.unit || 'cp'
      
      // Convert input quantity to base unit (copa)
      const quantityInBaseUnit = this.convertToBaseUnit(inputQuantity, inputUnit, this.adjustingProduct)
      
      if (type === 'increase') {
        return currentStock + quantityInBaseUnit
      } else if (type === 'decrease') {
        return Math.max(0, currentStock - quantityInBaseUnit)
      } else {
        return quantityInBaseUnit
      }
    },
    // Convert quantity from input unit to base unit (copa)
    convertToBaseUnit(quantity, unit, product) {
      if (!quantity || !unit || !product) return 0
      
      const qty = parseFloat(quantity) || 0
      
      // Base unit is always "copa" (pieces)
      // For now, we'll use simple conversions
      // This can be enhanced with product-specific conversion factors
      
      switch (unit) {
        case 'cp':
        case 'pcs':
          return qty
        case 'kg':
          // For Kese Mbeturinash and weight-based products, assume 1 kg = 1 piece for now
          // This should be configurable per product
          return qty
        case 'g':
          return qty / 1000 // 1000g = 1kg, then convert to pieces
        case 'L':
          return qty // 1L = 1 piece (for liquids)
        case 'ml':
          return qty / 1000 // 1000ml = 1L, then convert to pieces
        case 'm':
          return qty * 100 // 1m = 100cm, then convert to pieces
        case 'cm':
          return qty / 100 // 100cm = 1m, then convert to pieces
        default:
          return qty
      }
    },
    // Get minimum value for input based on unit
    getMinValueForUnit(unit) {
      switch (unit) {
        case 'kg':
        case 'L':
        case 'm':
          return 0.01
        case 'g':
        case 'ml':
        case 'cm':
          return 0.1
        default:
          return 0
      }
    },
    // Get step value for input based on unit
    getStepForUnit(unit) {
      switch (unit) {
        case 'kg':
        case 'L':
        case 'm':
          return 0.01
        case 'g':
        case 'ml':
        case 'cm':
          return 0.1
        default:
          return 1
      }
    },
    // Get placeholder for input based on unit
    getPlaceholderForUnit(unit) {
      return `Sasia në ${this.getUnitLabel(unit)}`
    },
    // Get label for unit
    getUnitLabel(unit) {
      const labels = {
        'cp': 'Copa',
        'kg': 'Kilogram',
        'g': 'Gram',
        'L': 'Liter',
        'ml': 'Mililitër',
        'm': 'Metër',
        'cm': 'Centimetër',
        'pcs': 'Pjesë'
      }
      return labels[unit] || unit
    },
    // Format quantity with unit for display
    formatQuantityWithUnit(quantity, product) {
      if (!product) return quantity
      
      const qty = parseFloat(quantity) || 0
      const name = (product.name || '').toLowerCase()
      
      // For Kese Mbeturinash, show in kg if quantity is small
      if (name.includes('kese mbeturinash')) {
        if (qty < 100 && qty > 0) {
          return `${qty.toFixed(2)} kg`
        }
        return `${qty} copa`
      }
      
      // For products sold by package, show in packages and pieces
      if (product.sold_by_package && product.pieces_per_package) {
        const packages = Math.floor(qty / product.pieces_per_package)
        const pieces = qty % product.pieces_per_package
        if (packages > 0 && pieces > 0) {
          return `${packages} kompleti + ${pieces} copa`
        } else if (packages > 0) {
          return `${packages} kompleti`
        } else {
          return `${pieces} copa`
        }
      }
      
      // Default: show in copa
      return `${qty} copa`
    },
    // Handle unit change
    onUnitChange() {
      // Reset quantity when unit changes to avoid confusion
      this.stockAdjustmentForm.quantity = 0
    },
    async saveStockAdjustment() {
      if (!this.adjustingProduct) return
      
      this.adjustingStock = true
      try {
        // Convert quantity to base unit before sending to API
        const quantityInBaseUnit = this.convertToBaseUnit(
          this.stockAdjustmentForm.quantity,
          this.stockAdjustmentForm.unit,
          this.adjustingProduct
        )
        
        const payload = {
          adjustment_type: this.stockAdjustmentForm.adjustment_type,
          quantity: quantityInBaseUnit, // Send converted quantity
          original_quantity: this.stockAdjustmentForm.quantity, // Keep original for reference
          original_unit: this.stockAdjustmentForm.unit, // Keep original unit for reference
          notes: this.stockAdjustmentForm.notes
        }
        
        await axios.post(`/api/stock/adjust/${this.adjustingProduct.id}`, payload)
        alert('Stoku u rregullua me sukses!')
        this.closeStockAdjustmentModal()
        await Promise.all([
          this.loadStock(),
          this.loadStockReport()
        ])
      } catch (error) {
        console.error('Error adjusting stock:', error)
        alert('Gabim në rregullimin e stokut')
      } finally {
        this.adjustingStock = false
      }
    },
    async loadInvoices() {
      try {
        const params = {}
        if (this.invoiceFilters.supplier_id) {
          params.supplier_id = this.invoiceFilters.supplier_id
        }
        if (this.invoiceFilters.status) {
          params.status = this.invoiceFilters.status
        }
        if (this.invoiceFilters.date_from) {
          params.date_from = this.invoiceFilters.date_from
        }
        if (this.invoiceFilters.date_to) {
          params.date_to = this.invoiceFilters.date_to
        }

        const response = await axios.get('/api/supplier-invoices', { params })
        const rawInvoices = response.data.data || []
        // Filter out any undefined/null invoices and ensure all have required properties
        this.invoices = rawInvoices.filter(i => {
          return i !== null && 
                 i !== undefined && 
                 typeof i === 'object' && 
                 i.id !== undefined && 
                 i.id !== null &&
                 i.id !== ''
        })
      } catch (error) {
        console.error('Error loading invoices:', error)
        alert('Gabim në ngarkimin e faturës')
      }
    },
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('sq-AL', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      })
    },
    getStatusLabel(status) {
      const labels = {
        'pending': 'Në Pritje',
        'paid': 'E Paguar',
        'overdue': 'Vonuar',
        'cancelled': 'Anuluar'
      }
      return labels[status] || status
    },
    getItemProductForInvoice(item) {
      // Try to get product from item.product (if loaded from API) or from allProducts
      if (item.product) {
        return item.product
      }
      if (item.product_id && this.allProducts.length > 0) {
        return this.allProducts.find(p => p.id == item.product_id)
      }
      return null
    },
    // Helper method to determine if item quantity is in pieces (cp)
    // For products sold by package, if unit_price is high (> 1€ typically), it's likely per package
    // If unit_price is low (< 1€ typically), it's likely per piece
    isItemQuantityInPieces(item) {
      // If unit_type is explicitly set to 'cp', it's in pieces
      if (item.unit_type === 'cp') return true
      
      // If unit_type is 'package' or 'kg', it's not in pieces
      if (item.unit_type === 'package' || item.unit_type === 'kg') return false
      
      // If unit_type is null/undefined, try to infer from unit_price
      const product = this.getItemProductForInvoice(item)
      if (product && product.sold_by_package && product.pieces_per_package) {
        const unitPrice = parseFloat(item.unit_price || 0)
        const quantity = parseFloat(item.quantity || 0)
        const totalPrice = parseFloat(item.total_price || 0)
        
        // Calculate unit price per piece
        const unitPricePerPiece = unitPrice / product.pieces_per_package
        
        // If unit_price is high (typically > 1€), it's likely per package
        // If unit_price is low (typically < 1€), it's likely per piece
        // Threshold: if unit_price > 1€, assume it's per package
        if (unitPrice > 1.0) {
          // Check if total matches: quantity (packages) × unit_price (per package)
          const expectedTotalAsPackages = quantity * unitPrice
          const tolerance = 0.01
          if (Math.abs(totalPrice - expectedTotalAsPackages) < tolerance) {
            return false // Quantity is in packages
          }
        }
        
        // If unit_price is low (< 1€), check if it matches per piece calculation
        if (unitPricePerPiece < 1.0) {
          const expectedTotalAsPieces = quantity * unitPrice
          const expectedTotalAsPackages = quantity * product.pieces_per_package * unitPricePerPiece
          const tolerance = 0.01
          
          // If total matches packages calculation better, it's packages
          if (Math.abs(totalPrice - expectedTotalAsPackages) < Math.abs(totalPrice - expectedTotalAsPieces)) {
            return false // Quantity is in packages
          }
        }
      }
      
      // Default: if product is not sold by package, quantity is in pieces
      if (product && !product.sold_by_package) {
        return true
      }
      
      // If product is sold by package and unit_type is null, default to packages (not pieces)
      // because invoices typically store quantity in packages for products sold by package
      if (product && product.sold_by_package) {
        return false
      }
      
      // Final fallback: default to pieces
      return true
    },
    // Helper method to determine if item was received in kg
    // First check unit_type, then check stock receipt items if available
    isItemInKg(item) {
      if (!this.isKeseMbeturinash({ name: item.product_name })) return false
      
      // If unit_type is explicitly set, use it
      if (item.unit_type === 'kg') return true
      if (item.unit_type === 'package') return false
      
      // If unit_type is null/undefined, try to get it from stock receipt items
      if (this.selectedInvoice && this.selectedInvoice.stock_receipt && this.selectedInvoice.stock_receipt.items) {
        const receiptItem = this.selectedInvoice.stock_receipt.items.find(
          ri => ri.product_id === item.product_id
        )
        if (receiptItem && receiptItem.unit_type === 'kg') return true
        if (receiptItem && receiptItem.unit_type === 'package') return false
      }
      
      // Fallback: try to infer from quantity (if quantity has decimal places, it's likely kg)
      const quantity = parseFloat(item.quantity)
      return quantity % 1 !== 0 // Has decimal places
    },
    async viewInvoice(invoice) {
      try {
        // Load full invoice details with items
        const response = await axios.get(`/api/supplier-invoices/${invoice.id}`)
        this.selectedInvoice = response.data.data
        // Debug: log unit_type for each item
        console.log('Invoice items with unit_type:', this.selectedInvoice.items.map(item => ({
          product_name: item.product_name,
          unit_type: item.unit_type,
          quantity: item.quantity
        })))
        this.showInvoiceViewModal = true
      } catch (error) {
        console.error('Error loading invoice details:', error)
        // Fallback to basic invoice data
        this.selectedInvoice = invoice
        this.showInvoiceViewModal = true
      }
    },
    closeInvoiceViewModal() {
      this.showInvoiceViewModal = false
      this.selectedInvoice = null
    },
    async editInvoice(invoice) {
      try {
        console.log('Editing invoice:', invoice.id)
        // Load full invoice details with items
        const response = await axios.get(`/api/supplier-invoices/${invoice.id}`)
        console.log('Invoice response:', response.data)
        const fullInvoice = response.data.data
        
        if (!fullInvoice) {
          alert('Fatura nuk u gjet')
          return
        }
        
        // Use allProducts instead of individual API calls
        const itemsWithProducts = (fullInvoice.items || []).map(item => {
          if (item.product_id) {
            // Find product from allProducts
            const product = this.allProducts.find(p => p.id == item.product_id)
            return {
              ...item,
              product: product || null,
              quantity: parseFloat(item.quantity || 0), // Ensure quantity is a number
              unit_price: parseFloat(item.unit_price || 0), // Ensure unit_price is a number
              total_price: parseFloat(item.total_price || 0) // Ensure total_price is a number
            }
          }
          return {
            ...item,
            quantity: parseFloat(item.quantity || 0),
            unit_price: parseFloat(item.unit_price || 0),
            total_price: parseFloat(item.total_price || 0)
          }
        })
        
        // Ensure unit_type is preserved for each item
        const itemsWithUnitType = itemsWithProducts.map(item => ({
          ...item,
          unit_type: item.unit_type || null // Preserve unit_type from invoice item
        }))
        
        this.editingInvoice = {
          ...fullInvoice,
          items: itemsWithUnitType,
          invoice_date: fullInvoice.invoice_date ? new Date(fullInvoice.invoice_date).toISOString().split('T')[0] : '',
          due_date: fullInvoice.due_date ? new Date(fullInvoice.due_date).toISOString().split('T')[0] : '',
          paid_date: fullInvoice.paid_date ? new Date(fullInvoice.paid_date).toISOString().split('T')[0] : ''
        }
        
        console.log('Editing invoice data:', this.editingInvoice)
        this.showInvoiceEditModal = true
      } catch (error) {
        console.error('Error loading invoice details:', error)
        console.error('Error response:', error.response)
        const errorMessage = error.response?.data?.message || error.message || 'Gabim në ngarkimin e detajeve të faturës'
        alert(errorMessage)
      }
    },
    closeInvoiceEditModal() {
      this.showInvoiceEditModal = false
      this.editingInvoice = null
    },
    addInvoiceItem() {
      if (!this.editingInvoice.items) {
        this.editingInvoice.items = []
      }
      this.editingInvoice.items.push({
        product_id: '',
        product_name: '',
        quantity: 1,
        unit_price: 0,
        total_price: 0,
        unit_type: null, // Will be set when product is selected
        product: null
      })
    },
    removeInvoiceItem(index) {
      this.editingInvoice.items.splice(index, 1)
      this.calculateInvoiceTotals()
    },
    onInvoiceProductChange(item, index) {
      const product = this.allProducts.find(p => p.id == item.product_id)
      if (product) {
        item.product = product
        item.product_name = product.name
        
        // Set unit_type based on product type (preserve existing if already set)
        if (!item.unit_type) {
          if (this.isKeseMbeturinash(product)) {
            // Check if this invoice item was originally in kg by checking stock receipt
            if (this.editingInvoice && this.editingInvoice.stock_receipt && this.editingInvoice.stock_receipt.items) {
              const receiptItem = this.editingInvoice.stock_receipt && this.editingInvoice.stock_receipt.items 
                ? this.editingInvoice.stock_receipt.items.find(
                    ri => ri.product_id === (product && product.id ? product.id : null)
                  )
                : null
              item.unit_type = receiptItem && receiptItem.unit_type === 'kg' ? 'kg' : 'package'
            } else {
              item.unit_type = 'package' // Default for Kese Mbeturinash
            }
          } else {
            item.unit_type = null
          }
        }
        
        if (item.unit_price === 0 || !item.unit_price) {
          // Try to get price from existing invoice items or use cost_price
          const existingItem = this.editingInvoice.items.find(i => i.product_id === (product && product.id ? product.id : null) && i.unit_price > 0)
          item.unit_price = existingItem ? existingItem.unit_price : (product.cost_price || 0)
        }
        this.calculateInvoiceItemTotal(item, index)
      }
    },
    calculateInvoiceItemTotal(item, index) {
      item.total_price = (item.quantity || 0) * (item.unit_price || 0)
      this.calculateInvoiceTotals()
    },
    calculateInvoiceTotals() {
      const subtotal = (this.editingInvoice.items || []).reduce((sum, item) => sum + (item.total_price || 0), 0)
      this.editingInvoice.subtotal = subtotal
      
      if (this.editingInvoice.has_vat) {
        const vatRate = this.editingInvoice.vat_rate || 18.00
        this.editingInvoice.vat_amount = (subtotal * vatRate) / 100
        this.editingInvoice.total_amount = subtotal + this.editingInvoice.vat_amount
      } else {
        this.editingInvoice.vat_amount = 0
        this.editingInvoice.total_amount = subtotal
      }
    },
    async saveEditedInvoice() {
      this.savingInvoice = true
      try {
        // Recalculate totals
        this.calculateInvoiceTotals()
        
        // Prepare items for API (include id if it's an existing item)
        const items = (this.editingInvoice.items || []).map(item => ({
          id: item.id || null, // Include id for existing items
          product_id: item.product_id,
          product_name: item.product_name,
          quantity: item.quantity,
          unit_price: item.unit_price,
          total_price: item.total_price,
          unit_type: item.unit_type || null, // Preserve unit_type
          notes: item.notes || null
        }))
        
        // Set paid_date if status is paid
        if (this.editingInvoice.status === 'paid' && !this.editingInvoice.paid_date) {
          this.editingInvoice.paid_date = new Date().toISOString().split('T')[0]
        } else if (this.editingInvoice.status !== 'paid') {
          this.editingInvoice.paid_date = null
        }
        
        const payload = {
          invoice_date: this.editingInvoice.invoice_date,
          due_date: this.editingInvoice.due_date || null,
          status: this.editingInvoice.status,
          paid_date: this.editingInvoice.paid_date || null,
          has_vat: this.editingInvoice.has_vat || false,
          vat_rate: this.editingInvoice.has_vat ? (this.editingInvoice.vat_rate || 18.00) : 0,
          vat_amount: this.editingInvoice.vat_amount || 0,
          subtotal: this.editingInvoice.subtotal,
          total_amount: this.editingInvoice.total_amount,
          notes: this.editingInvoice.notes || null,
          items: items
        }
        
        await axios.put(`/api/supplier-invoices/${this.editingInvoice.id}`, payload)
        
        alert('Fatura u përditësua me sukses!')
        this.closeInvoiceEditModal()
        await this.loadInvoices()
      } catch (error) {
        console.error('Error saving invoice:', error)
        alert('Gabim në ruajtjen e faturës: ' + (error.response?.data?.message || error.message))
      } finally {
        this.savingInvoice = false
      }
    },
    async deleteInvoice(invoice) {
      if (!invoice || !invoice.id) {
        console.error('Invalid invoice object:', invoice)
        alert('Gabim: Fatura nuk është e vlefshme')
        return
      }

      const invoiceNumber = invoice.invoice_number || invoice.id
      if (!confirm(`A jeni të sigurt që dëshironi të fshini faturën ${invoiceNumber}?`)) {
        return
      }

      try {
        // Ensure authentication token is set
        const token = localStorage.getItem('admin_token')
        if (!token) {
          alert('Ju nuk jeni të autentifikuar. Ju lutem identifikohuni përsëri.')
          this.$router.push('/admin/login')
          return
        }

        console.log('Deleting invoice:', invoice.id, 'Status:', invoice.status)
        const response = await axios.delete(`/api/supplier-invoices/${invoice.id}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        })
        console.log('Delete response:', response.data)
        
        if (response.data && response.data.success) {
          alert('Fatura u fshi me sukses!')
          
          // Close modal if open
          if (this.showInvoiceViewModal && this.selectedInvoice?.id === invoice.id) {
            this.closeInvoiceViewModal()
          }
          
          // Reload invoices
          await this.loadInvoices()
        } else {
          throw new Error(response.data?.message || 'Fshirja dështoi')
        }
      } catch (error) {
        console.error('Error deleting invoice:', error)
        console.error('Error response:', error.response)
        let errorMessage = 'Gabim në fshirjen e faturës'
        
        if (error.response) {
          if (error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message
          } else if (error.response.status === 401) {
            errorMessage = 'Ju nuk jeni të autentifikuar. Ju lutem identifikohuni përsëri.'
            this.$router.push('/admin/login')
          } else if (error.response.status === 404) {
            errorMessage = 'Fatura nuk u gjet'
          } else if (error.response.status === 422) {
            errorMessage = error.response.data.message || 'Fatura nuk mund të fshihet (mund të jetë e paguar)'
          } else if (error.response.status === 403) {
            errorMessage = 'Nuk keni leje për të fshirë këtë faturë'
          }
        } else if (error.message) {
          errorMessage = error.message
        }
        
        alert(errorMessage)
      }
    },
    async printInvoice(invoice) {
      try {
        // Load full invoice details if not already loaded
        let fullInvoice = invoice
        if (!invoice.items || invoice.items.length === 0) {
          const response = await axios.get(`/api/supplier-invoices/${invoice.id}`)
          fullInvoice = response.data.data
        }

        const printWindow = window.open('', '_blank', 'width=900,height=650')
        if (!printWindow) {
          alert('Ju lutem lejoni popup-et për të printuar faturën')
          return
        }

        const invoiceHtml = this.generateInvoiceHtml(fullInvoice)
        printWindow.document.write(invoiceHtml)
        printWindow.document.close()
        
        // Wait a bit for content to load, then print
        setTimeout(() => {
          printWindow.print()
        }, 250)
      } catch (error) {
        console.error('Error printing invoice:', error)
        alert('Gabim në printimin e faturës')
      }
    },
    generateInvoiceHtml(invoice) {
      const invoiceDate = this.formatDate(invoice.invoice_date)
      const dueDate = invoice.due_date ? this.formatDate(invoice.due_date) : null
      const paidDate = invoice.paid_date ? this.formatDate(invoice.paid_date) : null
      const isPaid = invoice.status === 'paid'
      const paymentStatus = isPaid ? 'E PAGUAR' : 'JO E PAGUAR'
      const paymentStatusClass = isPaid ? 'paid' : 'unpaid'

      const itemsRows = (invoice.items || [])
        .map(item => {
          const product = this.allProducts.find(p => p.id == item.product_id) || item.product
          const productName = item.product_name || (product ? product.name : '')
          const isKeseMbeturinash = productName && productName.toLowerCase().includes('kese mbeturinash')
          
          let packagingInfo = ''
          let quantityDisplay = item.quantity || 0
          let quantitySuffix = ''
          
          // Check unit_type to determine how to display
          if (isKeseMbeturinash && item.unit_type === 'kg') {
            // For "Kese Mbeturinash" with kg, show quantity in kg (NO cp info)
            quantityDisplay = parseFloat(item.quantity || 0).toFixed(2)
            packagingInfo = ` (në kg)`
            quantitySuffix = ' kg'
          } else if (product && product.sold_by_package && product.pieces_per_package) {
            // For products sold by package (including "Kese Mbeturinash" with package unit)
            packagingInfo = ` (${item.quantity} kompleti × ${product.pieces_per_package}cp = ${item.quantity * product.pieces_per_package} copa)`
            quantitySuffix = `<br><span style="font-size: 11px; color: #6b7280;">= ${item.quantity * (product.pieces_per_package || 1)} copa</span>`
          }
          
          return `
          <tr>
            <td>${item.product_name || '-'}${packagingInfo ? '<br><span style="font-size: 11px; color: #6b7280;">' + packagingInfo + '</span>' : ''}</td>
            <td style="text-align: center;">${quantityDisplay}${quantitySuffix}</td>
            <td style="text-align: right;">${this.formatPrice(item.unit_price)}</td>
            <td style="text-align: right; font-weight: bold;">${this.formatPrice(item.total_price)}</td>
          </tr>
        `
        })
        .join('')

      const scriptTag = '<' + 'script' + '>'
      const scriptClose = '</' + 'script' + '>'

      return '<html>' +
        '<head>' +
        '<title>Faturë Prodhuesi ' + (invoice.invoice_number || 'N/A') + '</title>' +
        '<style>' +
        '@media print { button { display: none; } }' +
        'body { font-family: Arial, sans-serif; padding: 24px; color: #111827; max-width: 800px; margin: 0 auto; }' +
        'h1 { font-size: 24px; margin-bottom: 8px; text-align: center; }' +
        '.meta { margin-bottom: 16px; font-size: 14px; }' +
        '.payment-status { margin-top: 12px; padding: 8px 12px; border-radius: 4px; display: inline-block; font-size: 14px; }' +
        '.payment-status.paid { background-color: #d1fae5; color: #059669; font-weight: bold; }' +
        '.payment-status.unpaid { background-color: #fee2e2; color: #dc2626; font-weight: bold; }' +
        'table { width: 100%; border-collapse: collapse; margin-top: 16px; }' +
        'th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; font-size: 14px; }' +
        'th { background-color: #f9fafb; font-weight: bold; }' +
        '.totals { margin-top: 16px; text-align: right; }' +
        '.totals p { margin: 4px 0; }' +
        '.signatures { display: flex; justify-content: space-between; margin-top: 60px; }' +
        '.signature-box { width: 45%; text-align: center; }' +
        '.signature-line { border-top: 1px solid #111827; padding-top: 4px; margin-bottom: 40px; display: inline-block; min-width: 200px; }' +
        '.footer { text-align: center; margin-top: 24px; font-size: 12px; color: #6b7280; }' +
        '</style>' +
        '</head>' +
        '<body>' +
        '<h1>FATURA E PRODHUESIT</h1>' +
        '<div class="meta">' +
        '<p><strong>Nr. Faturës:</strong> ' + (invoice.invoice_number || 'N/A') + '</p>' +
        '<p><strong>Data e Faturës:</strong> ' + invoiceDate + '</p>' +
        '<p><strong>Prodhuesi:</strong> ' + (invoice.supplier?.name || 'N/A') + '</p>' +
        (invoice.supplier?.contact_person ? '<p><strong>Kontakti:</strong> ' + invoice.supplier.contact_person + '</p>' : '') +
        (invoice.supplier?.phone ? '<p><strong>Telefon:</strong> ' + invoice.supplier.phone + '</p>' : '') +
        (invoice.supplier?.email ? '<p><strong>Email:</strong> ' + invoice.supplier.email + '</p>' : '') +
        (dueDate ? '<p><strong>Data e Maturimit:</strong> ' + dueDate + '</p>' : '') +
        (invoice.stock_receipt_id ? '<p><strong>Nr. Pranimit:</strong> ' + (invoice.stock_receipt?.receipt_number || invoice.stock_receipt_id) + '</p>' : '') +
        (isPaid ? '<div class="payment-status paid">' +
        '<strong>Statusi i Pagesës:</strong> ' + paymentStatus +
        (paidDate ? '<br><span style="font-size: 12px;">E paguar më: ' + paidDate + '</span>' : '') +
        '</div>' : '<div class="payment-status unpaid"><strong>Statusi i Pagesës:</strong> ' + paymentStatus + '</div>') +
        '</div>' +
        '<table>' +
        '<thead>' +
        '<tr><th>Produkti</th><th style="text-align: center;">Sasia</th><th style="text-align: right;">Çmimi Njësi</th><th style="text-align: right;">Totali</th></tr>' +
        '</thead>' +
        '<tbody>' +
        itemsRows +
        '</tbody>' +
        '</table>' +
        '<div class="totals">' +
        '<p>Nëntotali: ' + this.formatPrice(invoice.subtotal) + '</p>' +
        (invoice.has_vat ? '<p>TVSH (' + invoice.vat_rate + '%): ' + this.formatPrice(invoice.vat_amount) + '</p>' : '') +
        '<p style="font-size: 1.2em; border-top: 1px solid #e5e7eb; padding-top: 8px; margin-top: 8px;">Totali: ' + this.formatPrice(invoice.total_amount) + '</p>' +
        '</div>' +
        (invoice.notes ? '<div style="margin-top: 16px; padding: 12px; background-color: #f9fafb; border-radius: 4px;"><p><strong>Shënime:</strong> ' + invoice.notes + '</p></div>' : '') +
        '<div class="signatures">' +
        '<div class="signature-box">' +
        '<p class="signature-line"><strong>Nënshkrimi i Prodhuesit</strong></p>' +
        '<p style="font-size: 12px; color: #6b7280;">' + (invoice.supplier?.name || '') + '</p>' +
        '</div>' +
        '<div class="signature-box">' +
        '<p class="signature-line"><strong>Nënshkrimi i Blerësit</strong></p>' +
        '<p style="font-size: 12px; color: #6b7280;">GastroTrade</p>' +
        '</div>' +
        '</div>' +
        '<div class="footer">' +
        '<p><strong>Data e lëshimit:</strong> ' + invoiceDate + '</p>' +
        '</div>' +
        '<div style="margin-top: 24px; padding: 16px; border-top: 2px solid #e5e7eb; text-align: center;">' +
        '<h3 style="margin-bottom: 12px; font-size: 16px;">Ndaj Faturën:</h3>' +
        '<div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">' +
        '<button onclick="shareToViber()" style="padding: 8px 16px; background-color: #7360F2; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">💬 Viber</button>' +
        '<button onclick="shareToWhatsApp()" style="padding: 8px 16px; background-color: #25D366; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">📱 WhatsApp</button>' +
        '<button onclick="shareToGmail()" style="padding: 8px 16px; background-color: #EA4335; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">📧 Gmail</button>' +
        '<button onclick="window.print()" style="padding: 8px 16px; background-color: #6B7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">🖨 Printo</button>' +
        '</div>' +
        '</div>' +
        scriptTag +
        'const invoiceData = ' + JSON.stringify({
          invoice_number: invoice.invoice_number,
          supplier_name: invoice.supplier?.name,
          supplier_contact: invoice.supplier?.contact_person || '',
          supplier_phone: invoice.supplier?.phone || '',
          supplier_email: invoice.supplier?.email || '',
          total_amount: invoice.total_amount,
          subtotal: invoice.subtotal,
          vat_amount: invoice.vat_amount || 0,
          vat_rate: invoice.vat_rate || 0,
          has_vat: invoice.has_vat || false,
          invoice_date: invoiceDate,
          due_date: dueDate || '',
          status: invoice.status,
          paid_date: paidDate || '',
          items: (invoice.items || []).map(item => ({
            name: item.product_name,
            quantity: item.quantity,
            unit_price: item.unit_price,
            total_price: item.total_price
          }))
        }) + ';' +
        'function generateInvoiceText() {' +
        'let text = "📦 Faturë Prodhuesi: " + invoiceData.invoice_number + "\\n\\n";' +
        'text += "Prodhuesi: " + invoiceData.supplier_name + "\\n";' +
        'if (invoiceData.supplier_contact) text += "Kontakti: " + invoiceData.supplier_contact + "\\n";' +
        'if (invoiceData.supplier_phone) text += "Telefon: " + invoiceData.supplier_phone + "\\n";' +
        'if (invoiceData.supplier_email) text += "Email: " + invoiceData.supplier_email + "\\n";' +
        'text += "Data: " + invoiceData.invoice_date + "\\n";' +
        'if (invoiceData.due_date) text += "Data e Maturimit: " + invoiceData.due_date + "\\n";' +
        'text += "\\nProduktet:\\n";' +
        'invoiceData.items.forEach(function(item) {' +
        'text += "• " + item.name + " - " + item.quantity + " x " + item.unit_price.toFixed(2) + " € = " + item.total_price.toFixed(2) + " €\\n";' +
        '});' +
        'text += "\\nNëntotali: " + invoiceData.subtotal.toFixed(2) + " €\\n";' +
        'if (invoiceData.has_vat) {' +
        'text += "TVSH (" + invoiceData.vat_rate + "%): " + invoiceData.vat_amount.toFixed(2) + " €\\n";' +
        '}' +
        'text += "Totali: " + invoiceData.total_amount.toFixed(2) + " €\\n";' +
        'text += "\\nStatusi: " + (invoiceData.status === "paid" ? "E PAGUAR" : "JO E PAGUAR");' +
        'if (invoiceData.paid_date) text += " (E paguar më: " + invoiceData.paid_date + ")";' +
        'return text;' +
        '}' +
        'function shareToViber() {' +
        'const invoiceText = generateInvoiceText();' +
        'if (navigator.share) {' +
        'navigator.share({' +
        'title: "Faturë Prodhuesi " + invoiceData.invoice_number,' +
        'text: invoiceText' +
        '}).catch(function(err) {' +
        'console.log("Error sharing:", err);' +
        'fallbackShareViber(invoiceText);' +
        '});' +
        '} else {' +
        'fallbackShareViber(invoiceText);' +
        '}' +
        '}' +
        'function fallbackShareViber(text) {' +
        'if (navigator.clipboard) {' +
        'navigator.clipboard.writeText(text).then(function() {' +
        'alert("Teksti u kopjua në clipboard. Mund ta ngjitni në Viber.");' +
        '}).catch(function(err) {' +
        'console.error("Failed to copy:", err);' +
        'prompt("Kopjoni këtë tekst dhe ngjisni në Viber:", text);' +
        '});' +
        '} else {' +
        'prompt("Kopjoni këtë tekst dhe ngjisni në Viber:", text);' +
        '}' +
        '}' +
        'function shareToWhatsApp() {' +
        'const invoiceText = generateInvoiceText();' +
        'const whatsappUrl = "https://wa.me/?text=" + encodeURIComponent(invoiceText);' +
        'window.open(whatsappUrl, "_blank");' +
        '}' +
        'function shareToGmail() {' +
        'const subject = encodeURIComponent("Faturë Prodhuesi " + invoiceData.invoice_number);' +
        'const body = encodeURIComponent(generateInvoiceText());' +
        'const gmailUrl = "mailto:svalon95@gmail.com?subject=" + subject + "&body=" + body;' +
        'window.location.href = gmailUrl;' +
        '}' +
        scriptClose +
        '</body>' +
        '</html>'
    },
    startEdit(product, field) {
      if (!product || !product.id) {
        console.error('Invalid product in startEdit:', product)
        return
      }
      this.editingProductId = product.id
      this.editingField = field
      this.editingProduct = {
        cost_price: product.cost_price || 0,
        price: product.price || 0
      }
    },
    cancelEdit() {
      this.editingProductId = null
      this.editingField = null
      this.editingProduct = {}
    },
    async saveProductPrice(product, field) {
      try {
        const value = this.editingProduct[field]
        const payload = {}
        payload[field] = value || null
        
        if (!product || !product.id) {
          console.error('Invalid product in saveProductPrice:', product)
          alert('Gabim: Produkti nuk është i vlefshëm')
          return
        }
        await axios.put(`/api/admin/products/${product.id}`, payload)
        
        // Update local product data
        product[field] = value || null
        
        // Reload stock to get updated profit calculations
        await this.loadStock()
        
        this.cancelEdit()
      } catch (error) {
        console.error('Error saving product price:', error)
        alert('Gabim në ruajtjen e çmimit: ' + (error.response?.data?.message || error.message))
      }
    }
  }
}
</script>

<style scoped>
.btn-primary {
  @apply bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors duration-200 font-medium;
}

.btn-secondary {
  @apply bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-medium;
}
</style>

