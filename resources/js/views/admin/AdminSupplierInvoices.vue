<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="flex-1">
          <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-4">Faturat e Prodhuesve</h1>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
          <button 
            @click="showCreateInvoiceModal = true"
            class="btn-primary w-full sm:w-auto"
          >
            + Faturë e Re
          </button>
          <router-link
            to="/admin/stock"
            class="btn-secondary text-center"
          >
            📦 Stoku
          </router-link>
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

      <!-- Payment Statistics -->
      <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-3 sm:p-5 border border-green-200 shadow-sm">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-green-800">Të Paguara</h3>
            <span class="text-xl sm:text-2xl">✅</span>
          </div>
          <p class="text-xl sm:text-3xl font-bold text-green-900">{{ paymentStats.paidCount }}</p>
          <p class="text-xs sm:text-sm text-green-700 mt-1">{{ formatPrice(paymentStats.paidTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-3 sm:p-5 border border-red-200 shadow-sm">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-red-800">Të Papaguara</h3>
            <span class="text-xl sm:text-2xl">⚠️</span>
          </div>
          <p class="text-xl sm:text-3xl font-bold text-red-900">{{ paymentStats.unpaidCount }}</p>
          <p class="text-xs sm:text-sm text-red-700 mt-1">{{ formatPrice(paymentStats.unpaidTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-3 sm:p-5 border border-blue-200 shadow-sm">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-blue-800">Total Faturat</h3>
            <span class="text-xl sm:text-2xl">📄</span>
          </div>
          <p class="text-xl sm:text-3xl font-bold text-blue-900">{{ paymentStats.totalCount }}</p>
          <p class="text-xs sm:text-sm text-blue-700 mt-1">{{ formatPrice(paymentStats.totalAmount) }}</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-3 sm:p-5 border border-yellow-200 shadow-sm">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-yellow-800">Vonuar</h3>
            <span class="text-xl sm:text-2xl">🔴</span>
          </div>
          <p class="text-xl sm:text-3xl font-bold text-yellow-900">{{ paymentStats.overdueCount }}</p>
          <p class="text-xs sm:text-sm text-yellow-700 mt-1">{{ formatPrice(paymentStats.overdueTotal) }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Prodhuesi</label>
          <select 
            v-model="filters.supplier_id"
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
            v-model="filters.status"
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
          <label class="block text-xs font-semibold text-gray-600 mb-1">Pagesa</label>
          <select 
            v-model="filters.paymentStatus"
            @change="loadInvoices"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
          >
            <option value="">Të gjitha</option>
            <option value="paid">E Paguar</option>
            <option value="unpaid">E Papaguar</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Nga Data</label>
          <input 
            v-model="filters.date_from"
            type="date"
            @change="loadInvoices"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
          >
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Deri në Data</label>
          <input 
            v-model="filters.date_to"
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
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pagesa</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Veprime</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="invoice in invoices" :key="invoice.id" 
                  :class="invoice.status === 'paid' ? 'bg-green-50' : invoice.status === 'overdue' ? 'bg-red-50' : ''">
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ invoice.invoice_number }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ invoice.supplier?.name }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(invoice.invoice_date) }}
                  <div v-if="invoice.due_date" class="text-xs text-gray-400">
                    Maturimi: {{ formatDate(invoice.due_date) }}
                  </div>
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
                <td class="px-4 py-4 whitespace-nowrap">
                  <div v-if="invoice.status === 'paid'" class="text-sm">
                    <span class="text-green-600 font-semibold">✅ E Paguar</span>
                    <div v-if="invoice.paid_date" class="text-xs text-gray-500 mt-1">
                      {{ formatDate(invoice.paid_date) }}
                    </div>
                  </div>
                  <div v-else class="text-sm">
                    <span class="text-red-600 font-semibold">❌ E Papaguar</span>
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm">
                  <button 
                    @click="viewInvoice(invoice)"
                    class="text-primary-600 hover:text-primary-800 mr-3"
                    title="Shiko Detajet"
                  >
                    👁️
                  </button>
                  <button 
                    @click="editInvoice(invoice)"
                    class="text-blue-600 hover:text-blue-800 mr-3"
                    title="Edito"
                  >
                    ✏️
                  </button>
                  <button 
                    @click="printInvoice(invoice)"
                    class="text-green-600 hover:text-green-800 mr-3"
                    title="Printo"
                  >
                    🖨️
                  </button>
                  <button 
                    v-if="invoice.status !== 'paid'"
                    @click="markAsPaid(invoice)"
                    class="text-green-600 hover:text-green-800 mr-3"
                    title="Shëno si të Paguar"
                  >
                    ✅
                  </button>
                  <button 
                    @click="deleteInvoice(invoice)"
                    class="text-red-600 hover:text-red-800"
                    title="Fshi Faturën"
                  >
                    🗑️
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
          :class="[
            'bg-white rounded-lg shadow-lg p-4 border-2',
            invoice.status === 'paid' ? 'border-green-200 bg-green-50' :
            invoice.status === 'overdue' ? 'border-red-200 bg-red-50' :
            'border-gray-200'
          ]"
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

          <div class="mb-3 p-2 rounded" 
               :class="invoice.status === 'paid' ? 'bg-green-100' : 'bg-red-100'">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold" 
                    :class="invoice.status === 'paid' ? 'text-green-800' : 'text-red-800'">
                {{ invoice.status === 'paid' ? '✅ E Paguar' : '❌ E Papaguar' }}
              </span>
              <span v-if="invoice.paid_date" class="text-xs text-gray-600">
                {{ formatDate(invoice.paid_date) }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
            <div>
              <p class="text-xs text-gray-500 mb-1">Data</p>
              <p class="font-medium text-gray-900">{{ formatDate(invoice.invoice_date) }}</p>
              <p v-if="invoice.due_date" class="text-xs text-gray-400 mt-1">
                Maturimi: {{ formatDate(invoice.due_date) }}
              </p>
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
              v-if="invoice.status !== 'paid'"
              @click="markAsPaid(invoice)"
              class="px-3 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700"
              title="Shëno si të Paguar"
            >
              ✅
            </button>
            <button 
              @click="deleteInvoice(invoice)"
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

      <!-- View Invoice Modal -->
      <div v-if="showViewModal && selectedInvoice" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-0 sm:top-4 mx-auto max-w-4xl w-full p-3 sm:p-6">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 border-b border-gray-200 gap-3">
              <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Detajet e Faturës</h3>
              <button @click="closeViewModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none self-start sm:self-auto">
                &times;
              </button>
            </div>
            <div class="px-4 sm:px-6 py-4 sm:py-5 max-h-[90vh] sm:max-h-[80vh] overflow-y-auto">
              <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm text-gray-500">Nr. Faturës</p>
                    <p class="text-lg font-semibold">{{ selectedInvoice.invoice_number }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Prodhuesi</p>
                    <p class="text-lg font-semibold">{{ selectedInvoice.supplier?.name }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Data</p>
                    <p class="text-lg font-semibold">{{ formatDate(selectedInvoice.invoice_date) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Statusi</p>
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
                </div>

                <div class="border-t pt-4">
                  <h4 class="font-semibold mb-3">Produktet</h4>
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Produkti</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Sasia</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Çmimi</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Totali</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="item in selectedInvoice.items" :key="item.id">
                        <td class="px-3 py-2 text-sm">
                          <div class="font-medium">{{ item.product_name }}</div>
                          <span v-if="getItemProduct(item) && getItemProduct(item).sold_by_package && getItemProduct(item).pieces_per_package && (!isKeseMbeturinash(getItemProduct(item)) || (isKeseMbeturinash(getItemProduct(item)) && item.unit_type === 'package'))" class="text-xs text-gray-500 mt-1 block">
                            ({{ getItemProduct(item).pieces_per_package }}cp/kompleti)
                          </span>
                          <span v-else-if="isKeseMbeturinash(getItemProduct(item)) && item.unit_type === 'kg'" class="text-xs text-gray-500 mt-1 block">
                            (në kg)
                          </span>
                        </td>
                        <td class="px-3 py-2 text-sm">
                          <div class="font-medium">{{ item.unit_type === 'kg' && isKeseMbeturinash(getItemProduct(item)) ? parseFloat(item.quantity).toFixed(2) : item.quantity }}</div>
                          <span v-if="getItemProduct(item) && getItemProduct(item).sold_by_package && getItemProduct(item).pieces_per_package && (!isKeseMbeturinash(getItemProduct(item)) || (isKeseMbeturinash(getItemProduct(item)) && item.unit_type === 'package'))" class="text-xs text-gray-500 mt-1 block">
                            = {{ item.quantity * getItemProduct(item).pieces_per_package }} copa
                          </span>
                          <span v-else-if="isKeseMbeturinash(getItemProduct(item)) && item.unit_type === 'kg'" class="text-xs text-gray-500 mt-1 block">
                            kg
                          </span>
                        </td>
                        <td class="px-3 py-2 text-sm">{{ formatPrice(item.unit_price) }}</td>
                        <td class="px-3 py-2 text-sm font-semibold">{{ formatPrice(item.total_price) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="border-t pt-4">
                  <div class="flex justify-between items-center mb-2">
                    <span class="font-semibold">Nëntotali:</span>
                    <span class="font-bold">{{ formatPrice(selectedInvoice.subtotal) }}</span>
                  </div>
                  <div v-if="selectedInvoice.has_vat" class="flex justify-between items-center mb-2">
                    <span class="font-semibold">TVSH ({{ selectedInvoice.vat_rate }}%):</span>
                    <span class="font-bold">{{ formatPrice(selectedInvoice.vat_amount) }}</span>
                  </div>
                  <div class="flex justify-between items-center pt-2 border-t">
                    <span class="text-lg font-bold">Totali:</span>
                    <span class="text-lg font-bold text-primary-600">{{ formatPrice(selectedInvoice.total_amount) }}</span>
                  </div>
                  <div v-if="selectedInvoice.status === 'paid' && selectedInvoice.paid_date" class="mt-3 pt-3 border-t">
                    <p class="text-sm text-gray-500">Data e Pagesës:</p>
                    <p class="text-lg font-semibold text-green-600">{{ formatDate(selectedInvoice.paid_date) }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-end gap-3">
              <button 
                @click="closeViewModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
              >
                Mbyll
              </button>
              <button 
                v-if="selectedInvoice && selectedInvoice.status !== 'paid'"
                @click="deleteInvoice(selectedInvoice)"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
              >
                🗑️ Fshi Faturën
              </button>
              <button 
                @click="printInvoice(selectedInvoice)"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700"
              >
                🖨️ Printo
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Create Invoice Modal -->
      <div v-if="showCreateInvoiceModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-0 sm:top-4 mx-auto max-w-4xl w-full p-3 sm:p-6">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 border-b border-gray-200 gap-3">
              <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Faturë e Re</h3>
              <button @click="closeCreateInvoiceModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none self-start sm:self-auto">
                &times;
              </button>
            </div>
            <div class="px-4 sm:px-6 py-4 sm:py-5 max-h-[90vh] sm:max-h-[80vh] overflow-y-auto">
              <form @submit.prevent="saveNewInvoice">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prodhuesi *</label>
                    <select 
                      v-model="newInvoiceForm.supplier_id"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                      <option value="">Zgjidh Prodhuesin</option>
                      <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                        {{ supplier.name }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data e Faturës *</label>
                    <input 
                      v-model="newInvoiceForm.invoice_date"
                      type="date"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data e Maturimit</label>
                    <input 
                      v-model="newInvoiceForm.due_date"
                      type="date"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pranim i Mallit (Opsional)</label>
                    <select 
                      v-model="newInvoiceForm.stock_receipt_id"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                      <option value="">Pa Pranim</option>
                      <option v-for="receipt in stockReceipts" :key="receipt.id" :value="receipt.id">
                        {{ receipt.receipt_number }} - {{ formatDate(receipt.receipt_date) }}
                      </option>
                    </select>
                  </div>
                </div>

                <div class="mb-6">
                  <div class="flex items-center mb-3">
                    <input 
                      type="checkbox"
                      v-model="newInvoiceForm.has_vat"
                      id="has_vat"
                      class="mr-2 h-4 w-4 text-primary-600"
                    >
                    <label for="has_vat" class="text-sm font-medium text-gray-700">
                      Me TVSH
                    </label>
                  </div>
                  <div v-if="newInvoiceForm.has_vat" class="mt-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Përqindja e TVSH (%)</label>
                    <input 
                      v-model.number="newInvoiceForm.vat_rate"
                      type="number"
                      step="0.01"
                      min="0"
                      max="100"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                  </div>
                </div>

                <div class="mb-6">
                  <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold text-gray-900">Produktet</h4>
                    <button 
                      type="button"
                      @click="addInvoiceItem"
                      class="px-3 py-1 text-sm bg-primary-600 text-white rounded-md hover:bg-primary-700"
                    >
                      + Shto Produkt
                    </button>
                  </div>
                  <div class="space-y-3">
                    <div 
                      v-for="(item, index) in newInvoiceForm.items" 
                      :key="index"
                      class="grid grid-cols-1 sm:grid-cols-12 gap-3 p-3 border border-gray-200 rounded-md"
                    >
                      <div class="sm:col-span-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Produkti *</label>
                        <select 
                          v-model="item.product_id"
                          @change="onProductSelect(item, index)"
                          required
                          class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md"
                        >
                          <option value="">Zgjidh Produktin</option>
                          <option v-for="product in allProducts" :key="product.id" :value="product.id">
                            {{ product.name }}
                          </option>
                        </select>
                      </div>
                      <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sasia *</label>
                        <input 
                          v-model.number="item.quantity"
                          type="number"
                          step="0.01"
                          min="0"
                          required
                          @input="calculateItemTotal(item)"
                          class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md"
                        >
                      </div>
                      <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Çmimi *</label>
                        <input 
                          v-model.number="item.unit_price"
                          type="number"
                          step="0.01"
                          min="0"
                          required
                          @input="calculateItemTotal(item)"
                          class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md"
                        >
                      </div>
                      <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Totali</label>
                        <input 
                          :value="formatPrice(item.total_price || 0)"
                          readonly
                          class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md bg-gray-50"
                        >
                      </div>
                      <div class="sm:col-span-2 flex items-end">
                        <button 
                          type="button"
                          @click="removeInvoiceItem(index)"
                          class="w-full px-2 py-1 text-sm bg-red-600 text-white rounded-md hover:bg-red-700"
                        >
                          Fshi
                        </button>
                      </div>
                    </div>
                    <div v-if="newInvoiceForm.items.length === 0" class="text-center py-4 text-gray-500">
                      Shtoni produkte për të krijuar faturën
                    </div>
                  </div>
                </div>

                <div class="mb-6 border-t pt-4">
                  <div class="flex justify-between items-center mb-2">
                    <span class="font-semibold">Nëntotali:</span>
                    <span class="font-bold">{{ formatPrice(calculateSubtotal) }}</span>
                  </div>
                  <div v-if="newInvoiceForm.has_vat" class="flex justify-between items-center mb-2">
                    <span class="font-semibold">TVSH ({{ newInvoiceForm.vat_rate }}%):</span>
                    <span class="font-bold">{{ formatPrice(calculateVatAmount) }}</span>
                  </div>
                  <div class="flex justify-between items-center pt-2 border-t">
                    <span class="text-lg font-bold">Totali:</span>
                    <span class="text-lg font-bold text-primary-600">{{ formatPrice(calculateTotalAmount) }}</span>
                  </div>
                </div>

                <div class="mb-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Shënime</label>
                  <textarea 
                    v-model="newInvoiceForm.notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md"
                  ></textarea>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3">
                  <button 
                    type="button"
                    @click="closeCreateInvoiceModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                  >
                    Anulo
                  </button>
                  <button 
                    type="submit"
                    :disabled="savingInvoice || newInvoiceForm.items.length === 0"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 disabled:opacity-50"
                  >
                    {{ savingInvoice ? 'Duke ruajtur...' : 'Krijo Faturën' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Invoice Modal -->
      <div v-if="showEditModal && editingInvoice" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-0 sm:top-4 mx-auto max-w-4xl w-full p-3 sm:p-6">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 border-b border-gray-200 gap-3">
              <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Edito Faturën</h3>
              <button @click="closeEditModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none self-start sm:self-auto">
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
                    @click="closeEditModal"
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

export default {
  name: 'AdminSupplierInvoices',
  data() {
    return {
      invoices: [],
      suppliers: [],
      allProducts: [], // Store all products for checking product details
      paymentStats: {
        paidCount: 0,
        paidTotal: 0,
        unpaidCount: 0,
        unpaidTotal: 0,
        totalCount: 0,
        totalAmount: 0,
        overdueCount: 0,
        overdueTotal: 0
      },
      filters: {
        supplier_id: '',
        status: '',
        paymentStatus: '',
        date_from: '',
        date_to: ''
      },
      showViewModal: false,
      showEditModal: false,
      showCreateInvoiceModal: false,
      selectedInvoice: null,
      editingInvoice: null,
      savingInvoice: false,
      stockReceipts: [],
      newInvoiceForm: {
        supplier_id: '',
        invoice_date: new Date().toISOString().split('T')[0],
        due_date: '',
        stock_receipt_id: '',
        has_vat: false,
        vat_rate: 18.00,
        notes: '',
        items: []
      }
    }
  },
  computed: {
    calculateSubtotal() {
      return this.newInvoiceForm.items.reduce((sum, item) => {
        return sum + (parseFloat(item.total_price || 0))
      }, 0)
    },
    calculateVatAmount() {
      if (!this.newInvoiceForm.has_vat) return 0
      return (this.calculateSubtotal * parseFloat(this.newInvoiceForm.vat_rate || 0)) / 100
    },
    calculateTotalAmount() {
      return this.calculateSubtotal + this.calculateVatAmount
    }
  },
    async mounted() {
    await this.checkAuth()
    await Promise.all([
      this.loadInvoices(),
      this.loadSuppliers(),
      this.loadProducts(),
      this.loadStockReceipts()
    ])
    this.calculatePaymentStats()
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
    async loadSuppliers() {
      try {
        const response = await axios.get('/api/suppliers', { params: { active_only: true } })
        this.suppliers = response.data.data || []
      } catch (error) {
        console.error('Error loading suppliers:', error)
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
    async loadStockReceipts() {
      try {
        const response = await axios.get('/api/stock-receipts')
        this.stockReceipts = response.data.data || []
      } catch (error) {
        console.error('Error loading stock receipts:', error)
      }
    },
    async loadInvoices() {
      try {
        const params = {}
        if (this.filters.supplier_id) {
          params.supplier_id = this.filters.supplier_id
        }
        if (this.filters.status) {
          params.status = this.filters.status
        }
        if (this.filters.date_from) {
          params.date_from = this.filters.date_from
        }
        if (this.filters.date_to) {
          params.date_to = this.filters.date_to
        }

        const response = await axios.get('/api/supplier-invoices', { params })
        let invoices = response.data.data || []

        // Filter by payment status if selected
        if (this.filters.paymentStatus === 'paid') {
          invoices = invoices.filter(inv => inv.status === 'paid')
        } else if (this.filters.paymentStatus === 'unpaid') {
          invoices = invoices.filter(inv => inv.status !== 'paid')
        }

        this.invoices = invoices
        this.calculatePaymentStats()
      } catch (error) {
        console.error('Error loading invoices:', error)
        alert('Gabim në ngarkimin e faturës')
      }
    },
    calculatePaymentStats() {
      const allInvoices = this.invoices
      this.paymentStats = {
        paidCount: allInvoices.filter(inv => inv.status === 'paid').length,
        paidTotal: allInvoices.filter(inv => inv.status === 'paid').reduce((sum, inv) => sum + parseFloat(inv.total_amount || 0), 0),
        unpaidCount: allInvoices.filter(inv => inv.status !== 'paid').length,
        unpaidTotal: allInvoices.filter(inv => inv.status !== 'paid').reduce((sum, inv) => sum + parseFloat(inv.total_amount || 0), 0),
        totalCount: allInvoices.length,
        totalAmount: allInvoices.reduce((sum, inv) => sum + parseFloat(inv.total_amount || 0), 0),
        overdueCount: allInvoices.filter(inv => inv.status === 'overdue').length,
        overdueTotal: allInvoices.filter(inv => inv.status === 'overdue').reduce((sum, inv) => sum + parseFloat(inv.total_amount || 0), 0)
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
    isKeseMbeturinash(product) {
      if (!product) return false
      const name = product.name || ''
      return name.toLowerCase().includes('kese mbeturinash')
    },
    getItemProduct(item) {
      // Try to get product from item.product (if loaded from API) or from allProducts
      if (item.product) {
        return item.product
      }
      if (item.product_id && this.allProducts.length > 0) {
        return this.allProducts.find(p => p.id == item.product_id)
      }
      return null
    },
    async viewInvoice(invoice) {
      try {
        // Load full invoice details with items and products
        const response = await axios.get(`/api/supplier-invoices/${invoice.id}`)
        this.selectedInvoice = response.data.data
        this.showViewModal = true
      } catch (error) {
        console.error('Error loading invoice details:', error)
        // Fallback to basic invoice data
        this.selectedInvoice = invoice
        this.showViewModal = true
      }
    },
    closeViewModal() {
      this.showViewModal = false
      this.selectedInvoice = null
    },
    editInvoice(invoice) {
      this.editingInvoice = {
        ...invoice,
        invoice_date: invoice.invoice_date ? new Date(invoice.invoice_date).toISOString().split('T')[0] : '',
        due_date: invoice.due_date ? new Date(invoice.due_date).toISOString().split('T')[0] : '',
        paid_date: invoice.paid_date ? new Date(invoice.paid_date).toISOString().split('T')[0] : ''
      }
      this.showEditModal = true
    },
    closeEditModal() {
      this.showEditModal = false
      this.editingInvoice = null
    },
    async saveEditedInvoice() {
      this.savingInvoice = true
      try {
        // Recalculate VAT if changed
        if (this.editingInvoice.has_vat) {
          const vatRate = this.editingInvoice.vat_rate || 18.00
          const vatAmount = (this.editingInvoice.subtotal * vatRate) / 100
          this.editingInvoice.vat_amount = vatAmount
          this.editingInvoice.total_amount = this.editingInvoice.subtotal + vatAmount
        } else {
          this.editingInvoice.vat_amount = 0
          this.editingInvoice.total_amount = this.editingInvoice.subtotal
        }

        // Set paid_date if status is paid
        if (this.editingInvoice.status === 'paid' && !this.editingInvoice.paid_date) {
          this.editingInvoice.paid_date = new Date().toISOString().split('T')[0]
        } else if (this.editingInvoice.status !== 'paid') {
          this.editingInvoice.paid_date = null
        }

        await axios.put(`/api/supplier-invoices/${this.editingInvoice.id}`, this.editingInvoice)
        alert('Fatura u përditësua me sukses!')
        this.closeEditModal()
        await this.loadInvoices()
      } catch (error) {
        console.error('Error saving invoice:', error)
        alert('Gabim në ruajtjen e faturës')
      } finally {
        this.savingInvoice = false
      }
    },
    async markAsPaid(invoice) {
      if (confirm(`A jeni të sigurt që dëshironi të shënoni faturën ${invoice.invoice_number} si të paguar?`)) {
        try {
          await axios.put(`/api/supplier-invoices/${invoice.id}`, {
            status: 'paid',
            paid_date: new Date().toISOString().split('T')[0]
          })
          alert('Fatura u shënua si e paguar!')
          await this.loadInvoices()
        } catch (error) {
          console.error('Error marking as paid:', error)
          alert('Gabim në shënimin e faturës si të paguar')
        }
      }
    },
    closeCreateInvoiceModal() {
      this.showCreateInvoiceModal = false
      this.newInvoiceForm = {
        supplier_id: '',
        invoice_date: new Date().toISOString().split('T')[0],
        due_date: '',
        stock_receipt_id: '',
        has_vat: false,
        vat_rate: 18.00,
        notes: '',
        items: []
      }
    },
    addInvoiceItem() {
      this.newInvoiceForm.items.push({
        product_id: '',
        product_name: '',
        quantity: 0,
        unit_price: 0,
        total_price: 0,
        notes: ''
      })
    },
    removeInvoiceItem(index) {
      this.newInvoiceForm.items.splice(index, 1)
    },
    onProductSelect(item, index) {
      const product = this.allProducts.find(p => p.id == item.product_id)
      if (product) {
        item.product_name = product.name
        // Set default price from product cost_price if available
        if (product.cost_price) {
          item.unit_price = parseFloat(product.cost_price)
          this.calculateItemTotal(item)
        }
      }
    },
    calculateItemTotal(item) {
      item.total_price = parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)
    },
    async saveNewInvoice() {
      if (this.newInvoiceForm.items.length === 0) {
        alert('Ju lutem shtoni të paktën një produkt')
        return
      }

      this.savingInvoice = true
      try {
        const payload = {
          supplier_id: this.newInvoiceForm.supplier_id,
          invoice_date: this.newInvoiceForm.invoice_date,
          due_date: this.newInvoiceForm.due_date || null,
          stock_receipt_id: this.newInvoiceForm.stock_receipt_id || null,
          has_vat: this.newInvoiceForm.has_vat || false,
          vat_rate: this.newInvoiceForm.has_vat ? (this.newInvoiceForm.vat_rate || 18.00) : 0,
          notes: this.newInvoiceForm.notes || null,
          items: this.newInvoiceForm.items.map(item => ({
            product_id: item.product_id || null,
            product_name: item.product_name,
            quantity: parseFloat(item.quantity),
            unit_price: parseFloat(item.unit_price),
            notes: item.notes || null
          }))
        }

        await axios.post('/api/supplier-invoices', payload)
        alert('Fatura u krijua me sukses!')
        this.closeCreateInvoiceModal()
        await this.loadInvoices()
      } catch (error) {
        console.error('Error creating invoice:', error)
        const errorMessage = error.response?.data?.message || 'Gabim në krijimin e faturës'
        alert(errorMessage)
      } finally {
        this.savingInvoice = false
      }
    },
    async deleteInvoice(invoice) {
      if (!confirm(`A jeni të sigurt që dëshironi të fshini faturën ${invoice.invoice_number}?`)) {
        return
      }

      // Ask for deletion reason
      const deletedReason = prompt(`Arsyeja e fshirjes për faturën ${invoice.invoice_number}:`, 'Gabim në të dhëna')
      if (deletedReason === null) {
        return // User cancelled
      }

      try {
        await axios.delete(`/api/supplier-invoices/${invoice.id}`, {
          data: {
            deleted_reason: deletedReason || 'Nuk u specifikua arsyeja',
            deleted_by: 'Admin' // You can get this from auth if available
          }
        })
        alert('Fatura u fshi me sukses!')
        
        // Close modal if open
        if (this.showViewModal && this.selectedInvoice?.id === invoice.id) {
          this.closeViewModal()
        }
        if (this.showEditModal && this.editingInvoice?.id === invoice.id) {
          this.closeEditModal()
        }
        
        // Reload invoices
        await this.loadInvoices()
      } catch (error) {
        console.error('Error deleting invoice:', error)
        const errorMessage = error.response?.data?.message || 'Gabim në fshirjen e faturës'
        alert(errorMessage)
      }
    },
    printInvoice(invoice) {
      const printWindow = window.open('', '_blank')
      const invoiceHtml = this.generateInvoiceHtml(invoice)
      printWindow.document.write(invoiceHtml)
      printWindow.document.close()
      printWindow.print()
    },
    generateInvoiceHtml(invoice) {
      return `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Fatura ${invoice.invoice_number}</title>
          <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .info { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .total { text-align: right; font-weight: bold; }
            .status-paid { color: green; font-weight: bold; }
            .status-unpaid { color: red; font-weight: bold; }
          </style>
        </head>
        <body>
          <div class="header">
            <h1>FATURA E PRODHUESIT</h1>
            <p>Nr. Faturës: ${invoice.invoice_number}</p>
          </div>
          <div class="info">
            <p><strong>Prodhuesi:</strong> ${invoice.supplier?.name || '-'}</p>
            <p><strong>Data:</strong> ${this.formatDate(invoice.invoice_date)}</p>
            ${invoice.due_date ? `<p><strong>Data e Maturimit:</strong> ${this.formatDate(invoice.due_date)}</p>` : ''}
            <p><strong>Statusi:</strong> ${this.getStatusLabel(invoice.status)}</p>
            ${invoice.status === 'paid' && invoice.paid_date ? `<p class="status-paid"><strong>✅ E Paguar:</strong> ${this.formatDate(invoice.paid_date)}</p>` : ''}
            ${invoice.status !== 'paid' ? `<p class="status-unpaid"><strong>❌ E Papaguar</strong></p>` : ''}
          </div>
          <table>
            <thead>
              <tr>
                <th>Produkti</th>
                <th>Sasia</th>
                <th>Çmimi Njësi</th>
                <th>Totali</th>
              </tr>
            </thead>
            <tbody>
              ${(invoice.items || []).map(item => `
                <tr>
                  <td>${item.product_name}</td>
                  <td>${item.quantity}</td>
                  <td>${this.formatPrice(item.unit_price)}</td>
                  <td>${this.formatPrice(item.total_price)}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
          <div class="total">
            <p>Nëntotali: ${this.formatPrice(invoice.subtotal)}</p>
            ${invoice.has_vat ? `<p>TVSH (${invoice.vat_rate}%): ${this.formatPrice(invoice.vat_amount)}</p>` : ''}
            <p style="font-size: 1.2em;">Totali: ${this.formatPrice(invoice.total_amount)}</p>
          </div>
        </body>
        </html>
      `
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

