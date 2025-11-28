<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="flex-1">
          <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-4">Menaxhimi i Klientëve</h1>
          <button 
            @click="showAddModal = true"
            class="btn-primary w-full sm:w-auto"
          >
            + Shto Klient të Ri
          </button>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
          <router-link
            to="/admin/products"
            class="btn-secondary text-center"
          >
            📦 Produktet
          </router-link>
          <router-link
            to="/admin/stock"
            class="btn-secondary text-center"
          >
            📊 Stoku
          </router-link>
          <router-link
            to="/admin/trash"
            class="btn-secondary text-center"
          >
            🗑️ Historia e Fshirjeve
          </router-link>
          <button 
            @click="logout"
            class="btn-secondary w-full sm:w-auto"
          >
            🚪 Dil
          </button>
        </div>
      </div>

      <!-- Overall Payment Statistics -->
      <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 border border-green-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-green-800">Të Paguara</h3>
            <span class="text-2xl">✅</span>
          </div>
          <p class="text-3xl font-bold text-green-900">{{ overallPaymentStats.paidCount }}</p>
          <p class="text-sm text-green-700 mt-1">{{ formatPrice(overallPaymentStats.paidTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-5 border border-red-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-red-800">Të Papaguara</h3>
            <span class="text-2xl">⚠️</span>
          </div>
          <p class="text-3xl font-bold text-red-900">{{ overallPaymentStats.unpaidCount }}</p>
          <p class="text-sm text-red-700 mt-1">{{ formatPrice(overallPaymentStats.unpaidTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-blue-800">Total Porosi</h3>
            <span class="text-2xl">📦</span>
          </div>
          <p class="text-3xl font-bold text-blue-900">{{ overallPaymentStats.totalCount }}</p>
          <p class="text-sm text-blue-700 mt-1">{{ formatPrice(overallPaymentStats.totalAmount) }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-purple-800">Klientë</h3>
            <span class="text-2xl">👥</span>
          </div>
          <p class="text-3xl font-bold text-purple-900">{{ clients.length }}</p>
          <p class="text-sm text-purple-700 mt-1">Total klientë</p>
        </div>
      </div>

      <!-- Search Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Kërko sipas Emrit</label>
          <input 
            v-model.trim="searchFilters.name"
            type="text"
            placeholder="p.sh. Valon"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Kërko sipas Biznesit</label>
          <input 
            v-model.trim="searchFilters.business"
            type="text"
            placeholder="p.sh. GastroTrade"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Kërko sipas Nr. Fiskal</label>
          <input 
            v-model.trim="searchFilters.fiscal"
            type="text"
            placeholder="p.sh. 600123..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md uppercase tracking-wide focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Kërko sipas Qytetit</label>
          <input 
            v-model.trim="searchFilters.city"
            type="text"
            placeholder="p.sh. Ferizaj"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
      </div>

      <!-- Clients Table - Desktop -->
      <div class="hidden md:block bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emri</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emri i Biznisit</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nr. Fiskal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qyteti</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefon</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Viber</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statusi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veprime</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="client in filteredClients" :key="client.id">
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ client.name }}</td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ client.store_name || '-' }}</td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ client.fiscal_number || '-' }}</td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ client.city || '-' }}</td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ client.phone || '-' }}</td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ client.viber || '-' }}</td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span :class="client.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ client.is_active ? 'Aktiv' : 'Jo Aktiv' }}
                  </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                  <button @click="editClient(client)" class="text-primary-600 hover:text-primary-900">Edit</button>
                  <button @click="viewOrders(client)" class="text-indigo-600 hover:text-indigo-900">Porositë</button>
                  <button @click="viewPrices(client)" class="text-green-600 hover:text-green-900">Çmimet</button>
                  <button @click="deleteClient(client.id)" class="text-red-600 hover:text-red-900">Fshi</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Clients Cards - Mobile -->
      <div class="md:hidden space-y-4">
        <div 
          v-for="client in filteredClients" 
          :key="client.id"
          class="bg-white rounded-lg shadow-lg p-4"
        >
          <div class="flex justify-between items-start mb-3">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900">{{ client.name }}</h3>
              <p class="text-sm text-gray-600">{{ client.store_name || '-' }}</p>
            </div>
            <span :class="client.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                  class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap">
              {{ client.is_active ? 'Aktiv' : 'Jo Aktiv' }}
            </span>
          </div>
          
          <div class="space-y-2 text-sm text-gray-600 mb-4">
            <p v-if="client.fiscal_number"><strong>Nr. Fiskal:</strong> {{ client.fiscal_number }}</p>
            <p v-if="client.city"><strong>Qyteti:</strong> {{ client.city }}</p>
            <p v-if="client.phone"><strong>Telefon:</strong> {{ client.phone }}</p>
            <p v-if="client.viber"><strong>Viber:</strong> {{ client.viber }}</p>
          </div>

          <div class="flex flex-wrap gap-2">
            <button @click="editClient(client)" class="flex-1 px-3 py-2 text-sm font-medium text-primary-600 border border-primary-300 rounded-md hover:bg-primary-50">
              Edit
            </button>
            <button @click="viewOrders(client)" class="flex-1 px-3 py-2 text-sm font-medium text-indigo-600 border border-indigo-300 rounded-md hover:bg-indigo-50">
              Porositë
            </button>
            <button @click="viewPrices(client)" class="flex-1 px-3 py-2 text-sm font-medium text-green-600 border border-green-300 rounded-md hover:bg-green-50">
              Çmimet
            </button>
            <button @click="deleteClient(client.id)" class="flex-1 px-3 py-2 text-sm font-medium text-red-600 border border-red-300 rounded-md hover:bg-red-50">
              Fshi
            </button>
          </div>
        </div>
      </div>

      <!-- Add/Edit Modal -->
      <div v-if="showAddModal || editingClient" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-5 border w-full sm:w-96 max-w-md shadow-lg rounded-md bg-white m-4">
          <h3 class="text-lg font-bold text-gray-900 mb-4">
            {{ editingClient ? 'Edit Klient' : 'Shto Klient të Ri' }}
          </h3>
          <form @submit.prevent="saveClient">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Emri *</label>
                <input v-model="clientForm.name" type="text" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Emri i Biznisit *</label>
                <input v-model="clientForm.store_name" type="text" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Qyteti</label>
                <input v-model="clientForm.city" type="text" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Telefon</label>
                <input v-model="clientForm.phone" type="text" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Viber</label>
                <input v-model="clientForm.viber" type="text" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Numri Fiskal i Biznesit *</label>
                <input v-model="clientForm.fiscal_number" type="text" required
                       placeholder="p.sh. 123456789"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase tracking-wide">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Shënime</label>
                <textarea v-model="clientForm.notes" rows="3" 
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
              </div>
              <div>
                <label class="flex items-center">
                  <input v-model="clientForm.is_active" type="checkbox" 
                         class="rounded border-gray-300 text-primary-600">
                  <span class="ml-2 text-sm text-gray-700">Aktiv</span>
                </label>
              </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
              <button type="button" @click="closeModal" class="btn-secondary w-full sm:w-auto">Anulo</button>
              <button type="submit" class="btn-primary w-full sm:w-auto">Ruaj</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Orders Modal -->
      <div v-if="showOrdersModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-0 sm:top-12 mx-auto max-w-4xl w-full p-3 sm:p-6">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 border-b border-gray-200 gap-3">
              <div class="flex-1">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Porositë e Klientit</h3>
                <p class="text-sm text-gray-500">
                  {{ selectedClientName }} • {{ selectedClientBusiness || 'Pa biznes të regjistruar' }}
                </p>
                <p v-if="selectedClientFiscal" class="text-xs text-gray-500">
                  Nr. Fiskal: {{ selectedClientFiscal }}
                </p>
              </div>
              <button @click="closeOrdersModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none self-start sm:self-auto">
                &times;
              </button>
            </div>

            <div class="px-4 sm:px-6 py-4 sm:py-5 max-h-[70vh] overflow-y-auto">
              <!-- Payment Statistics -->
              <div v-if="!ordersLoading && !ordersError && selectedClientOrders.length > 0" class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs font-medium text-green-700 mb-1">Të Paguara</p>
                      <p class="text-2xl font-bold text-green-900">{{ paymentStats.paidCount }}</p>
                      <p class="text-xs text-green-600 mt-1">{{ formatPrice(paymentStats.paidTotal) }}</p>
                    </div>
                    <div class="text-3xl">✅</div>
                  </div>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs font-medium text-red-700 mb-1">Të Papaguara</p>
                      <p class="text-2xl font-bold text-red-900">{{ paymentStats.unpaidCount }}</p>
                      <p class="text-xs text-red-600 mt-1">{{ formatPrice(paymentStats.unpaidTotal) }}</p>
                    </div>
                    <div class="text-3xl">⚠️</div>
                  </div>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs font-medium text-blue-700 mb-1">Totali</p>
                      <p class="text-2xl font-bold text-blue-900">{{ paymentStats.totalCount }}</p>
                      <p class="text-xs text-blue-600 mt-1">{{ formatPrice(paymentStats.totalAmount) }}</p>
                    </div>
                    <div class="text-3xl">💰</div>
                  </div>
                </div>
              </div>

              <!-- Payment Filter -->
              <div v-if="!ordersLoading && !ordersError && selectedClientOrders.length > 0" class="mb-4 flex flex-wrap gap-2">
                <button 
                  @click="paymentFilter = 'all'"
                  :class="paymentFilter === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                  class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
                >
                  Të Gjitha ({{ selectedClientOrders.length }})
                </button>
                <button 
                  @click="paymentFilter = 'paid'"
                  :class="paymentFilter === 'paid' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                  class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
                >
                  ✅ Të Paguara ({{ paymentStats.paidCount }})
                </button>
                <button 
                  @click="paymentFilter = 'unpaid'"
                  :class="paymentFilter === 'unpaid' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                  class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
                >
                  ⚠️ Të Papaguara ({{ paymentStats.unpaidCount }})
                </button>
              </div>

              <div v-if="ordersLoading" class="flex items-center gap-3 text-gray-600">
                <span class="inline-block h-5 w-5 border-2 border-primary-600 border-t-transparent rounded-full animate-spin"></span>
                Duke ngarkuar porositë...
              </div>

              <div v-else-if="ordersError" class="p-4 bg-red-50 text-red-700 rounded-lg">
                {{ ordersError }}
              </div>

              <div v-else-if="filteredOrders.length === 0" class="p-6 text-center text-gray-500">
                <div class="text-4xl mb-2">{{ paymentFilter === 'paid' ? '✅' : paymentFilter === 'unpaid' ? '⚠️' : '📦' }}</div>
                <p class="text-lg font-medium mb-1">
                  {{ paymentFilter === 'paid' ? 'Nuk ka porosi të paguara' : paymentFilter === 'unpaid' ? 'Nuk ka porosi të papaguara' : 'Ky klient ende nuk ka porosi të ruajtura' }}
                </p>
                <p v-if="paymentFilter !== 'all'" class="text-sm text-gray-400 mt-1">
                  Të gjitha porositë janë {{ paymentFilter === 'paid' ? 'të papaguara' : 'të paguara' }}
                </p>
              </div>

              <div v-else class="space-y-4">
                <div 
                  v-for="order in filteredOrders" 
                  :key="order.id"
                  :class="[
                    'border rounded-xl p-4 transition-all duration-200',
                    order.is_paid 
                      ? 'border-green-300 bg-green-50 hover:bg-green-100' 
                      : 'border-red-300 bg-red-50 hover:bg-red-100'
                  ]"
                >
                  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                      <p class="text-sm text-gray-500">#{{ order.order_number }}</p>
                      <p class="text-gray-900 font-semibold">{{ formatDate(order.created_at) }}</p>
                    </div>
                    <div class="text-sm text-gray-600">
                      <p><strong>Produkte:</strong> {{ order.total_items }}</p>
                      <p v-if="order.has_vat && order.amount_before_vat && order.vat_amount">
                        <strong>Shuma para TVSH:</strong> {{ formatPrice(order.amount_before_vat) }}<br>
                        <strong>TVSH (18%):</strong> {{ formatPrice(order.vat_amount) }}<br>
                        <strong>Vlera totale me TVSH:</strong> {{ order.total_amount ? formatPrice(order.total_amount) : 'Sipas kërkesës' }}
                      </p>
                      <p v-else>
                        <strong>Vlera totale:</strong> {{ order.total_amount ? formatPrice(order.total_amount) : 'Sipas kërkesës' }}
                      </p>
                      <p class="mt-1">
                        <strong>Statusi i Pagesës:</strong> 
                        <span :class="order.is_paid ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                          {{ order.is_paid ? '✓ E Paguar' : '✗ Jo e Paguar' }}
                        </span>
                        <span v-if="order.is_paid && order.paid_at" class="text-xs text-gray-500 ml-2">
                          ({{ formatDate(order.paid_at) }})
                        </span>
                      </p>
                    </div>
                    <div class="flex flex-wrap gap-2 items-center">
                      <select 
                        v-model="order.status" 
                        @change="updateOrderStatus(order)"
                        class="px-3 py-1.5 text-xs font-semibold rounded-md border focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        :class="order.status === 'ruajtur' ? 'bg-blue-100 text-blue-800 border-blue-300' : order.status === 'konfirmuar' ? 'bg-green-100 text-green-800 border-green-300' : order.status === 'anuluar' ? 'bg-red-100 text-red-800 border-red-300' : order.status === 'dërguar' ? 'bg-purple-100 text-purple-800 border-purple-300' : order.status === 'kompletuar' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-gray-100 text-gray-700 border-gray-300'"
                      >
                        <option value="ruajtur">Ruajtur</option>
                        <option value="konfirmuar">Konfirmuar</option>
                        <option value="dërguar">Dërguar</option>
                        <option value="kompletuar">Kompletuar</option>
                        <option value="anuluar">Anuluar</option>
                      </select>
                      <label class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-md border cursor-pointer transition-colors"
                        :class="order.is_paid ? 'bg-green-100 text-green-800 border-green-300' : 'bg-gray-100 text-gray-700 border-gray-300'"
                      >
                        <input 
                          type="checkbox" 
                          :checked="order.is_paid"
                          @change="togglePaymentStatus(order)"
                          class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                        >
                        <span>{{ order.is_paid ? '✓ E Paguar' : 'Jo e Paguar' }}</span>
                      </label>
                      <button 
                        @click="editOrder(order)"
                        class="px-3 py-2 text-sm rounded-md border border-yellow-200 text-yellow-700 hover:bg-yellow-50 transition-colors duration-200"
                        title="Edito porosinë"
                      >
                        ✏️ Edit
                      </button>
                      <button 
                        @click="printOrder(order)"
                        class="px-3 py-2 text-sm rounded-md border border-primary-200 text-primary-700 hover:bg-primary-50 transition-colors duration-200"
                        title="Printo porosinë"
                      >
                        🖨 Printo
                      </button>
                      <button 
                        @click="deleteOrder(order)"
                        class="px-3 py-2 text-sm rounded-md border border-red-200 text-red-700 hover:bg-red-50 transition-colors duration-200"
                        title="Fshi porosinë"
                      >
                        🗑️ Fshi
                      </button>
                    </div>
                    <div v-if="order.is_paid && order.paid_at" class="mt-2 text-xs text-gray-500">
                      💰 E paguar më: {{ formatDate(order.paid_at) }}
                    </div>
                  </div>

                  <details class="mt-3">
                    <summary class="text-primary-600 cursor-pointer text-sm">Shiko produktet</summary>
                    <div class="mt-3 space-y-2">
                      <div 
                        v-for="item in order.items" 
                        :key="item.id"
                        class="bg-white rounded-lg p-3 text-sm flex flex-col md:flex-row md:items-center md:justify-between gap-2"
                      >
                        <div>
                          <p class="font-medium text-gray-900">{{ item.product_name }}</p>
                          <p class="text-gray-600">
                            {{ formatQuantity(item) }}
                          </p>
                        </div>
                        <div class="text-right text-gray-700">
                          <p><strong>Njësi:</strong> {{ item.unit_price ? formatPrice(item.unit_price) : 'Sipas kërkesës' }}</p>
                          <p><strong>Totali:</strong> {{ item.total_price ? formatPrice(item.total_price) : 'Sipas kërkesës' }}</p>
                        </div>
                      </div>
                    </div>
                  </details>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Order Modal -->
      <div v-if="showEditOrderModal && editingOrder" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-4 sm:top-12 mx-auto max-w-4xl w-full p-3 sm:p-6">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 border-b border-gray-200 gap-3">
              <div>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Edito Porosinë</h3>
                <p class="text-sm text-gray-500">#{{ editingOrder.order_number }}</p>
                <p v-if="editingOrder.updated_at && editingOrder.updated_at !== editingOrder.created_at" class="text-xs text-gray-400 mt-1">
                  E modifikuar: {{ formatDate(editingOrder.updated_at) }}
                </p>
              </div>
              <button @click="closeEditOrderModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none self-start sm:self-auto">
                &times;
              </button>
            </div>

            <div class="px-4 sm:px-6 py-4 sm:py-5 max-h-[80vh] overflow-y-auto">
              <!-- Customer Info -->
              <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-3">Të Dhënat e Klientit</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Emri</label>
                    <input v-model="editingOrder.customer_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Biznesi</label>
                    <input v-model="editingOrder.business_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Numri Fiskal</label>
                    <input v-model="editingOrder.fiscal_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm uppercase tracking-wide">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Qyteti</label>
                    <input v-model="editingOrder.city" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Telefon</label>
                    <input v-model="editingOrder.phone" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                  </div>
                </div>
              </div>

              <!-- Order Items -->
              <div class="mb-6">
                <div class="flex justify-between items-center mb-3">
                  <h4 class="font-semibold text-gray-900">Produktet</h4>
                  <button 
                    @click="addOrderItem"
                    class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                  >
                    + Shto Produkt
                  </button>
                </div>
                <div class="space-y-3">
                  <div 
                    v-for="(item, index) in editingOrderItems" 
                    :key="item.temp_id || item.id"
                    class="border border-gray-200 rounded-lg p-4 bg-white"
                  >
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                      <div class="md:col-span-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Produkti</label>
                        <input 
                          v-model="item.product_name" 
                          type="text" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                          placeholder="Emri i produktit"
                        >
                      </div>
                      <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sasia</label>
                        <input 
                          v-model.number="item.quantity" 
                          @input="onItemDiscountChange(item)"
                          type="number" 
                          min="1"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        >
                      </div>
                      <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Çmimi Njësi</label>
                        <input 
                          v-model.number="item.unit_price" 
                          @input="onItemDiscountChange(item)"
                          type="number" 
                          step="0.01"
                          min="0"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                          placeholder="0.00"
                        >
                      </div>
                      <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Totali</label>
                        <input 
                          :value="calculateItemTotal(item)" 
                          type="text" 
                          readonly
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50"
                        >
                      </div>
                      <div class="md:col-span-2 flex gap-2">
                        <button 
                          @click="removeOrderItem(index)"
                          class="px-3 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors w-full"
                        >
                          🗑️
                        </button>
                      </div>
                    </div>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-7 gap-3">
                      <div class="sm:col-span-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tipi i Zbritjes</label>
                        <select 
                          v-model="item.discount_type"
                          @change="onItemDiscountChange(item)"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        >
                          <option value="">Pa zbritje</option>
                          <option value="percentage">% (përqindje)</option>
                          <option value="fixed">€ (shumë fikse)</option>
                        </select>
                      </div>
                      <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Vlera</label>
                        <input 
                          v-model.number="item.discount_value"
                          @input="onItemDiscountChange(item)"
                          type="number"
                          min="0"
                          step="0.01"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                          placeholder="0.00"
                        >
                      </div>
                      <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Zbritja aktuale</label>
                        <input 
                          :value="item.discount_amount ? formatPrice(item.discount_amount) : '€0.00'"
                          type="text"
                          readonly
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50"
                        >
                      </div>
                    </div>
                    <p v-if="item.discount_amount > 0" class="mt-1 text-xs text-green-600">
                      Zbritje e aplikuar: -{{ formatPrice(item.discount_amount) }}
                    </p>
                    <div v-if="item.sold_by_package && item.pieces_per_package" class="mt-2 text-xs text-gray-500">
                      {{ item.quantity }} kompleti × {{ item.pieces_per_package }}cp = {{ item.quantity * item.pieces_per_package }} copa
                    </div>
                  </div>
                </div>
              </div>

              <!-- VAT Controls -->
              <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-3">TVSH (18%)</h4>
                <div class="flex items-center gap-3 mb-3">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                      type="checkbox" 
                      v-model="editingOrder.has_vat"
                      @change="onVatChange"
                      class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <span class="text-sm font-medium text-gray-700">
                      {{ editingOrder.has_vat ? '✓ Faturë me TVSH' : 'Faturë pa TVSH' }}
                    </span>
                  </label>
                </div>
                <div v-if="editingOrder.has_vat && calculateTotalAmount() > 0" class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Shuma para TVSH</label>
                    <input 
                      :value="formatPrice(calculateAmountBeforeVat())"
                      type="text"
                      readonly
                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white"
                    >
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">TVSH (18%)</label>
                    <input 
                      :value="formatPrice(calculateVatAmount())"
                      type="text"
                      readonly
                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white"
                    >
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Totali me TVSH</label>
                    <input 
                      :value="formatPrice(calculateTotalAmount())"
                      type="text"
                      readonly
                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white font-semibold"
                    >
                  </div>
                </div>
              </div>

              <!-- General Discount Controls -->
              <div class="mb-6 p-4 bg-amber-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-3">Zbritja e Përgjithshme (vetëm admin)</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tipi i zbritjes</label>
                    <select 
                      v-model="editingOrder.discount_type"
                      @change="onGeneralDiscountChange"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                    >
                      <option value="">Pa zbritje</option>
                      <option value="percentage">% (përqindje)</option>
                      <option value="fixed">€ (shumë fikse)</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Vlera</label>
                    <input 
                      v-model.number="editingOrder.discount_value"
                      @input="onGeneralDiscountChange"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                      placeholder="0.00"
                    >
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Zbritja aktuale</label>
                    <input 
                      :value="formatPrice(editingOrder.discount_amount || 0)"
                      type="text"
                      readonly
                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white"
                    >
                  </div>
                </div>
              </div>

              <!-- Order Summary -->
              <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                  <span class="font-semibold text-gray-900">Total Produkte:</span>
                  <span class="font-bold text-gray-900">{{ calculateTotalItems() }}</span>
                </div>
                <div class="flex justify-between items-center text-sm mb-1">
                  <span class="text-gray-700">Nëntotali:</span>
                  <span class="font-medium text-gray-900">{{ formatPrice(editingOrder && editingOrder.has_vat ? calculateAmountBeforeVat() : calculateTotalAmount()) }}</span>
                </div>
                <div 
                  v-if="calculateItemDiscountTotal() > 0" 
                  class="flex justify-between items-center text-sm text-red-600 mb-1"
                >
                  <span>Zbritje e produkteve:</span>
                  <span>-{{ formatPrice(calculateItemDiscountTotal()) }}</span>
                </div>
                <div 
                  v-if="editingOrder && editingOrder.discount_amount > 0" 
                  class="flex justify-between items-center text-sm text-red-600 mb-1"
                >
                  <span>Zbritje e përgjithshme:</span>
                  <span>-{{ formatPrice(editingOrder.discount_amount) }}</span>
                </div>
                <div 
                  v-if="editingOrder && editingOrder.has_vat && calculateAmountBeforeVat() > 0" 
                  class="flex justify-between items-center text-sm text-gray-700 mb-1 pt-2 border-t border-blue-200"
                >
                  <span>TVSH (18%):</span>
                  <span class="font-medium">{{ formatPrice(calculateVatAmount()) }}</span>
                </div>
                <div class="flex justify-between items-center mt-2 pt-2 border-t border-blue-100">
                  <span class="font-semibold text-gray-900">Vlera Totale{{ editingOrder && editingOrder.has_vat ? ' me TVSH' : '' }}:</span>
                  <span class="font-bold text-primary-600 text-lg">{{ formatPrice(calculateTotalAmount()) }}</span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex flex-col sm:flex-row justify-end gap-3">
                <button 
                  @click="closeEditOrderModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors"
                >
                  Anulo
                </button>
                <button 
                  @click="saveEditedOrder"
                  :disabled="savingOrder"
                  class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 transition-colors disabled:opacity-50"
                >
                  {{ savingOrder ? 'Duke ruajtur...' : 'Ruaj Ndryshimet' }}
                </button>
              </div>
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
  name: 'AdminClients',
  data() {
    return {
      clients: [],
      searchFilters: {
        name: '',
        business: '',
        fiscal: '',
        city: ''
      },
      showAddModal: false,
      editingClient: null,
      clientForm: {
        name: '',
        store_name: '',
        fiscal_number: '',
        city: '',
        phone: '',
        viber: '',
        notes: '',
        is_active: true
      },
      showOrdersModal: false,
      ordersLoading: false,
      ordersError: null,
      selectedClientOrders: [],
      selectedClientName: '',
      selectedClientBusiness: '',
      selectedClientFiscal: '',
      selectedClientId: null,
      showEditOrderModal: false,
      editingOrder: null,
      editingOrderItems: [],
      savingOrder: false,
      tempItemIdCounter: 0,
      paymentFilter: 'all',
      paymentStatsData: null
    }
  },
  async mounted() {
    await this.checkAuth()
    await this.loadClients()
    await this.loadAllOrders()
  },
  computed: {
    filteredClients() {
      const name = this.searchFilters.name.trim().toLowerCase()
      const business = this.searchFilters.business.trim().toLowerCase()
      const fiscal = this.searchFilters.fiscal.trim().toLowerCase()
      const city = this.searchFilters.city.trim().toLowerCase()

      if (!this.clients || this.clients.length === 0) {
        return []
      }

      return this.clients.filter(client => {
        const clientName = (client.name || '').toLowerCase()
        const clientBusiness = (client.store_name || '').toLowerCase()
        const clientFiscal = (client.fiscal_number || '').toLowerCase()
        const clientCity = (client.city || '').toLowerCase()

        const matchesName = !name || clientName.includes(name)
        const matchesBusiness = !business || clientBusiness.includes(business)
        const matchesFiscal = !fiscal || clientFiscal.includes(fiscal)
        const matchesCity = !city || clientCity.includes(city)

        return matchesName && matchesBusiness && matchesFiscal && matchesCity
      })
    },
    filteredOrders() {
      if (!this.selectedClientOrders || this.selectedClientOrders.length === 0) {
        return []
      }

      if (this.paymentFilter === 'all') {
        return this.selectedClientOrders
      }

      return this.selectedClientOrders.filter(order => {
        if (this.paymentFilter === 'paid') {
          return order.is_paid === true || order.is_paid === 1
        }
        if (this.paymentFilter === 'unpaid') {
          return !order.is_paid || order.is_paid === 0 || order.is_paid === false
        }
        return true
      })
    },
    paymentStats() {
      if (!this.selectedClientOrders || this.selectedClientOrders.length === 0) {
        return {
          paidCount: 0,
          unpaidCount: 0,
          totalCount: 0,
          paidTotal: 0,
          unpaidTotal: 0,
          totalAmount: 0
        }
      }

      const paid = this.selectedClientOrders.filter(o => o.is_paid === true || o.is_paid === 1)
      const unpaid = this.selectedClientOrders.filter(o => !o.is_paid || o.is_paid === 0 || o.is_paid === false)

      const paidTotal = paid.reduce((sum, o) => sum + (parseFloat(o.total_amount) || 0), 0)
      const unpaidTotal = unpaid.reduce((sum, o) => sum + (parseFloat(o.total_amount) || 0), 0)
      const totalAmount = this.selectedClientOrders.reduce((sum, o) => sum + (parseFloat(o.total_amount) || 0), 0)

      return {
        paidCount: paid.length,
        unpaidCount: unpaid.length,
        totalCount: this.selectedClientOrders.length,
        paidTotal,
        unpaidTotal,
        totalAmount
      }
    },
    overallPaymentStats() {
      if (!this.paymentStatsData) {
        return {
          paidCount: 0,
          unpaidCount: 0,
          totalCount: 0,
          paidTotal: 0,
          unpaidTotal: 0,
          totalAmount: 0
        }
      }

      return {
        paidCount: this.paymentStatsData.paid_count || 0,
        unpaidCount: this.paymentStatsData.unpaid_count || 0,
        totalCount: this.paymentStatsData.total_count || 0,
        paidTotal: this.paymentStatsData.paid_total || 0,
        unpaidTotal: this.paymentStatsData.unpaid_total || 0,
        totalAmount: this.paymentStatsData.total_amount || 0
      }
    }
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
    async loadClients() {
      try {
        const response = await axios.get('/api/clients')
        this.clients = response.data.data
      } catch (error) {
        console.error('Error loading clients:', error)
        alert('Gabim në ngarkimin e klientëve')
      }
    },
    async loadAllOrders() {
      try {
        // Use dedicated stats endpoint for better performance
        const statsResponse = await axios.get('/api/orders/stats/payments')
        if (statsResponse.data.success) {
          this.paymentStatsData = statsResponse.data.data
        } else {
          this.paymentStatsData = null
        }
      } catch (error) {
        console.error('Error loading payment stats:', error)
        this.paymentStatsData = null
      }
    },
    editClient(client) {
      this.editingClient = client
      this.clientForm = { ...client }
      this.showAddModal = true
    },
    async saveClient() {
      try {
        if (this.editingClient) {
          await axios.put(`/api/clients/${this.editingClient.id}`, this.clientForm)
        } else {
          await axios.post('/api/clients', this.clientForm)
        }
        await this.loadClients()
        this.closeModal()
        alert('Klienti u ruajt me sukses!')
      } catch (error) {
        console.error('Error saving client:', error)
        alert('Gabim në ruajtjen e klientit')
      }
    },
    async deleteClient(id) {
      if (!confirm('A jeni të sigurt që dëshironi të fshini këtë klient?')) {
        return
      }
      try {
        await axios.delete(`/api/clients/${id}`)
        await this.loadClients()
        alert('Klienti u fshi me sukses!')
      } catch (error) {
        console.error('Error deleting client:', error)
        alert('Gabim në fshirjen e klientit')
      }
    },
    viewPrices(client) {
      this.$router.push(`/admin/clients/${client.id}/prices`)
    },
    closeModal() {
      this.showAddModal = false
      this.editingClient = null
      this.clientForm = {
        name: '',
        store_name: '',
        fiscal_number: '',
        city: '',
        phone: '',
        viber: '',
        notes: '',
        is_active: true
      }
    },
    viewOrders(client) {
      this.selectedClientName = client.name
      this.selectedClientBusiness = client.store_name || ''
      this.selectedClientFiscal = client.fiscal_number || ''
      this.selectedClientId = client.id
      this.selectedClientOrders = []
      this.ordersError = null
      this.showOrdersModal = true
      this.ordersLoading = true
      this.loadClientOrders(client.id)
    },
    async loadClientOrders(clientId) {
      try {
        const response = await axios.get('/api/orders', {
          params: { client_id: clientId }
        })
        this.selectedClientOrders = response.data.data || []
      } catch (error) {
        console.error('Error loading orders:', error)
        this.ordersError = 'Gabim gjatë ngarkimit të porosive. Ju lutem provoni përsëri.'
      } finally {
        this.ordersLoading = false
      }
    },
    closeOrdersModal() {
      this.showOrdersModal = false
      this.ordersLoading = false
      this.ordersError = null
      this.selectedClientOrders = []
      this.selectedClientName = ''
      this.selectedClientBusiness = ''
      this.selectedClientFiscal = ''
      this.selectedClientId = null
      this.paymentFilter = 'all'
    },
    formatPrice(price) {
      if (price === null || price === undefined) {
        return 'Sipas kërkesës'
      }
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },
    formatDate(value) {
      if (!value) return '-'
      return new Date(value).toLocaleString('sq-AL', {
        dateStyle: 'medium',
        timeStyle: 'short'
      })
    },
    formatQuantity(item) {
      if (!item) return ''
      if (item.sold_by_package && item.pieces_per_package) {
        const totalPieces = item.quantity * item.pieces_per_package
        return `${item.quantity} komplete (${totalPieces} copa)`
      }
      return `${item.quantity} copë`
    },
    printOrder(order) {
      if (!order) return

      const totalAmount = order.total_amount ? this.formatPrice(order.total_amount) : 'Sipas kërkesës'
      const createdAt = this.formatDate(order.created_at)
      const paidAt = order.paid_at ? this.formatDate(order.paid_at) : null
      const isPaid = order.is_paid === true || order.is_paid === 1
      const paymentStatus = isPaid ? 'E PAGUAR' : 'JO E PAGUAR'
      const paymentStatusClass = isPaid ? 'color: #059669; font-weight: bold;' : 'color: #dc2626; font-weight: bold;'

      const itemsRows = (order.items || [])
        .map(item => {
          const quantityText = this.formatQuantity(item)
          const unitPrice = item.unit_price ? this.formatPrice(item.unit_price) : 'Sipas kërkesës'

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

      // Calculate total item discounts
      const totalItemDiscounts = (order.items || []).reduce((sum, item) => {
        return sum + (parseFloat(item.discount_amount) || 0)
      }, 0)

      const orderDataJson = JSON.stringify({
        order_number: order.order_number || 'N/A',
        customer_name: order.customer_name || 'N/A',
        business_name: order.business_name || 'N/A',
        fiscal_number: order.fiscal_number || 'N/A',
        city: order.city || 'N/A',
        phone: order.phone || 'N/A',
        total_amount: order.total_amount
      })

      const scriptTag = '<' + 'script' + '>'
      const scriptClose = '<' + '/' + 'script' + '>'

      const printWindow = window.open('', '_blank', 'width=900,height=650')
      if (!printWindow) {
        alert('Lejoni hapjen e dritareve të reja për të printuar porosinë.')
        return
      }

      const htmlContent = '<html>' +
        '<head>' +
        '<title>Faturë ' + (order.order_number || 'N/A') + '</title>' +
        '<style>' +
        'body { font-family: Arial, sans-serif; padding: 24px; color: #111827; }' +
        'h1 { font-size: 24px; margin-bottom: 8px; }' +
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
        '<h1>Faturë ' + (order.order_number || 'N/A') + '</h1>' +
        '<div class="meta">' +
        '<p><strong>Data e Porosisë:</strong> ' + createdAt + '</p>' +
        '<p><strong>Klienti:</strong> ' + (order.customer_name || 'N/A') + ' — ' + (order.business_name || 'N/A') + '</p>' +
        '<p><strong>Nr. Fiskal:</strong> ' + (order.fiscal_number || 'N/A') + '</p>' +
        '<p><strong>Qyteti:</strong> ' + (order.city || 'N/A') + '</p>' +
        '<p><strong>Telefon/Viber:</strong> ' + (order.phone || 'N/A') + '</p>' +
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
        '<p><strong>Totali i produkteve:</strong> ' + order.total_items + '</p>' +
        (order.subtotal ? '<p><strong>Nëntotali (para zbritjeve):</strong> ' + this.formatPrice(order.subtotal) + '</p>' : '') +
        (totalItemDiscounts > 0 ? '<p style="color: #dc2626;"><strong>Totali i zbritjeve të produkteve:</strong> -' + this.formatPrice(totalItemDiscounts) + '</p>' : '') +
        (order.discount_amount && order.discount_amount > 0 ? '<p style="color: #dc2626;"><strong>Zbritje e përgjithshme ' + (order.discount_type === 'percentage' ? order.discount_value + '%' : 'fikse') + ':</strong> -' + this.formatPrice(order.discount_amount) + '</p>' : '') +
        (order.has_vat && order.amount_before_vat && order.vat_amount ? 
          '<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">' +
          '<p><strong>Shuma para TVSH:</strong> ' + this.formatPrice(order.amount_before_vat) + '</p>' +
          '<p><strong>TVSH (18%):</strong> ' + this.formatPrice(order.vat_amount) + '</p>' +
          '<p style="font-size: 16px; margin-top: 8px;"><strong>Vlera Totale me TVSH:</strong> ' + totalAmount + '</p>' +
          '</div>' :
          '<p style="font-size: 16px; margin-top: 8px;"><strong>Vlera Totale:</strong> ' + totalAmount + '</p>'
        ) +
        '</div>' +
        '</div>' +
        '<div style="margin-top: 48px; padding-top: 24px; border-top: 2px solid #e5e7eb;">' +
        '<div style="display: flex; justify-content: space-between; margin-bottom: 60px;">' +
        '<div style="width: 45%; text-align: center;">' +
        '<p style="font-weight: bold; margin-bottom: 40px; border-top: 1px solid #111827; padding-top: 4px; display: inline-block; min-width: 200px;">Nënshkrimi i Blerësit</p>' +
        '<p style="font-size: 12px; color: #6b7280; margin-top: 8px;">' + (order.business_name || order.customer_name || '') + '</p>' +
        '</div>' +
        '<div style="width: 45%; text-align: center;">' +
        '<p style="font-weight: bold; margin-bottom: 40px; border-top: 1px solid #111827; padding-top: 4px; display: inline-block; min-width: 200px;">Nënshkrimi i Shitësit</p>' +
        '<p style="font-size: 12px; color: #6b7280; margin-top: 8px;">GastroTrade</p>' +
        '</div>' +
        '</div>' +
        '<div style="text-align: center; margin-top: 24px; font-size: 12px; color: #6b7280;">' +
        '<p><strong>Data e lëshimit:</strong> ' + createdAt + '</p>' +
        '</div>' +
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
        'const orderData = ' + orderDataJson + ';' +
        'function shareToViber() {' +
        'const orderText = "📦 Faturë " + orderData.order_number + "\\n\\nKlienti: " + orderData.customer_name + " — " + orderData.business_name + "\\nQyteti: " + orderData.city + "\\nTelefon: " + orderData.phone + "\\n\\nTotal: " + (orderData.total_amount ? orderData.total_amount.toFixed(2) + " €" : "Sipas kërkesës");' +
        'if (navigator.share) {' +
        'navigator.share({' +
        'title: "Faturë " + orderData.order_number,' +
        'text: orderText' +
        '}).catch(function(err) {' +
        'console.log("Error sharing:", err);' +
        'fallbackShareViber(orderText);' +
        '});' +
        '} else {' +
        'fallbackShareViber(orderText);' +
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
        'const orderText = "📦 Faturë " + orderData.order_number + "\\n\\nKlienti: " + orderData.customer_name + " — " + orderData.business_name + "\\nQyteti: " + orderData.city + "\\nTelefon: " + orderData.phone + "\\n\\nTotal: " + (orderData.total_amount ? orderData.total_amount.toFixed(2) + " €" : "Sipas kërkesës");' +
        'const whatsappUrl = "https://wa.me/?text=" + encodeURIComponent(orderText);' +
        'window.open(whatsappUrl, "_blank");' +
        '}' +
        'function shareToGmail() {' +
        'const subject = encodeURIComponent("Faturë " + orderData.order_number);' +
        'const body = encodeURIComponent("Faturë: " + orderData.order_number + "\\n\\nKlienti: " + orderData.customer_name + " — " + orderData.business_name + "\\nQyteti: " + orderData.city + "\\nTelefon: " + orderData.phone + "\\n\\nTotal: " + (orderData.total_amount ? orderData.total_amount.toFixed(2) + " €" : "Sipas kërkesës"));' +
        'const gmailUrl = "mailto:svalon95@gmail.com?subject=" + subject + "&body=" + body;' +
        'window.location.href = gmailUrl;' +
        '}' +
        scriptClose +
        '</body>' +
        '</html>'

      printWindow.document.write(htmlContent)
      printWindow.document.close()
    },
    async updateOrderStatus(order) {
      try {
        await axios.put(`/api/orders/${order.id}`, {
          status: order.status
        })
        // Optionally reload orders to ensure consistency
        if (this.selectedClientId) {
          await this.loadClientOrders(this.selectedClientId)
        }
      } catch (error) {
        console.error('Error updating order status:', error)
        alert('Gabim në përditësimin e statusit të porosisë')
        // Reload orders to revert the change
        if (this.selectedClientId) {
          await this.loadClientOrders(this.selectedClientId)
        }
      }
    },
    async togglePaymentStatus(order) {
      const newStatus = !order.is_paid
      const originalStatus = order.is_paid
      
      // Optimistically update UI
      order.is_paid = newStatus
      if (newStatus) {
        order.paid_at = new Date().toISOString()
      } else {
        order.paid_at = null
      }
      
      try {
        await axios.put(`/api/orders/${order.id}`, {
          is_paid: newStatus
        })
        // Reload to get server timestamp
        if (this.selectedClientId) {
          await this.loadClientOrders(this.selectedClientId)
        }
        // Reload overall stats
        await this.loadAllOrders()
      } catch (error) {
        console.error('Error updating payment status:', error)
        // Revert on error
        order.is_paid = originalStatus
        order.paid_at = originalStatus ? order.paid_at : null
        alert('Gabim në përditësimin e statusit të pagesës')
        // Reload orders to revert the change
        if (this.selectedClientId) {
          await this.loadClientOrders(this.selectedClientId)
        }
      }
    },
    async deleteOrder(order) {
      if (!confirm(`A jeni të sigurt që dëshironi të fshini porosinë #${order.order_number}?`)) {
        return
      }
      try {
        await axios.delete(`/api/orders/${order.id}`)
        // Remove from local array
        this.selectedClientOrders = this.selectedClientOrders.filter(o => o.id !== order.id)
        // Reload overall stats
        await this.loadAllOrders()
        alert('Porosia u fshi me sukses!')
      } catch (error) {
        console.error('Error deleting order:', error)
        alert('Gabim në fshirjen e porosisë')
      }
    },
    editOrder(order) {
      // Deep clone the order and items
      const clonedOrder = JSON.parse(JSON.stringify(order))
      this.editingOrder = {
        ...clonedOrder,
        discount_type: clonedOrder.discount_type || '',
        discount_value: clonedOrder.discount_value ?? 0,
        discount_amount: clonedOrder.discount_amount ?? 0,
        has_vat: clonedOrder.has_vat || false,
        vat_amount: clonedOrder.vat_amount || null,
        amount_before_vat: clonedOrder.amount_before_vat || null,
        fiscal_number: clonedOrder.fiscal_number ? String(clonedOrder.fiscal_number).trim().toUpperCase() : ''
      }
      this.editingOrderItems = (clonedOrder.items || []).map(item => ({
        ...item,
        discount_amount: item.discount_amount || 0,
        discount_type: item.discount_type || '',
        discount_value: item.discount_value ?? 0,
        temp_id: null // Keep original id for updates
      }))
      this.editingOrderItems.forEach(item => this.onItemDiscountChange(item))
      this.recalculateGeneralDiscountAmount()
      this.tempItemIdCounter = 0
      this.showEditOrderModal = true
    },
    closeEditOrderModal() {
      this.showEditOrderModal = false
      this.editingOrder = null
      this.editingOrderItems = []
      this.savingOrder = false
    },
    addOrderItem() {
      this.editingOrderItems.push({
        id: null,
        temp_id: `temp_${++this.tempItemIdCounter}`,
        product_id: null,
        product_name: '',
        quantity: 1,
        sold_by_package: false,
        pieces_per_package: null,
        unit_price: null,
        total_price: null,
        discount_amount: 0,
        discount_type: '',
        discount_value: 0
      })
      this.recalculateGeneralDiscountAmount()
    },
    removeOrderItem(index) {
      this.editingOrderItems.splice(index, 1)
      this.recalculateGeneralDiscountAmount()
    },
    getOrderItemBaseAmount(item) {
      if (!item || !item.unit_price) return 0
      if (item.sold_by_package && item.pieces_per_package) {
        return item.unit_price * item.quantity * item.pieces_per_package
      }
      return item.unit_price * item.quantity
    },
    getOrderItemDiscount(item) {
      if (!item) return 0
      const baseAmount = this.getOrderItemBaseAmount(item)
      if (!baseAmount) {
        item.discount_amount = 0
        return 0
      }
      const discountValue = Number(item.discount_value) || 0
      let discount = 0
      if (item.discount_type === 'percentage') {
        discount = (baseAmount * discountValue) / 100
      } else if (item.discount_type === 'fixed') {
        discount = Math.min(discountValue, baseAmount)
      } else {
        discount = item.discount_amount || 0
      }
      item.discount_amount = discount
      return discount
    },
    getOrderItemTotal(item) {
      if (!item || !item.unit_price) return 0
      const base = this.getOrderItemBaseAmount(item)
      const discount = this.getOrderItemDiscount(item)
      return Math.max(0, base - discount)
    },
    calculateItemsSubtotal() {
      if (!Array.isArray(this.editingOrderItems)) return 0
      return this.editingOrderItems.reduce((sum, item) => sum + this.getOrderItemBaseAmount(item), 0)
    },
    calculateItemsTotalAfterDiscount() {
      if (!Array.isArray(this.editingOrderItems)) return 0
      return this.editingOrderItems.reduce((sum, item) => sum + this.getOrderItemTotal(item), 0)
    },
    calculateItemDiscountTotal() {
      const subtotal = this.calculateItemsSubtotal()
      const afterDiscount = this.calculateItemsTotalAfterDiscount()
      return Math.max(0, subtotal - afterDiscount)
    },
    recalculateGeneralDiscountAmount(itemsTotal = null) {
      if (!this.editingOrder) return 0
      const totalAfterItems = itemsTotal !== null ? itemsTotal : this.calculateItemsTotalAfterDiscount()
      const type = this.editingOrder.discount_type
      const value = Number(this.editingOrder.discount_value) || 0
      let amount = 0
      if (type === 'percentage') {
        amount = (totalAfterItems * value) / 100
      } else if (type === 'fixed') {
        amount = Math.min(value, totalAfterItems)
      } else {
        amount = 0
      }
      this.editingOrder.discount_amount = amount
      return amount
    },
    onItemDiscountChange(item) {
      this.getOrderItemDiscount(item)
      this.recalculateGeneralDiscountAmount()
    },
    onGeneralDiscountChange() {
      this.recalculateGeneralDiscountAmount()
    },
    calculateItemTotal(item) {
      if (!item.unit_price) return 'Sipas kërkesës'
      return this.formatPrice(this.getOrderItemTotal(item))
    },
    calculateTotalItems() {
      return this.editingOrderItems.reduce((total, item) => {
        if (item.sold_by_package && item.pieces_per_package) {
          return total + (item.quantity * item.pieces_per_package)
        }
        return total + item.quantity
      }, 0)
    },
    calculateTotalAmount() {
      const itemsTotal = this.calculateItemsTotalAfterDiscount()
      const generalDiscount = this.recalculateGeneralDiscountAmount(itemsTotal)
      return Math.max(0, itemsTotal - generalDiscount)
    },
    calculateVatAmount() {
      if (!this.editingOrder || !this.editingOrder.has_vat) {
        return 0
      }
      const totalWithVat = this.calculateTotalAmount()
      if (totalWithVat <= 0) {
        return 0
      }
      // TVSH = Totali me TVSH / 6.5555
      return totalWithVat / 6.5555
    },
    calculateAmountBeforeVat() {
      if (!this.editingOrder || !this.editingOrder.has_vat) {
        return this.calculateTotalAmount()
      }
      const totalWithVat = this.calculateTotalAmount()
      const vatAmount = this.calculateVatAmount()
      // Shuma para TVSH = Totali me TVSH - TVSH
      return totalWithVat - vatAmount
    },
    onVatChange() {
      // Recalculate VAT when checkbox changes
      if (this.editingOrder && this.editingOrder.has_vat) {
        const totalWithVat = this.calculateTotalAmount()
        const vatAmount = this.calculateVatAmount()
        const amountBeforeVat = this.calculateAmountBeforeVat()
        
        // Update the VAT fields
        this.editingOrder.vat_amount = vatAmount
        this.editingOrder.amount_before_vat = amountBeforeVat
      } else {
        this.editingOrder.vat_amount = null
        this.editingOrder.amount_before_vat = null
      }
    },
    async saveEditedOrder() {
      if (!this.editingOrder || this.editingOrderItems.length === 0) {
        alert('Porosia duhet të ketë të paktën një produkt!')
        return
      }

      this.savingOrder = true
      try {
        // Prepare items for API
        const items = this.editingOrderItems.map(item => ({
          id: item.id || null,
          product_id: item.product_id || null,
          product_name: item.product_name,
          quantity: item.quantity,
          sold_by_package: item.sold_by_package || false,
          pieces_per_package: item.pieces_per_package || null,
          unit_price: item.unit_price || null,
          discount_amount: item.discount_amount || 0,
          discount_type: item.discount_type || null,
          discount_value: item.discount_value || 0,
          total_price: this.calculateItemTotalValue(item)
        }))

        const subtotal = this.calculateItemsSubtotal()
        const itemsTotalAfterDiscount = this.calculateItemsTotalAfterDiscount()
        const generalDiscountAmount = this.recalculateGeneralDiscountAmount(itemsTotalAfterDiscount)
        const itemDiscountAmount = this.calculateItemDiscountTotal()
        const totalAmount = Math.max(0, itemsTotalAfterDiscount - generalDiscountAmount)

        // Calculate VAT if enabled
        let finalTotalAmount = totalAmount
        let vatAmount = null
        let amountBeforeVat = null
        
        if (this.editingOrder.has_vat && totalAmount > 0) {
          // Totali aktual përfshin TVSH-në
          // TVSH = Totali me TVSH / 6.5555
          vatAmount = totalAmount / 6.5555
          // Shuma para TVSH = Totali me TVSH - TVSH
          amountBeforeVat = totalAmount - vatAmount
          // Totali me TVSH mbetet i njëjtë
          finalTotalAmount = totalAmount
        }

        const payload = {
          customer_name: this.editingOrder.customer_name,
          business_name: this.editingOrder.business_name,
          city: this.editingOrder.city,
          phone: this.editingOrder.phone,
          subtotal,
          item_discount_total: itemDiscountAmount,
          discount_amount: generalDiscountAmount,
          discount_type: this.editingOrder.discount_type || null,
          discount_value: this.editingOrder.discount_value || 0,
          total_amount: finalTotalAmount,
          has_vat: this.editingOrder.has_vat || false,
          vat_amount: vatAmount,
          amount_before_vat: amountBeforeVat,
          items
        }

        if (this.editingOrder.fiscal_number && String(this.editingOrder.fiscal_number).trim() !== '') {
          payload.fiscal_number = String(this.editingOrder.fiscal_number).trim().toUpperCase()
        }

        const response = await axios.put(`/api/orders/${this.editingOrder.id}`, payload)

        // Update local order
        const updatedOrder = response.data.data
        const orderIndex = this.selectedClientOrders.findIndex(o => o.id === updatedOrder.id)
        if (orderIndex !== -1) {
          this.selectedClientOrders[orderIndex] = updatedOrder
        }

        alert('Porosia u përditësua me sukses!')
        // Reload overall stats
        await this.loadAllOrders()
        this.closeEditOrderModal()
      } catch (error) {
        console.error('Error saving order:', error)
        if (error.response && error.response.data) {
          console.error('Server response:', error.response.data)
        }
        let errorMessage = 'Gabim në ruajtjen e porosisë'
        if (error.response && error.response.data) {
          if (error.response.data.message) {
            errorMessage = error.response.data.message
          }
          if (error.response.data.errors) {
            const errors = Object.values(error.response.data.errors).flat()
            if (errors.length) {
              errorMessage = errors.join('\n')
            }
          }
        }
        alert(errorMessage)
      } finally {
        this.savingOrder = false
      }
    },
    calculateItemTotalValue(item) {
      if (!item.unit_price) return null
      return this.getOrderItemTotal(item)
    }
  }
}
</script>

