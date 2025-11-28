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
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stoku</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Çmimi Blerje</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Çmimi Shitje</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fitimi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vlera Stok</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statusi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in filteredProducts" :key="product.id">
                <td class="px-4 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                  <div class="text-xs text-gray-500">{{ product.category?.name }}</div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ product.supplier?.name || '-' }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <span 
                      :class="[
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        product.stock_quantity <= 0 ? 'bg-red-100 text-red-800' :
                        product.stock_quantity <= product.min_stock_level ? 'bg-yellow-100 text-yellow-800' :
                        'bg-green-100 text-green-800'
                      ]"
                    >
                      {{ product.stock_quantity }}
                    </span>
                    <button 
                      @click.stop="openStockAdjustmentModal(product)"
                      type="button"
                      class="text-primary-600 hover:text-primary-800 hover:bg-primary-50 p-1 rounded transition-colors cursor-pointer"
                      title="Rregullo Stokun"
                    >
                      ✏️
                    </button>
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    Min: {{ product.min_stock_level }}
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ product.cost_price ? formatPrice(product.cost_price) : '-' }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ product.price ? formatPrice(product.price) : 'Sipas kërkesës' }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm">
                  <div v-if="product.profit_margin !== null" class="text-green-600 font-semibold">
                    {{ formatPrice(product.profit_margin) }}
                  </div>
                  <div v-if="product.profit_percentage !== null" class="text-xs text-gray-500">
                    ({{ product.profit_percentage.toFixed(1) }}%)
                  </div>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatPrice(product.total_stock_value || 0) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span 
                    :class="[
                      'px-2 py-1 text-xs font-semibold rounded-full',
                      product.stock_quantity <= 0 ? 'bg-red-100 text-red-800' :
                      product.stock_quantity <= product.min_stock_level ? 'bg-yellow-100 text-yellow-800' :
                      'bg-green-100 text-green-800'
                    ]"
                  >
                    {{ product.stock_quantity <= 0 ? 'Pa Stok' :
                       product.stock_quantity <= product.min_stock_level ? 'Stok i Ulët' :
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
          v-for="product in filteredProducts" 
          :key="product.id"
          class="bg-white rounded-lg shadow-lg p-4 border border-gray-200"
        >
          <div class="flex justify-between items-start mb-3">
            <div class="flex-1">
              <h3 class="text-base font-semibold text-gray-900">{{ product.name }}</h3>
              <p class="text-xs text-gray-500 mt-1">{{ product.category?.name }}</p>
            </div>
            <button 
              @click.stop="openStockAdjustmentModal(product)"
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
              <p class="text-sm font-medium text-gray-900">{{ product.supplier?.name || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Statusi</p>
              <span 
                :class="[
                  'inline-block px-2 py-1 text-xs font-semibold rounded-full',
                  product.stock_quantity <= 0 ? 'bg-red-100 text-red-800' :
                  product.stock_quantity <= product.min_stock_level ? 'bg-yellow-100 text-yellow-800' :
                  'bg-green-100 text-green-800'
                ]"
              >
                {{ product.stock_quantity <= 0 ? 'Pa Stok' :
                   product.stock_quantity <= product.min_stock_level ? 'Stok i Ulët' :
                   'Në Stok' }}
              </span>
            </div>
          </div>

          <div class="border-t border-gray-200 pt-3 mb-3">
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs text-gray-500">Stoku</span>
              <div class="flex items-center gap-2">
                <span 
                  :class="[
                    'px-2 py-1 text-xs font-semibold rounded-full',
                    product.stock_quantity <= 0 ? 'bg-red-100 text-red-800' :
                    product.stock_quantity <= product.min_stock_level ? 'bg-yellow-100 text-yellow-800' :
                    'bg-green-100 text-green-800'
                  ]"
                >
                  {{ product.stock_quantity }}
                </span>
                <span class="text-xs text-gray-500">Min: {{ product.min_stock_level }}</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-xs text-gray-500 mb-1">Çmimi Blerje</p>
              <p class="font-medium text-gray-900">{{ product.cost_price ? formatPrice(product.cost_price) : '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Çmimi Shitje</p>
              <p class="font-medium text-gray-900">{{ product.price ? formatPrice(product.price) : 'Sipas kërkesës' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Fitimi</p>
              <div v-if="product.profit_margin !== null">
                <p class="font-semibold text-green-600">{{ formatPrice(product.profit_margin) }}</p>
                <p v-if="product.profit_percentage !== null" class="text-xs text-gray-500">
                  ({{ product.profit_percentage.toFixed(1) }}%)
                </p>
              </div>
              <p v-else class="text-gray-400">-</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Vlera Stok</p>
              <p class="font-medium text-gray-900">{{ formatPrice(product.total_stock_value || 0) }}</p>
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
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                {{ supplier.name }}
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
                <tr v-for="invoice in invoices" :key="invoice.id">
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
                      @click="printInvoice(invoice)"
                      class="text-blue-600 hover:text-blue-800 mr-3"
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
            v-for="invoice in invoices" 
            :key="invoice.id"
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
                @click="printInvoice(invoice)"
                class="flex-1 px-3 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700"
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
                      :key="index"
                      class="border border-gray-200 rounded-lg p-4 bg-white"
                    >
                      <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-4">
                          <label class="block text-xs font-medium text-gray-700 mb-1">Produkti *</label>
                          <select 
                            v-model="item.product_id"
                            @change="onProductChange(item)"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary-500"
                          >
                            <option value="">Zgjidhni produktin</option>
                            <option v-for="product in allProducts" :key="product.id" :value="product.id">
                              {{ product.name }}
                            </option>
                          </select>
                        </div>
                        <div class="md:col-span-2">
                          <label class="block text-xs font-medium text-gray-700 mb-1">Sasia *</label>
                          <input 
                            v-model.number="item.quantity"
                            @input="calculateItemTotal(item)"
                            type="number"
                            min="1"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                          >
                        </div>
                        <div class="md:col-span-2">
                          <label class="block text-xs font-medium text-gray-700 mb-1">Çmimi Blerje *</label>
                          <input 
                            v-model.number="item.unit_cost"
                            @input="calculateItemTotal(item)"
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
                            :value="formatPrice(item.total_cost || 0)"
                            type="text"
                            readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50"
                          >
                        </div>
                        <div class="md:col-span-2 flex gap-2">
                          <button 
                            type="button"
                            @click="removeReceiptItem(index)"
                            class="px-3 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 w-full"
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
                  <div class="flex justify-between items-center mb-2">
                    <span class="font-semibold text-gray-900">Total Produkte:</span>
                    <span class="font-bold text-gray-900">{{ calculateTotalItems() }}</span>
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
        <div class="relative top-0 sm:top-4 mx-auto p-4 sm:p-5 border w-full sm:w-96 max-w-md shadow-lg rounded-md bg-white m-2 sm:m-4">
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
                <input 
                  v-model.number="stockAdjustmentForm.quantity"
                  type="number"
                  min="0"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md"
                >
                <p v-if="adjustingProduct && stockAdjustmentForm.adjustment_type === 'set'" class="text-xs text-gray-500 mt-1">
                  Stoku do të bëhet: {{ stockAdjustmentForm.quantity || 0 }}
                </p>
                <p v-else-if="adjustingProduct && stockAdjustmentForm.adjustment_type === 'increase'" class="text-xs text-gray-500 mt-1">
                  Stoku do të bëhet: {{ calculateNewStock('increase') }}
                </p>
                <p v-else-if="adjustingProduct && stockAdjustmentForm.adjustment_type === 'decrease'" class="text-xs text-gray-500 mt-1">
                  Stoku do të bëhet: {{ calculateNewStock('decrease') }}
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
        <div class="relative top-0 sm:top-4 mx-auto max-w-4xl w-full p-3 sm:p-6">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 border-b border-gray-200 gap-3">
              <div>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Detajet e Faturës</h3>
                <p class="text-sm text-gray-500 mt-1">{{ selectedInvoice.invoice_number }}</p>
              </div>
              <button @click="closeInvoiceViewModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none self-start sm:self-auto">
                &times;
              </button>
            </div>
            <div class="px-4 sm:px-6 py-4 sm:py-5 max-h-[90vh] sm:max-h-[80vh] overflow-y-auto">
              <div class="space-y-6">
                <!-- Invoice Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm text-gray-500 mb-1">Prodhuesi</p>
                    <p class="text-lg font-semibold text-gray-900">{{ selectedInvoice.supplier?.name || '-' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500 mb-1">Data e Faturës</p>
                    <p class="text-lg font-semibold text-gray-900">{{ formatDate(selectedInvoice.invoice_date) }}</p>
                  </div>
                  <div v-if="selectedInvoice.due_date">
                    <p class="text-sm text-gray-500 mb-1">Data e Maturimit</p>
                    <p class="text-lg font-semibold text-gray-900">{{ formatDate(selectedInvoice.due_date) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500 mb-1">Statusi</p>
                    <span 
                      :class="[
                        'inline-block px-3 py-1 text-sm font-semibold rounded-full',
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
                    <p class="text-sm text-gray-500 mb-1">Data e Pagesës</p>
                    <p class="text-lg font-semibold text-green-600">{{ formatDate(selectedInvoice.paid_date) }}</p>
                  </div>
                </div>

                <!-- Invoice Items -->
                <div class="border-t pt-4">
                  <h4 class="font-semibold text-gray-900 mb-3">Produktet</h4>
                  <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produkti</th>
                          <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sasia</th>
                          <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Çmimi Njësi</th>
                          <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Totali</th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in selectedInvoice.items" :key="item.id">
                          <td class="px-3 py-2 text-sm text-gray-900">{{ item.product_name }}</td>
                          <td class="px-3 py-2 text-sm text-gray-500">{{ item.quantity }}</td>
                          <td class="px-3 py-2 text-sm text-gray-500">{{ formatPrice(item.unit_price) }}</td>
                          <td class="px-3 py-2 text-sm font-semibold text-gray-900 text-right">{{ formatPrice(item.total_price) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Invoice Summary -->
                <div class="border-t pt-4">
                  <div class="space-y-2">
                    <div class="flex justify-between items-center">
                      <span class="text-sm font-medium text-gray-700">Nëntotali:</span>
                      <span class="text-sm font-semibold text-gray-900">{{ formatPrice(selectedInvoice.subtotal) }}</span>
                    </div>
                    <div v-if="selectedInvoice.has_vat" class="flex justify-between items-center">
                      <span class="text-sm font-medium text-gray-700">TVSH ({{ selectedInvoice.vat_rate }}%):</span>
                      <span class="text-sm font-semibold text-gray-900">{{ formatPrice(selectedInvoice.vat_amount) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                      <span class="text-lg font-bold text-gray-900">Totali:</span>
                      <span class="text-lg font-bold text-primary-600">{{ formatPrice(selectedInvoice.total_amount) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Notes -->
                <div v-if="selectedInvoice.notes" class="border-t pt-4">
                  <p class="text-sm text-gray-500 mb-1">Shënime</p>
                  <p class="text-sm text-gray-900">{{ selectedInvoice.notes }}</p>
                </div>
              </div>
            </div>
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-end gap-3">
              <button 
                @click="closeInvoiceViewModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
              >
                Mbyll
              </button>
              <button 
                v-if="selectedInvoice && selectedInvoice.status !== 'paid'"
                @click.stop="deleteInvoice(selectedInvoice)"
                type="button"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
              >
                🗑️ Fshi Faturën
              </button>
              <button 
                @click="printInvoice(selectedInvoice)"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700"
              >
                🖨️ Printo Faturën
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

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
      savingReceipt: false,
      adjustingStock: false,
      adjustingProduct: null,
      selectedInvoice: null,
      stockAdjustmentForm: {
        adjustment_type: 'increase',
        quantity: 0,
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
      let filtered = this.products

      if (this.filters.search) {
        const search = this.filters.search.toLowerCase()
        filtered = filtered.filter(p => 
          p.name.toLowerCase().includes(search) ||
          (p.description && p.description.toLowerCase().includes(search))
        )
      }

      if (this.filters.supplier_id) {
        filtered = filtered.filter(p => p.supplier_id == this.filters.supplier_id)
      }

      if (this.filters.stockFilter === 'low_stock') {
        filtered = filtered.filter(p => p.stock_quantity <= p.min_stock_level && p.stock_quantity > 0)
      } else if (this.filters.stockFilter === 'out_of_stock') {
        filtered = filtered.filter(p => p.stock_quantity <= 0)
      }

      return filtered
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
  },
  methods: {
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
        this.products = response.data.data || []
      } catch (error) {
        console.error('Error loading stock:', error)
        alert('Gabim në ngarkimin e stokut')
      }
    },
    async loadSuppliers() {
      try {
        const response = await axios.get('/api/suppliers', { params: { active_only: true } })
        this.suppliers = response.data.data || []
      } catch (error) {
        console.error('Error loading suppliers:', error)
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
        this.allProducts = response.data.data || []
      } catch (error) {
        console.error('Error loading products:', error)
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
        quantity: 1,
        unit_cost: 0,
        total_cost: 0
      })
    },
    removeReceiptItem(index) {
      this.receiptForm.items.splice(index, 1)
    },
    onProductChange(item) {
      const product = this.allProducts.find(p => p.id == item.product_id)
      if (product && product.cost_price) {
        item.unit_cost = product.cost_price
        this.calculateItemTotal(item)
      }
    },
    calculateItemTotal(item) {
      item.total_cost = (item.quantity || 0) * (item.unit_cost || 0)
    },
    calculateTotalItems() {
      return this.receiptForm.items.reduce((sum, item) => sum + (item.quantity || 0), 0)
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
        const response = await axios.post('/api/stock-receipts', this.receiptForm)
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
      this.stockAdjustmentForm = {
        adjustment_type: 'increase',
        quantity: 0,
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
        notes: ''
      }
    },
    calculateNewStock(type) {
      if (!this.adjustingProduct) return 0
      const currentStock = this.adjustingProduct.stock_quantity || 0
      const quantity = this.stockAdjustmentForm.quantity || 0
      
      if (type === 'increase') {
        return currentStock + quantity
      } else if (type === 'decrease') {
        return Math.max(0, currentStock - quantity)
      }
      return currentStock
    },
    async saveStockAdjustment() {
      if (!this.adjustingProduct) return

      this.adjustingStock = true
      try {
        await axios.post(`/api/stock/adjust/${this.adjustingProduct.id}`, this.stockAdjustmentForm)
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
        this.invoices = response.data.data || []
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
    async viewInvoice(invoice) {
      try {
        // Load full invoice details with items
        const response = await axios.get(`/api/supplier-invoices/${invoice.id}`)
        this.selectedInvoice = response.data.data
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
        .map(item => `
          <tr>
            <td>${item.product_name || '-'}</td>
            <td style="text-align: center;">${item.quantity || 0}</td>
            <td style="text-align: right;">${this.formatPrice(item.unit_price)}</td>
            <td style="text-align: right; font-weight: bold;">${this.formatPrice(item.total_price)}</td>
          </tr>
        `)
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
          total_amount: invoice.total_amount,
          invoice_date: invoiceDate
        }) + ';' +
        'function shareToViber() {' +
        'const invoiceText = "📦 Faturë Prodhuesi " + invoiceData.invoice_number + "\\n\\nProdhuesi: " + invoiceData.supplier_name + "\\nData: " + invoiceData.invoice_date + "\\n\\nTotal: " + invoiceData.total_amount.toFixed(2) + " €";' +
        'const viberUrl = "viber://forward?text=" + encodeURIComponent(invoiceText);' +
        'window.open(viberUrl, "_blank");' +
        '}' +
        'function shareToWhatsApp() {' +
        'const invoiceText = "📦 Faturë Prodhuesi " + invoiceData.invoice_number + "\\n\\nProdhuesi: " + invoiceData.supplier_name + "\\nData: " + invoiceData.invoice_date + "\\n\\nTotal: " + invoiceData.total_amount.toFixed(2) + " €";' +
        'const whatsappUrl = "https://wa.me/?text=" + encodeURIComponent(invoiceText);' +
        'window.open(whatsappUrl, "_blank");' +
        '}' +
        'function shareToGmail() {' +
        'const subject = "Faturë Prodhuesi " + invoiceData.invoice_number;' +
        'const body = "Faturë Prodhuesi: " + invoiceData.invoice_number + "\\nProdhuesi: " + invoiceData.supplier_name + "\\nData: " + invoiceData.invoice_date + "\\nTotal: " + invoiceData.total_amount.toFixed(2) + " €";' +
        'const gmailUrl = "https://mail.google.com/mail/?view=cm&fs=1&to=&su=" + encodeURIComponent(subject) + "&body=" + encodeURIComponent(body);' +
        'window.open(gmailUrl, "_blank");' +
        '}' +
        scriptClose +
        '</body>' +
        '</html>'
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

