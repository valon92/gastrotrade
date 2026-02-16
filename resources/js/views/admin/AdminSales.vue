<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="flex-1">
          <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-4">Shitjet</h1>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
          <router-link
            v-if="canManageClients"
            to="/admin/clients"
            class="btn-secondary text-center"
          >
            👥 Klientët
          </router-link>
          <router-link
            v-if="canManageStock"
            to="/admin/stock"
            class="btn-secondary text-center"
          >
            📊 Stoku
          </router-link>
          <router-link
            v-if="canViewTrash"
            to="/admin/trash"
            class="btn-secondary text-center"
          >
            🗑️ Historia e Fshirjeve
          </router-link>
          <router-link
            v-if="canManage"
            to="/admin/users"
            class="btn-secondary text-center"
          >
            👤 Adminat
          </router-link>
          <button 
            @click="logout"
            class="btn-secondary w-full sm:w-auto"
          >
            🚪 Dil
          </button>
        </div>
      </div>

      <!-- Statistics -->
      <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 border border-green-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-green-800">Të Paguara</h3>
            <span class="text-2xl">✅</span>
          </div>
          <p class="text-3xl font-bold text-green-900">{{ stats.paidCount }}</p>
          <p class="text-sm text-green-700 mt-1">{{ formatPrice(stats.paidTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-5 border border-red-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-red-800">Të Papaguara</h3>
            <span class="text-2xl">⚠️</span>
          </div>
          <p class="text-3xl font-bold text-red-900">{{ stats.unpaidCount }}</p>
          <p class="text-sm text-red-700 mt-1">{{ formatPrice(stats.unpaidTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-blue-800">Total Shitje</h3>
            <span class="text-2xl">💰</span>
          </div>
          <p class="text-3xl font-bold text-blue-900">{{ stats.totalCount }}</p>
          <p class="text-sm text-blue-700 mt-1">{{ formatPrice(stats.totalAmount) }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-purple-800">Vlera Mesatare</h3>
            <span class="text-2xl">📊</span>
          </div>
          <p class="text-3xl font-bold text-purple-900">{{ stats.totalCount > 0 ? formatPrice(stats.totalAmount / stats.totalCount) : '€0.00' }}</p>
          <p class="text-sm text-purple-700 mt-1">Për porosi</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Kërko</label>
          <input 
            v-model.trim="filters.search"
            type="text"
            placeholder="Nr. porosie, klient..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            @input="debounceSearch"
          >
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Statusi</label>
          <select 
            v-model="filters.status"
            @change="loadSales"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">Të Gjitha</option>
            <option value="ruajtur">Ruajtur</option>
            <option value="konfirmuar">Konfirmuar</option>
            <option value="dërguar">Dërguar</option>
            <option value="kompletuar">Kompletuar</option>
            <option value="anuluar">Anuluar</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Statusi i Pagesës</label>
          <select 
            v-model="filters.payment"
            @change="loadSales"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">Të Gjitha</option>
            <option value="paid">Të Paguara</option>
            <option value="unpaid">Të Papaguara</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Data</label>
          <input 
            v-model="filters.date_from"
            type="date"
            @change="loadSales"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
      </div>

      <!-- Sales Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nr. Porosie</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klienti</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkte</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statusi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagesa</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Totali</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Veprime</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading" class="text-center">
                <td colspan="8" class="px-4 py-8 text-gray-500">Duke ngarkuar...</td>
              </tr>
              <tr v-else-if="sales.length === 0" class="text-center">
                <td colspan="8" class="px-4 py-8 text-gray-500">Nuk u gjetën shitje</td>
              </tr>
              <tr v-else v-for="sale in sales" :key="sale.id" class="hover:bg-gray-50">
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ sale.order_number }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                  <div>
                    <div class="font-medium">{{ sale.customer_name || sale.client?.name || '-' }}</div>
                    <div class="text-xs text-gray-500">{{ sale.business_name || sale.client?.store_name || '' }}</div>
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ formatDate(sale.created_at) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ sale.total_items || (sale.items ? sale.items.length : 0) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span 
                    :class="[
                      'px-2 py-1 text-xs font-semibold rounded-full',
                      sale.status === 'kompletuar' ? 'bg-green-100 text-green-800' :
                      sale.status === 'dërguar' ? 'bg-blue-100 text-blue-800' :
                      sale.status === 'konfirmuar' ? 'bg-yellow-100 text-yellow-800' :
                      sale.status === 'anuluar' ? 'bg-red-100 text-red-800' :
                      'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ getStatusLabel(sale.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span 
                    :class="[
                      'px-2 py-1 text-xs font-semibold rounded-full',
                      sale.is_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ sale.is_paid ? '✅ E Paguar' : '⚠️ Jo e Paguar' }}
                  </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                  {{ formatPrice(sale.total_amount) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="viewSale(sale)"
                    class="text-primary-600 hover:text-primary-900 mr-3"
                    title="Shiko Detajet"
                  >
                    👁️
                  </button>
                  <button
                    @click="printSale(sale)"
                    class="text-gray-600 hover:text-gray-900"
                    title="Printo"
                  >
                    🖨️
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="!loading && sales.length > 0" class="mt-6 flex justify-center">
        <div class="flex gap-2">
          <button
            @click="loadSales(page - 1)"
            :disabled="page === 1"
            class="px-4 py-2 border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
          >
            Para
          </button>
          <span class="px-4 py-2 text-sm text-gray-700">
            Faqja {{ page }} nga {{ totalPages }}
          </span>
          <button
            @click="loadSales(page + 1)"
            :disabled="page >= totalPages"
            class="px-4 py-2 border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
          >
            Pas
          </button>
        </div>
      </div>
    </div>

    <!-- Sale Details Modal -->
    <div v-if="showSaleModal && selectedSale" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-0 sm:top-4 mx-auto max-w-4xl w-full p-3 sm:p-6">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 border-b border-gray-200 gap-3">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Detajet e Shitjes</h3>
            <button @click="closeSaleModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none self-start sm:self-auto">
              &times;
            </button>
          </div>
          <div class="px-4 sm:px-6 py-4 sm:py-5 max-h-[90vh] sm:max-h-[80vh] overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Nr. Porosie</p>
                <p class="text-lg font-semibold text-gray-900">{{ selectedSale.order_number }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Data</p>
                <p class="text-lg font-semibold text-gray-900">{{ formatDate(selectedSale.created_at) }}</p>
              </div>
              <!-- Always show client data -->
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Klienti</p>
                <p class="text-lg font-semibold text-gray-900">{{ selectedSale.customer_name || selectedSale.client?.name || '-' }}</p>
                <p class="text-sm text-gray-600">{{ selectedSale.business_name || selectedSale.client?.store_name || '' }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ selectedSale.fiscal_number || selectedSale.client?.fiscal_number || '' }}</p>
                <p class="text-xs text-gray-500">{{ selectedSale.city || selectedSale.client?.city || '' }}</p>
                <p class="text-xs text-gray-500">{{ selectedSale.phone || selectedSale.client?.phone || '' }}</p>
              </div>
              
              <!-- Show location data if available (when unit/location is selected) -->
              <div v-if="hasLocationData(selectedSale)" class="sm:col-span-2">
                <div class="mt-3 pt-3 border-t-2 border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 shadow-sm">
                  <div class="flex items-center gap-2 mb-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                      <span class="text-white text-sm">📍</span>
                    </div>
                    <p class="text-xs font-bold text-blue-800 uppercase tracking-wider">Të Dhënat e Pikës/Njësisë</p>
                  </div>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div v-if="selectedSale.location_unit_name" class="flex items-start gap-2">
                      <span class="text-blue-600 font-semibold text-xs min-w-[80px]">Pika/Njësia:</span>
                      <span class="text-sm font-bold text-gray-900 flex-1">{{ selectedSale.location_unit_name }}</span>
                    </div>
                    <div v-if="selectedSale.location_street_number" class="flex items-start gap-2">
                      <span class="text-blue-600 font-semibold text-xs min-w-[80px]">Adresa:</span>
                      <span class="text-sm text-gray-700 flex-1">{{ selectedSale.location_street_number }}</span>
                    </div>
                    <div v-if="selectedSale.location_city" class="flex items-start gap-2">
                      <span class="text-blue-600 font-semibold text-xs min-w-[80px]">Vendi/Qyteti:</span>
                      <span class="text-sm text-gray-700 flex-1">{{ selectedSale.location_city }}</span>
                    </div>
                    <div v-if="selectedSale.location_phone" class="flex items-start gap-2">
                      <span class="text-blue-600 font-semibold text-xs min-w-[80px]">Telefon:</span>
                      <span class="text-sm text-gray-700 flex-1 font-mono">{{ selectedSale.location_phone }}</span>
                    </div>
                    <div v-if="selectedSale.location_viber" class="flex items-start gap-2">
                      <span class="text-blue-600 font-semibold text-xs min-w-[80px]">Viber:</span>
                      <span class="text-sm text-gray-700 flex-1 font-mono">{{ selectedSale.location_viber }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Statusi</p>
                <span 
                  :class="[
                    'px-2 py-1 text-xs font-semibold rounded-full',
                    selectedSale.status === 'kompletuar' ? 'bg-green-100 text-green-800' :
                    selectedSale.status === 'dërguar' ? 'bg-blue-100 text-blue-800' :
                    selectedSale.status === 'konfirmuar' ? 'bg-yellow-100 text-yellow-800' :
                    selectedSale.status === 'anuluar' ? 'bg-red-100 text-red-800' :
                    'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ getStatusLabel(selectedSale.status) }}
                </span>
              </div>
            </div>

            <!-- Items -->
            <div class="mb-6">
              <div class="flex justify-between items-start mb-4">
                <h4 class="text-lg font-semibold text-gray-900">Produktet</h4>
                <!-- Advanced Location Display System - Technology Enhanced -->
                <div v-if="hasLocationData(selectedSale)" class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 border-2 border-blue-400 rounded-xl px-4 py-3 max-w-xs shadow-lg transform transition-all duration-300 hover:scale-105">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                      <span class="text-white text-xs">📍</span>
                    </div>
                    <p class="text-xs font-bold text-blue-800 uppercase tracking-wider">Sistemi i Lokacionit</p>
                  </div>
                  <div class="text-sm space-y-1.5">
                    <div v-if="selectedSale.location_unit_name" class="flex items-center gap-2">
                      <span class="text-blue-600 font-semibold text-xs">Njësia:</span>
                      <span class="font-bold text-gray-900">{{ selectedSale.location_unit_name }}</span>
                    </div>
                    <div v-if="selectedSale.location_street_number" class="flex items-center gap-2">
                      <span class="text-blue-600 font-semibold text-xs">Adresa:</span>
                      <span class="text-gray-700">{{ selectedSale.location_street_number }}</span>
                    </div>
                    <div v-if="selectedSale.location_city" class="flex items-center gap-2">
                      <span class="text-blue-600 font-semibold text-xs">Qyteti:</span>
                      <span class="text-gray-700">{{ selectedSale.location_city }}</span>
                    </div>
                    <div v-if="selectedSale.location_phone" class="flex items-center gap-2 pt-1 border-t border-blue-200">
                      <span class="text-green-600">📞</span>
                      <span class="text-gray-700 font-mono text-xs">{{ selectedSale.location_phone }}</span>
                    </div>
                    <div v-if="selectedSale.location_viber" class="flex items-center gap-2">
                      <span class="text-purple-600">💬</span>
                      <span class="text-gray-700 font-mono text-xs">{{ selectedSale.location_viber }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produkti</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sasia</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Çmimi Njësi</th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Totali</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in (selectedSale.items || [])" :key="item.id">
                      <td class="px-4 py-3 text-sm text-gray-900">
                        {{ item.product_name }}
                        <span v-if="item.sold_by_package && item.pieces_per_package" class="text-xs text-gray-500">
                          ({{ item.pieces_per_package }}cp/pako)
                        </span>
                      </td>
                      <td class="px-4 py-3 text-sm text-gray-700">
                        <template v-if="item.sold_by_package && item.pieces_per_package">
                          <template v-if="item.unit_type === 'package' || (!item.unit_type && parseFloat(item.quantity) <= parseFloat(item.pieces_per_package) * 10)">
                            {{ parseFloat(item.quantity).toFixed(0) }} komplete ({{ (parseFloat(item.quantity) * parseFloat(item.pieces_per_package)).toFixed(0) }} copa)
                          </template>
                          <template v-else>
                            {{ parseFloat(item.quantity).toFixed(0) }} copa
                          </template>
                        </template>
                        <template v-else>
                          {{ parseFloat(item.quantity).toFixed(0) }} copë
                        </template>
                      </td>
                      <td class="px-4 py-3 text-sm text-gray-700">
                        {{ formatPrice(item.unit_price) }}
                      </td>
                      <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">
                        <div v-if="getItemSubtotal(item) > 0">
                          <div>{{ formatPrice(getItemSubtotal(item)) }}</div>
                          <div v-if="item.discount_amount > 0" class="text-xs text-red-600">
                            -{{ formatPrice(item.discount_amount) }}
                          </div>
                          <div class="font-semibold">
                            {{ formatPrice(item.total_price) }}
                          </div>
                        </div>
                        <div v-else>
                          {{ formatPrice(item.total_price) }}
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Summary -->
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm font-medium text-gray-700">Nëntotali (para zbritjeve):</span>
                  <span class="text-sm font-semibold text-gray-900">{{ formatPrice(selectedSale.subtotal || calculateItemsSubtotal()) }}</span>
                </div>
                <div v-if="calculateItemDiscountTotal() > 0" class="flex justify-between">
                  <span class="text-sm font-medium text-red-600">Zbritje e produkteve:</span>
                  <span class="text-sm font-semibold text-red-600">-{{ formatPrice(calculateItemDiscountTotal()) }}</span>
                </div>
                <div v-if="selectedSale.discount_amount > 0" class="flex justify-between">
                  <span class="text-sm font-medium text-red-600">Zbritje e përgjithshme:</span>
                  <span class="text-sm font-semibold text-red-600">-{{ formatPrice(selectedSale.discount_amount) }}</span>
                </div>
                <div v-if="selectedSale.has_vat && selectedSale.amount_before_vat" class="flex justify-between pt-2 border-t border-gray-200">
                  <span class="text-sm font-medium text-gray-700">Shuma para TVSH:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ formatPrice(selectedSale.amount_before_vat) }}</span>
                </div>
                <div v-if="selectedSale.has_vat" class="flex justify-between">
                  <span class="text-sm font-medium text-gray-700">TVSH (18%):</span>
                  <span class="text-sm font-semibold text-gray-900">{{ formatPrice(selectedSale.vat_amount || 0) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t-2 border-gray-300">
                  <span class="text-lg font-bold text-gray-900">Totali{{ selectedSale.has_vat ? ' me TVSH' : '' }}:</span>
                  <span class="text-lg font-bold text-primary-600">{{ formatPrice(selectedSale.total_amount) }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-end gap-3">
            <button 
              @click="closeSaleModal"
              class="px-4 sm:px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
            >
              Mbyll
            </button>
            <button 
              @click="printSale(selectedSale)"
              class="px-4 sm:px-6 py-2.5 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 transition-colors"
            >
              🖨️ Printo
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { adminStore } from '../../stores/adminStore'

export default {
  name: 'AdminSales',
  data() {
    return {
      sales: [],
      loading: false,
      stats: {
        paidCount: 0,
        unpaidCount: 0,
        totalCount: 0,
        paidTotal: 0,
        unpaidTotal: 0,
        totalAmount: 0
      },
      filters: {
        search: '',
        status: '',
        payment: '',
        date_from: ''
      },
      page: 1,
      perPage: 50,
      totalPages: 1,
      showSaleModal: false,
      selectedSale: null,
      searchTimeout: null
    }
  },
  computed: {
    canManage() {
      return adminStore.canManage()
    },
    canManageClients() {
      return adminStore.canManageClients()
    },
    canManageStock() {
      return adminStore.canManageStock()
    },
    canViewTrash() {
      return adminStore.canViewTrash()
    }
  },
  mounted() {
    // Load user data from store
    adminStore.loadUser()
    
    // If user data is not in store, try to get it from API
    if (!adminStore.user) {
      this.loadUserData()
    }
    
    this.setupAxiosInterceptor()
    
    // Check if order number is in URL params (from cart)
    const urlParams = new URLSearchParams(window.location.search)
    const orderNumber = urlParams.get('order')
    if (orderNumber) {
      this.filters.search = orderNumber
      // Scroll to top and highlight the order after loading
      setTimeout(() => {
        this.loadSales(1).then(() => {
          const sale = this.sales.find(s => s.order_number === orderNumber)
          if (sale) {
            this.viewSale(sale)
            // Remove order param from URL
            window.history.replaceState({}, '', '/admin/sales')
          }
        })
      }, 100)
    } else {
      this.loadSales()
    }
    
    this.loadStats()
  },
  methods: {
    hasLocationData(sale) {
      if (!sale) return false
      return !!(sale.location_unit_name || sale.location_street_number || sale.location_city || sale.location_phone || sale.location_viber)
    },
    setupAxiosInterceptor() {
      axios.interceptors.request.use(config => {
        const adminToken = localStorage.getItem('admin_token')
        if (adminToken) {
          config.headers.Authorization = `Bearer ${adminToken}`
        }
        return config
      })
    },
    async loadSales(page = 1) {
      this.loading = true
      this.page = page
      try {
        const params = {
          page: this.page,
          per_page: this.perPage
        }
        
        if (this.filters.search) {
          params.search = this.filters.search
        }
        if (this.filters.status) {
          params.status = this.filters.status
        }
        if (this.filters.payment) {
          params.is_paid = this.filters.payment === 'paid' ? 1 : 0
        }
        if (this.filters.date_from) {
          params.date_from = this.filters.date_from
        }

        const response = await axios.get('/api/sales', { params })
        this.sales = response.data.data || []
        this.totalPages = response.data.last_page || 1
        
        // If order number was in URL, find and open it
        const urlParams = new URLSearchParams(window.location.search)
        const orderNumber = urlParams.get('order')
        if (orderNumber && this.sales.length > 0) {
          const sale = this.sales.find(s => s.order_number === orderNumber)
          if (sale) {
            // Small delay to ensure UI is ready
            setTimeout(() => {
              this.viewSale(sale)
              // Remove order param from URL
              window.history.replaceState({}, '', '/admin/sales')
            }, 300)
          }
        }
        
        // Debug: Log first sale to check location data
        if (this.sales.length > 0) {
          console.log('First sale location data:', {
            order_number: this.sales[0].order_number,
            location_unit_name: this.sales[0].location_unit_name,
            location_street_number: this.sales[0].location_street_number,
            location_city: this.sales[0].location_city,
            location_phone: this.sales[0].location_phone,
            location_viber: this.sales[0].location_viber,
            client_location_id: this.sales[0].client_location_id,
            hasLocationData: !!(this.sales[0].location_unit_name || this.sales[0].location_street_number || this.sales[0].location_city || this.sales[0].location_phone || this.sales[0].location_viber)
          })
          
          // If location data is missing but client_location_id exists, try to load it
          this.sales.forEach((sale, index) => {
            if ((!sale.location_unit_name && !sale.location_street_number) && sale.client_location_id) {
              console.log(`Sale ${index} (${sale.order_number}) has client_location_id but missing location data, will load on print/view`)
            }
          })
        }
      } catch (error) {
        console.error('Error loading sales:', error)
        alert('Gabim gjatë ngarkimit të shitjeve')
      } finally {
        this.loading = false
      }
    },
    async loadStats() {
      try {
        const response = await axios.get('/api/orders/stats/payments')
        if (response.data.success) {
          this.stats = response.data.data
        }
      } catch (error) {
        console.error('Error loading stats:', error)
        // Silently fail - stats are not critical
      }
    },
    debounceSearch() {
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => {
        this.loadSales(1)
      }, 500)
    },
    async viewSale(sale) {
      // If location data is missing but client_location_id exists, try to load it from API
      if ((!sale.location_unit_name && !sale.location_street_number) && sale.client_location_id) {
        try {
          // Try to get fresh order data with location
          const response = await axios.get(`/api/orders/${sale.id}`)
          if (response.data.success && response.data.data) {
            const freshOrder = response.data.data
            // Update sale with location data
            sale.location_unit_name = freshOrder.location_unit_name
            sale.location_street_number = freshOrder.location_street_number
            sale.location_city = freshOrder.location_city
            sale.location_phone = freshOrder.location_phone
            sale.location_viber = freshOrder.location_viber
            console.log('Loaded location data from API:', {
              location_unit_name: sale.location_unit_name,
              location_street_number: sale.location_street_number
            })
          }
        } catch (error) {
          console.error('Error loading location data:', error)
        }
      }
      
      // Debug: Log location data and items
      console.log('Sale location data:', {
        order_number: sale.order_number,
        location_unit_name: sale.location_unit_name,
        location_street_number: sale.location_street_number,
        location_city: sale.location_city,
        location_phone: sale.location_phone,
        location_viber: sale.location_viber,
        client_location_id: sale.client_location_id,
        hasLocationData: !!(sale.location_unit_name || sale.location_street_number || sale.location_city || sale.location_phone || sale.location_viber)
      })
      console.log('Sale items:', sale.items?.map(item => ({
        product_name: item.product_name,
        quantity: item.quantity,
        unit_type: item.unit_type,
        sold_by_package: item.sold_by_package,
        pieces_per_package: item.pieces_per_package,
        shouldShowAsPackages: item.sold_by_package && item.pieces_per_package && (item.unit_type === 'package' || (!item.unit_type && parseFloat(item.quantity) < parseFloat(item.pieces_per_package)))
      })))
      
      // Set selectedSale first to show modal
      this.selectedSale = sale
      this.showSaleModal = true
      
      // Force Vue reactivity update after setting selectedSale
      this.$nextTick(() => {
        // If location data is still missing after modal opens, try to reload from API
        if ((!this.selectedSale.location_unit_name && !this.selectedSale.location_street_number) && this.selectedSale.client_location_id) {
          console.log('Location data still missing after modal open, attempting reload...')
          // Reload location data from API
          axios.get(`/api/orders/${this.selectedSale.id}`)
            .then(response => {
              if (response.data.success && response.data.data) {
                const freshOrder = response.data.data
                // Update selectedSale with location data (Vue 3 doesn't need $set)
                if (this.selectedSale) {
                  this.selectedSale.location_unit_name = freshOrder.location_unit_name
                  this.selectedSale.location_street_number = freshOrder.location_street_number
                  this.selectedSale.location_city = freshOrder.location_city
                  this.selectedSale.location_phone = freshOrder.location_phone
                  this.selectedSale.location_viber = freshOrder.location_viber
                  console.log('Loaded location data from API in modal:', {
                    location_unit_name: this.selectedSale.location_unit_name,
                    location_street_number: this.selectedSale.location_street_number
                  })
                }
              }
            })
            .catch(error => {
              console.error('Error loading location data in modal:', error)
            })
        }
      })
    },
    closeSaleModal() {
      this.showSaleModal = false
      this.selectedSale = null
    },
    async printSale(sale) {
      if (!sale) return

      // Create a copy of sale to avoid mutating the original
      let saleData = { ...sale }
      
      // Check if location data exists
      let hasLocationData = !!(saleData.location_unit_name || saleData.location_street_number || saleData.location_city || saleData.location_phone || saleData.location_viber)
      
      // Debug: Log initial location data
      console.log('Print sale - Initial location data:', {
        location_unit_name: saleData.location_unit_name,
        location_street_number: saleData.location_street_number,
        location_city: saleData.location_city,
        location_phone: saleData.location_phone,
        location_viber: saleData.location_viber,
        client_location_id: saleData.client_location_id,
        hasLocationData: hasLocationData
      })
      
      // If location data is missing but client_location_id exists, try to load it from API
      if (!hasLocationData && saleData.client_location_id) {
        try {
          console.log('Loading location data from API for order:', saleData.id)
          // Try to get fresh order data with location
          const response = await axios.get(`/api/orders/${saleData.id}`)
          if (response.data.success && response.data.data) {
            const freshOrder = response.data.data
            console.log('Fresh order data from API:', {
              location_unit_name: freshOrder.location_unit_name,
              location_street_number: freshOrder.location_street_number,
              location_city: freshOrder.location_city,
              location_phone: freshOrder.location_phone,
              location_viber: freshOrder.location_viber
            })
            // Update saleData with location data
            saleData.location_unit_name = freshOrder.location_unit_name || null
            saleData.location_street_number = freshOrder.location_street_number || null
            saleData.location_city = freshOrder.location_city || null
            saleData.location_phone = freshOrder.location_phone || null
            saleData.location_viber = freshOrder.location_viber || null
            hasLocationData = !!(saleData.location_unit_name || saleData.location_street_number || saleData.location_city || saleData.location_phone || saleData.location_viber)
            console.log('Updated saleData with location data:', {
              location_unit_name: saleData.location_unit_name,
              location_street_number: saleData.location_street_number,
              hasLocationData: hasLocationData
            })
          }
        } catch (error) {
          console.error('Error loading location data for printing:', error)
        }
      }
      
      // Final check - use saleData instead of sale
      hasLocationData = !!(saleData.location_unit_name || saleData.location_street_number || saleData.location_city || saleData.location_phone || saleData.location_viber)
      console.log('Print sale - Final location data check:', {
        location_unit_name: saleData.location_unit_name,
        location_street_number: saleData.location_street_number,
        location_city: saleData.location_city,
        location_phone: saleData.location_phone,
        location_viber: saleData.location_viber,
        hasLocationData: hasLocationData,
        willShowLocation: hasLocationData
      })

      const totalAmount = saleData.total_amount ? this.formatPrice(saleData.total_amount) : 'Sipas kërkesës'
      const createdAt = this.formatDate(saleData.created_at)
      const paidAt = saleData.paid_at ? this.formatDate(saleData.paid_at) : null
      const isPaid = saleData.is_paid === true || saleData.is_paid === 1
      const paymentStatus = isPaid ? 'E PAGUAR' : 'JO E PAGUAR'
      const paymentStatusClass = isPaid ? 'color: #059669; font-weight: bold;' : 'color: #dc2626; font-weight: bold;'

      const itemsRows = (saleData.items || [])
        .map(item => {
          let quantityText = ''
          if (item.sold_by_package && item.pieces_per_package) {
            // If unit_type is 'package' or if quantity is reasonable for packages (likely packages)
            if (item.unit_type === 'package' || (!item.unit_type && parseFloat(item.quantity) <= parseFloat(item.pieces_per_package) * 10)) {
              const totalPieces = parseFloat(item.quantity) * parseFloat(item.pieces_per_package)
              quantityText = `${parseFloat(item.quantity).toFixed(0)} komplete (${totalPieces.toFixed(0)} copa)`
            } else {
              quantityText = `${parseFloat(item.quantity).toFixed(0)} copa`
            }
          } else {
            quantityText = `${parseFloat(item.quantity).toFixed(0)} copë`
          }

          const unitPrice = item.unit_price ? this.formatPrice(item.unit_price) : 'Sipas kërkesës'
          
          // Calculate item subtotal and discount
          let itemSubtotal = 0
          if (item.unit_price) {
            if (item.sold_by_package && item.pieces_per_package) {
              itemSubtotal = item.unit_price * item.quantity * item.pieces_per_package
            } else {
              itemSubtotal = item.unit_price * item.quantity
            }
          }
          
          const itemDiscount = item.discount_amount || 0
          const itemTotal = item.total_price || (itemSubtotal > 0 ? itemSubtotal - itemDiscount : 0)

          return `
            <tr>
              <td>${item.product_name}</td>
              <td>${quantityText}</td>
              <td>${unitPrice}</td>
              <td>
                ${itemSubtotal > 0 ? this.formatPrice(itemSubtotal) : 'Sipas kërkesës'}
                ${itemDiscount > 0 ? '<br><span style="color: #dc2626; font-size: 11px;">- ' + this.formatPrice(itemDiscount) + '</span>' : ''}
                <br><strong>${itemTotal > 0 ? this.formatPrice(itemTotal) : 'Sipas kërkesës'}</strong>
              </td>
            </tr>
          `
        })
        .join('')

      // Calculate totals
      const subtotal = sale.subtotal ? this.formatPrice(sale.subtotal) : ''
      const vatAmount = sale.vat_amount ? this.formatPrice(sale.vat_amount) : ''
      const amountBeforeVat = sale.amount_before_vat ? this.formatPrice(sale.amount_before_vat) : ''

      const printWindow = window.open('', '_blank')
      const htmlContent = '<html>' +
        '<head>' +
        '<title>Faturë ' + (saleData.order_number || 'N/A') + '</title>' +
        '<style>' +
        'body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }' +
        'h1 { color: #1f2937; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }' +
        '.meta { margin-bottom: 16px; font-size: 14px; }' +
        '.payment-status { margin-top: 12px; padding: 8px 12px; border-radius: 4px; display: inline-block; font-size: 14px; }' +
        '.payment-status.paid { background-color: #d1fae5; color: #059669; font-weight: bold; }' +
        '.payment-status.unpaid { background-color: #fee2e2; color: #dc2626; font-weight: bold; }' +
        'table { width: 100%; border-collapse: collapse; margin-top: 16px; }' +
        'th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; font-size: 14px; }' +
        'th { background-color: #f9fafb; }' +
        '.totals { margin-top: 16px; font-weight: bold; }' +
        '</style>' +
        '</head>' +
        '<body>' +
        '<h1>Faturë ' + (saleData.order_number || 'N/A') + '</h1>' +
        '<div class="meta">' +
        '<p><strong>Data e Porosisë:</strong> ' + createdAt + '</p>' +
        // If location data exists, show ONLY location data, otherwise show client data
        (saleData.location_unit_name || saleData.location_street_number || saleData.location_city || saleData.location_phone || saleData.location_viber ? (
          '<div style="background-color: #f0f9ff; padding: 12px; border-radius: 6px; margin-bottom: 12px; border-left: 4px solid #3b82f6;">' +
          '<p style="margin: 0 0 8px 0; font-weight: bold; color: #1e40af; font-size: 14px;">📍 Të Dhënat e Pikës/Njësisë</p>' +
          (saleData.location_unit_name ? '<p style="margin: 4px 0;"><strong>Pika/Njësia:</strong> ' + saleData.location_unit_name + '</p>' : '') +
          (saleData.location_street_number ? '<p style="margin: 4px 0;"><strong>Adresa:</strong> ' + saleData.location_street_number + '</p>' : '') +
          (saleData.location_city ? '<p style="margin: 4px 0;"><strong>Vendi/Qyteti:</strong> ' + saleData.location_city + '</p>' : '') +
          (saleData.location_phone ? '<p style="margin: 4px 0;"><strong>Telefon:</strong> ' + saleData.location_phone + '</p>' : '') +
          (saleData.location_viber ? '<p style="margin: 4px 0;"><strong>Viber:</strong> ' + saleData.location_viber + '</p>' : '') +
          '</div>'
        ) : (
          // Only show client data if no location data exists
          '<div style="margin-bottom: 12px;">' +
          '<p><strong>Klienti:</strong> ' + (saleData.customer_name || 'N/A') + ' — ' + (saleData.business_name || 'N/A') + '</p>' +
          '<p><strong>Nr. Fiskal:</strong> ' + (saleData.fiscal_number || 'N/A') + '</p>' +
          '<p><strong>Qyteti:</strong> ' + (saleData.city || 'N/A') + '</p>' +
          '<p><strong>Telefon/Viber:</strong> ' + (saleData.phone || 'N/A') + '</p>' +
          '</div>'
        )) +
        (isPaid ? '<div class="payment-status paid">' +
        '<strong>Statusi i Pagesës:</strong> ' + paymentStatus +
        (paidAt ? '<br><span style="font-size: 12px;">E paguar më: ' + paidAt + '</span>' : '') +
        '</div>' : '') +
        '</div>' +
        '<table>' +
        '<thead>' +
        '<tr><th>Produkti</th><th>Sasia</th><th>Çmimi Njësi</th><th>Totali</th></tr>' +
        '</thead>' +
        '<tbody>' +
        itemsRows +
        '</tbody>' +
        '</table>' +
        '<div class="totals">' +
        '<div style="margin-bottom: 8px;">' +
        '<p><strong>Totali i produkteve:</strong> ' + (sale.items ? sale.items.length : sale.total_items || 0) + '</p>' +
        (subtotal ? '<p><strong>Nëntotali (para zbritjeve):</strong> ' + subtotal + '</p>' : '') +
        // Calculate and show item discounts
        (() => {
          const totalItemDiscounts = (sale.items || []).reduce((sum, item) => {
            return sum + (parseFloat(item.discount_amount) || 0)
          }, 0)
          return totalItemDiscounts > 0 ? '<p style="color: #dc2626;"><strong>Totali i zbritjeve të produkteve:</strong> -' + this.formatPrice(totalItemDiscounts) + '</p>' : ''
        })() +
        (sale.discount_amount && sale.discount_amount > 0 ? '<p style="color: #dc2626;"><strong>Zbritje e përgjithshme ' + (sale.discount_type === 'percentage' ? sale.discount_value + '%' : 'fikse') + ':</strong> -' + this.formatPrice(sale.discount_amount) + '</p>' : '') +
        (sale.discount_amount && sale.discount_amount > 0 ? '<p style="color: #dc2626;"><strong>Zbritje e përgjithshme ' + (sale.discount_type === 'percentage' ? sale.discount_value + '%' : 'fikse') + ':</strong> -' + this.formatPrice(sale.discount_amount) + '</p>' : '') +
        (sale.has_vat && amountBeforeVat ? '<p><strong>Shuma para TVSH:</strong> ' + amountBeforeVat + '</p>' : '') +
        (sale.has_vat && vatAmount ? '<p><strong>TVSH (18%):</strong> ' + vatAmount + '</p>' : '') +
        '<p><strong>Vlera Totale' + (sale.has_vat ? ' me TVSH' : '') + ':</strong> ' + totalAmount + '</p>' +
        '</div>' +
        '</div>' +
        '<div style="margin-top: 40px; display: flex; justify-content: space-between;">' +
        '<div>' +
        '<p><strong>Nënshkrimi i Blerësit</strong></p>' +
        // If location data exists, show only location name, otherwise show business/customer name
        (sale.location_unit_name || sale.location_street_number ? (
          '<p>' + (sale.location_unit_name || '') + '</p>' +
          (sale.location_street_number ? '<p style="font-size: 12px; color: #6b7280; margin-top: 2px;">' + sale.location_street_number + '</p>' : '')
        ) : (
          '<p>' + (sale.business_name || sale.customer_name || '') + '</p>'
        )) +
        '</div>' +
        '<div>' +
        '<p><strong>Nënshkrimi i Shitësit</strong></p>' +
        '<p>GastroTrade</p>' +
        '</div>' +
        '</div>' +
        '<p style="margin-top: 20px; font-size: 12px; color: #6b7280;">' +
        'Data e lëshimit: ' + createdAt +
        '</p>' +
        '<div style="margin-top: 20px; text-align: center;">' +
        '<button onclick="window.print()" style="padding: 8px 16px; background-color: #6B7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">🖨 Printo</button>' +
        '</div>' +
        '</body>' +
        '</html>'

      printWindow.document.write(htmlContent)
      printWindow.document.close()
    },
    getStatusLabel(status) {
      const labels = {
        'ruajtur': 'Ruajtur',
        'konfirmuar': 'Konfirmuar',
        'dërguar': 'Dërguar',
        'kompletuar': 'Kompletuar',
        'anuluar': 'Anuluar'
      }
      return labels[status] || status
    },
    getItemSubtotal(item) {
      if (!item || !item.unit_price) return 0
      if (item.sold_by_package && item.pieces_per_package) {
        return item.unit_price * item.quantity * item.pieces_per_package
      }
      return item.unit_price * item.quantity
    },
    calculateItemsSubtotal() {
      if (!this.selectedSale || !this.selectedSale.items) return 0
      return this.selectedSale.items.reduce((sum, item) => sum + this.getItemSubtotal(item), 0)
    },
    calculateItemDiscountTotal() {
      if (!this.selectedSale || !this.selectedSale.items) return 0
      return this.selectedSale.items.reduce((sum, item) => sum + (parseFloat(item.discount_amount) || 0), 0)
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
      return new Date(date).toLocaleString('sq-AL', {
        dateStyle: 'medium',
        timeStyle: 'short'
      })
    },
    logout() {
      adminStore.clearUser()
      localStorage.removeItem('admin_token')
      delete axios.defaults.headers.common['Authorization']
      this.$router.push('/admin/login')
    }
  }
}
</script>

<style scoped>
.btn-primary {
  padding: 0.5rem 1rem;
  background-color: #3b82f6;
  color: white;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
  font-weight: 500;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-secondary {
  padding: 0.5rem 1rem;
  background-color: #e5e7eb;
  color: #1f2937;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
  font-weight: 500;
}

.btn-secondary:hover {
  background-color: #d1d5db;
}
</style>

