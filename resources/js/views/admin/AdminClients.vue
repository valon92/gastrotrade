<template>
  <AdminLayout>
    <div class="p-4 sm:p-6 lg:p-8">
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
      </div>

      <!-- Overall Payment Statistics -->
      <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-3 sm:p-5 border border-green-200 shadow-sm min-w-0">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-green-800 truncate">Të Paguara</h3>
            <span class="text-lg sm:text-2xl shrink-0">✅</span>
          </div>
          <p class="text-xl sm:text-3xl font-bold text-green-900">{{ overallPaymentStats.paidCount }}</p>
          <p class="text-xs sm:text-sm text-green-700 mt-1 truncate">{{ formatPrice(overallPaymentStats.paidTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-3 sm:p-5 border border-red-200 shadow-sm min-w-0">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-red-800 truncate">Të Papaguara</h3>
            <span class="text-lg sm:text-2xl shrink-0">⚠️</span>
          </div>
          <p class="text-xl sm:text-3xl font-bold text-red-900">{{ overallPaymentStats.unpaidCount }}</p>
          <p class="text-xs sm:text-sm text-red-700 mt-1 truncate">{{ formatPrice(overallPaymentStats.unpaidTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-3 sm:p-5 border border-blue-200 shadow-sm min-w-0">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-blue-800 truncate">Total Porosi</h3>
            <span class="text-lg sm:text-2xl shrink-0">📦</span>
          </div>
          <p class="text-xl sm:text-3xl font-bold text-blue-900">{{ overallPaymentStats.totalCount }}</p>
          <p class="text-xs sm:text-sm text-blue-700 mt-1 truncate">{{ formatPrice(overallPaymentStats.totalAmount) }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-3 sm:p-5 border border-purple-200 shadow-sm min-w-0">
          <div class="flex items-center justify-between mb-1 sm:mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-purple-800 truncate">Klientë</h3>
            <span class="text-lg sm:text-2xl shrink-0">👥</span>
          </div>
          <p class="text-xl sm:text-3xl font-bold text-purple-900">{{ clients.length }}</p>
          <p class="text-xs sm:text-sm text-purple-700 mt-1">Total klientë</p>
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
            placeholder="p.sh. AronTrade"
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

      <!-- Clients Table - Desktop (vetëm në ekrane të mëdha lg+) -->
      <div class="hidden lg:block bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emri</th>
                <th class="px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emri i Biznisit</th>
                <th class="px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nr. Fiskal</th>
                <th class="px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qyteti</th>
                <th class="px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nr. Rrugës</th>
                <th class="px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Njësia</th>
                <th class="px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefon</th>
                <th class="px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Viber</th>
                <th class="px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statusi</th>
                <th class="px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veprime</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="client in filteredClients" :key="client.id">
                <td class="px-3 lg:px-4 py-3 lg:py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ client.name }}</td>
                <td class="px-3 lg:px-4 py-3 lg:py-4 whitespace-nowrap text-sm text-gray-500">{{ client.store_name || '-' }}</td>
                <td class="px-3 lg:px-4 py-3 lg:py-4 whitespace-nowrap text-sm text-gray-500">{{ client.fiscal_number || '-' }}</td>
                <td class="px-3 lg:px-4 py-3 lg:py-4 whitespace-nowrap text-sm text-gray-500">{{ client.city || '-' }}</td>
                <td class="px-3 lg:px-4 py-3 lg:py-4 whitespace-nowrap text-sm text-gray-500">{{ client.street_number || '-' }}</td>
                <td class="px-3 lg:px-4 py-3 lg:py-4 whitespace-nowrap text-sm text-gray-500">{{ client.unit || '-' }}</td>
                <td class="px-3 lg:px-4 py-3 lg:py-4 whitespace-nowrap text-sm text-gray-500">{{ client.phone || '-' }}</td>
                <td class="px-3 lg:px-4 py-3 lg:py-4 whitespace-nowrap text-sm text-gray-500">{{ client.viber || '-' }}</td>
                <td class="px-3 lg:px-4 py-3 lg:py-4 whitespace-nowrap">
                  <span :class="client.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ client.is_active ? 'Aktiv' : 'Jo Aktiv' }}
                  </span>
                </td>
                <td class="px-3 lg:px-4 py-3 lg:py-4 text-sm font-medium">
                  <div class="flex flex-wrap gap-1.5">
                    <button @click="editClient(client)" class="text-primary-600 hover:text-primary-900 whitespace-nowrap">Edit</button>
                    <button @click="viewOrders(client)" class="text-indigo-600 hover:text-indigo-900 whitespace-nowrap">Porositë</button>
                    <button @click="viewPrices(client)" class="text-green-600 hover:text-green-900 whitespace-nowrap">Çmimet</button>
                    <button @click="deleteClient(client.id)" class="text-red-600 hover:text-red-900 whitespace-nowrap">Fshi</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Clients Cards - Mobile & Tablet (deri në lg) -->
      <div class="lg:hidden space-y-4">
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
            <p v-if="client.street_number"><strong>Nr. e Rrugës:</strong> {{ client.street_number }}</p>
            <p v-if="client.unit"><strong>Njësia:</strong> {{ client.unit }}</p>
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
      <div v-if="showAddModal || editingClient" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-3 sm:p-4">
        <div class="relative w-full max-w-2xl bg-white shadow-lg rounded-lg my-4 sm:my-12 p-4 sm:p-5 max-h-[calc(100vh-2rem)] overflow-y-auto">
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
                       placeholder="p.sh. Ferizaj"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <!-- Lokacionet/Njësitë -->
              <div>
                <div class="flex justify-between items-center mb-2">
                  <label class="block text-sm font-medium text-gray-700">Pikat/Njësitë</label>
                  <button 
                    type="button"
                    @click="addLocation"
                    class="text-sm text-primary-600 hover:text-primary-800 font-medium"
                  >
                    + Shto Pikë/Njësi
                  </button>
                </div>
                <div v-if="clientForm.locations && clientForm.locations.length > 0" class="space-y-3 mb-3">
                  <div 
                    v-for="(location, index) in clientForm.locations" 
                    :key="index"
                    class="border border-gray-200 rounded-lg p-3 bg-gray-50"
                  >
                    <div class="flex justify-between items-start mb-2">
                      <span class="text-xs font-semibold text-gray-600">Pika/Njësia {{ index + 1 }}</span>
                      <button 
                        type="button"
                        @click="removeLocation(index)"
                        class="text-red-600 hover:text-red-800 text-xs"
                      >
                        ✕ Fshi
                      </button>
                    </div>
                    <div class="space-y-2">
                      <div>
                        <label class="block text-xs font-medium text-gray-600">Emri i Pikës/Njësisë *</label>
                        <input 
                          v-model="location.unit_name" 
                          type="text" 
                          required
                          placeholder="p.sh. Pika 1, Pika 2, Degë A"
                          class="mt-1 block w-full px-2 py-1.5 text-sm border-gray-300 rounded-md shadow-sm"
                        >
                      </div>
                      <div>
                        <label class="block text-xs font-medium text-gray-600">Nr. e Rrugës</label>
                        <input 
                          v-model="location.street_number" 
                          type="text" 
                          placeholder="p.sh. Rruga Lidhja E Prizerent, Nr. 15"
                          class="mt-1 block w-full px-2 py-1.5 text-sm border-gray-300 rounded-md shadow-sm"
                        >
                      </div>
                      <div class="grid grid-cols-2 gap-2">
                        <div>
                          <label class="block text-xs font-medium text-gray-600">Telefon</label>
                          <input 
                            v-model="location.phone" 
                            type="text" 
                            placeholder="p.sh. 048 75 66 46"
                            class="mt-1 block w-full px-2 py-1.5 text-sm border-gray-300 rounded-md shadow-sm"
                          >
                        </div>
                        <div>
                          <label class="block text-xs font-medium text-gray-600">Viber</label>
                          <input 
                            v-model="location.viber" 
                            type="text" 
                            placeholder="p.sh. 044 82 43 14"
                            class="mt-1 block w-full px-2 py-1.5 text-sm border-gray-300 rounded-md shadow-sm"
                          >
                        </div>
                      </div>
                      <div>
                        <label class="block text-xs font-medium text-gray-600">Vendi/Qyteti</label>
                        <input 
                          v-model="location.notes" 
                          type="text"
                          placeholder="p.sh. Ferizaj, Prishtinë, etj."
                          class="mt-1 block w-full px-2 py-1.5 text-sm border-gray-300 rounded-md shadow-sm"
                        >
                      </div>
                      <div>
                        <label class="flex items-center">
                          <input 
                            v-model="location.is_active" 
                            type="checkbox" 
                            class="rounded border-gray-300 text-primary-600"
                          >
                          <span class="ml-2 text-xs text-gray-700">Aktiv</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                <p v-else class="text-xs text-gray-500 mb-2">
                  Nuk ka pika/njësi të shtuara. Klikoni "+ Shto Pikë/Njësi" për të shtuar.
                </p>
              </div>
              
              <!-- Telefon dhe Viber bazë (për klientin kryesor) -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Telefon Kryesor</label>
                <input v-model="clientForm.phone" type="text" 
                       placeholder="p.sh. 048 75 66 46"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <p class="mt-1 text-xs text-gray-500">
                  Telefoni kryesor i biznesit (opsional nëse ka pika me telefona të veçantë)
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Viber Kryesor</label>
                <input v-model="clientForm.viber" type="text" 
                       placeholder="p.sh. 044 82 43 14"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <p class="mt-1 text-xs text-gray-500">
                  Viber kryesor i biznesit (opsional nëse ka pika me viber të veçantë)
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Numri Fiskal i Biznesit *</label>
                <input v-model="clientForm.fiscal_number" type="text" required
                       placeholder="p.sh. 123456789"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase tracking-wide">
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
      <div v-if="showOrdersModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-2 sm:p-4 md:p-6">
        <div class="relative w-full max-w-4xl my-4 sm:my-8 px-0 sm:px-2">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden min-w-0">
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

            <div class="px-3 sm:px-6 py-4 sm:py-5 max-h-[calc(100vh-8rem)] sm:max-h-[70vh] overflow-y-auto">
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
              
              <!-- Location Filter -->
              <div v-if="!ordersLoading && !ordersError && selectedClientOrders.length > 0 && availableLocations.length > 0" class="mb-4">
                <div class="flex flex-wrap gap-2 items-center">
                  <span class="text-sm font-medium text-gray-700">Filtro sipas Njësisë:</span>
                  <button 
                    @click="locationFilter = 'all'"
                    :class="locationFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors duration-200"
                  >
                    Të Gjitha
                  </button>
                  <button 
                    @click="locationFilter = 'no_location'"
                    :class="locationFilter === 'no_location' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors duration-200"
                  >
                    Pa Njësi
                  </button>
                  <button 
                    v-for="location in availableLocations"
                    :key="location"
                    @click="locationFilter = location"
                    :class="locationFilter === location ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors duration-200"
                  >
                    📍 {{ location }}
                  </button>
                </div>
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
                      <div v-if="order.location_unit_name" class="mt-1 text-xs text-gray-600">
                        <p><strong>Pika/Njësia:</strong> {{ order.location_unit_name }}</p>
                        <p v-if="order.location_street_number"><strong>Adresa:</strong> {{ order.location_street_number }}</p>
                        <p v-if="order.location_city"><strong>Vendi/Qyteti:</strong> {{ order.location_city }}</p>
                        <p v-if="order.location_phone"><strong>Telefon i Pikës:</strong> {{ order.location_phone }}</p>
                      </div>
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
      <div v-if="showEditOrderModal && editingOrder" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-2 sm:p-4 md:p-6">
        <div class="relative w-full max-w-4xl my-4 sm:my-8 px-0 sm:px-2">
          <div class="bg-white rounded-xl shadow-2xl overflow-hidden min-w-0">
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

            <div class="px-3 sm:px-6 py-4 sm:py-5 max-h-[calc(100vh-10rem)] sm:max-h-[80vh] overflow-y-auto">
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
                      {{ item.quantity }} Komplete × {{ item.pieces_per_package }}cp = {{ item.quantity * item.pieces_per_package }} copa
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

              <!-- Payment Controls -->
              <div class="mb-6 p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                <h4 class="font-semibold text-gray-900 mb-3">Pagesa</h4>
                <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                  <label class="flex items-center gap-2 cursor-pointer pt-1">
                    <input
                      type="checkbox"
                      v-model="editingOrder.is_paid"
                      class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                    >
                    <span class="text-sm font-medium text-gray-700">
                      {{ editingOrder.is_paid ? '✓ E Paguar' : 'E PaPaguar' }}
                    </span>
                  </label>
                  <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Data/Ora e pagesës</label>
                    <input
                      type="datetime-local"
                      v-model="editingOrder.paid_at_local"
                      :disabled="!editingOrder.is_paid"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white disabled:opacity-60"
                    >
                    <p class="text-xs text-gray-500 mt-1">
                      Nëse lihet bosh, do vendoset automatikisht koha aktuale kur ruhet si “E Paguar”.
                    </p>
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
  </AdminLayout>
</template>

<script>
import axios from 'axios'
import { adminStore } from '../../stores/adminStore'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { OFFICIAL_ORDER_EMAIL } from '../../config/site'

export default {
  name: 'AdminClients',
  components: {
    AdminLayout
  },
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
        password: '',
        is_active: true,
        locations: [] // Array për shumë pika/njësi
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
      locationFilter: 'all',
      paymentStatsData: null,
      showGroupedByLocation: false
    }
  },
  async mounted() {
    // Scroll to top when component mounts
    window.scrollTo({ top: 0, behavior: 'smooth' })
    
    await this.checkAuth()
    await this.loadClients()
    await this.loadAllOrders()
  },
  computed: {
    canManage() {
      return adminStore.canManage()
    },
    canViewSales() {
      return adminStore.canViewSales()
    },
    canManageProducts() {
      return adminStore.canManageProducts()
    },
    canManageStock() {
      return adminStore.canManageStock()
    },
    canViewTrash() {
      return adminStore.canViewTrash()
    },
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

      let orders = this.selectedClientOrders

      // Apply payment filter
      if (this.paymentFilter !== 'all') {
        orders = orders.filter(order => {
          if (this.paymentFilter === 'paid') {
            return order.is_paid === true || order.is_paid === 1
          }
          if (this.paymentFilter === 'unpaid') {
            return !order.is_paid || order.is_paid === 0 || order.is_paid === false
          }
          return true
        })
      }

      // Apply location filter
      if (this.locationFilter && this.locationFilter !== 'all') {
        orders = orders.filter(order => {
          if (this.locationFilter === 'no_location') {
            return !order.location_unit_name
          }
          return order.location_unit_name === this.locationFilter
        })
      }

      return orders
    },
    availableLocations() {
      if (!this.selectedClientOrders || this.selectedClientOrders.length === 0) {
        return []
      }
      
      const locations = new Set()
      this.selectedClientOrders.forEach(order => {
        if (order.location_unit_name) {
          locations.add(order.location_unit_name)
        }
      })
      
      return Array.from(locations).sort()
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
    // Logout is now handled by AdminLayout,
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
      // Load client with locations
      this.loadClientWithLocations(client.id)
    },
    async loadClientWithLocations(clientId) {
      try {
        const response = await axios.get(`/api/clients/${clientId}`)
        const client = response.data.data
        this.clientForm = {
          name: client.name || '',
          store_name: client.store_name || '',
          fiscal_number: client.fiscal_number || '',
          city: client.city || '',
          phone: client.phone || '',
          viber: client.viber || '',
          notes: client.notes || '',
          password: '',
          is_active: client.is_active !== undefined ? client.is_active : true,
          locations: client.locations && client.locations.length > 0 
            ? client.locations.map(loc => ({
                id: loc.id,
                unit_name: loc.unit_name || '',
                street_number: loc.street_number || '',
                phone: loc.phone || '',
                viber: loc.viber || '',
                notes: loc.notes || '',
                is_active: loc.is_active !== undefined ? loc.is_active : true
              }))
            : []
        }
        this.showAddModal = true
      } catch (error) {
        console.error('Error loading client:', error)
        // Fallback to basic client data
        const client = this.clients.find(c => c.id === clientId)
        if (client) {
          this.clientForm = { ...client, locations: [] }
          this.showAddModal = true
        }
      }
    },
    addLocation() {
      if (!this.clientForm.locations) {
        this.clientForm.locations = []
      }
      this.clientForm.locations.push({
        unit_name: '',
        street_number: '',
        phone: '',
        viber: '',
        notes: '',
        is_active: true
      })
    },
    removeLocation(index) {
      if (this.clientForm.locations && this.clientForm.locations.length > index) {
        this.clientForm.locations.splice(index, 1)
      }
    },
    async saveClient() {
      try {
        // Validate required fields
        if (!this.clientForm.name || !this.clientForm.name.trim()) {
          alert('Ju lutem plotësoni emrin e klientit!')
          return
        }
        
        if (!this.clientForm.store_name || !this.clientForm.store_name.trim()) {
          alert('Ju lutem plotësoni emrin e biznisit!')
          return
        }
        
        if (!this.clientForm.fiscal_number || !this.clientForm.fiscal_number.trim()) {
          alert('Ju lutem plotësoni numrin fiskal!')
          return
        }
        
        // Validate locations if provided
        if (this.clientForm.locations && this.clientForm.locations.length > 0) {
          for (let i = 0; i < this.clientForm.locations.length; i++) {
            const loc = this.clientForm.locations[i]
            if (!loc.unit_name || !loc.unit_name.trim()) {
              alert(`Ju lutem plotësoni emrin e pikës/njësisë ${i + 1}!`)
              return
            }
          }
        }
        
        // Prepare data with locations (send password only when set)
        const dataToSend = {
          ...this.clientForm,
          locations: (this.clientForm.locations || []).map(loc => ({
            ...loc,
            is_active: loc.is_active !== undefined ? loc.is_active : true
          }))
        }
        if (!dataToSend.password || !String(dataToSend.password).trim()) {
          delete dataToSend.password
        }
        
        if (this.editingClient) {
          await axios.put(`/api/clients/${this.editingClient.id}`, dataToSend)
        } else {
          await axios.post('/api/clients', dataToSend)
        }
        await this.loadClients()
        this.closeModal()
        alert('✅ Klienti u ruajt me sukses!')
      } catch (error) {
        console.error('Error saving client:', error)
        let errorMessage = 'Gabim në ruajtjen e klientit'
        
        if (error.response?.data?.errors) {
          const errors = error.response.data.errors
          const errorList = Object.entries(errors)
            .map(([field, messages]) => `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`)
            .join('\n')
          errorMessage = `Gabime në validim:\n${errorList}`
        } else if (error.response?.data?.message) {
          errorMessage = error.response.data.message
        }
        
        alert(errorMessage)
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
        password: '',
        is_active: true,
        locations: []
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
      this.locationFilter = 'all'
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
    /** Rreshti në DB është komplet vetëm kur sold_by_package dhe jo shitje me copë (unit_type cp) */
    orderItemIsPackageSale(item) {
      if (!item) return false
      const ut = (item.unit_type || '').toString().toLowerCase().trim()
      if (ut === 'cp' || ut === 'piece') return false
      return item.sold_by_package === true || item.sold_by_package === 1
    },
    /**
     * Llogarit sasinë në copë për një rresht porosie/fature.
     * Nëse për komplet të nominal nuk përputhet vlera me totalin e rreshtit, përdoret numri i nënkuptuar i copave (rreshta legacy).
     */
    orderItemInvoiceQty(item) {
      const q = parseFloat(item.quantity) || 0
      const ppk = parseInt(item.pieces_per_package, 10)
      const unitPrice = item.unit_price != null ? parseFloat(item.unit_price) : null
      const lineTotal = item.total_price != null ? parseFloat(item.total_price) : null
      let mode = this.orderItemIsPackageSale(item) && ppk > 0 ? 'package' : 'piece'
      let packageCount = q
      let pieceCount = mode === 'package' ? q * ppk : q
      const lineDiscount = parseFloat(item.discount_amount) || 0
      if (
        mode === 'package' &&
        lineDiscount < 0.01 &&
        unitPrice != null &&
        lineTotal != null &&
        !isNaN(unitPrice) &&
        !isNaN(lineTotal) &&
        unitPrice > 0
      ) {
        const expected = pieceCount * unitPrice
        if (Math.abs(expected - lineTotal) > 0.05) {
          const implied = Math.round(lineTotal / unitPrice)
          if (implied > 0 && Math.abs(implied * unitPrice - lineTotal) <= 0.05) {
            mode = 'piece'
            pieceCount = implied
          }
        }
      }
      return { mode, packageCount, ppk, pieceCount }
    },
    /** Rreshti i faturës: sasia e dukshme + njësia (kompletet zbërthehen në copë) */
    invoiceLineQtyParts(item, fmtQty) {
      const iq = this.orderItemInvoiceQty(item)
      if (iq.mode === 'package' && iq.ppk > 0) {
        const nbsp = '\u00A0'
        const q = iq.packageCount
        const total = iq.pieceCount
        const ppk = iq.ppk
        const html =
          '<span class="inv-qty-stack inv-qty-stack--pkg">' +
          '<span class="inv-qty-line inv-qty-line1">' + fmtQty(q) + nbsp + 'komplete</span>' +
          '<span class="inv-qty-line inv-qty-line2">×' + nbsp + ppk + nbsp + 'cp</span>' +
          '<span class="inv-qty-line inv-qty-line3">=' + nbsp + fmtQty(total) + nbsp + 'cp</span>' +
          '</span>'
        const plain = fmtQty(q) + ' komplete × ' + ppk + ' cp = ' + fmtQty(total) + ' cp'
        return { qtyHtml: html, qtyPlain: plain, unit: 'Copë' }
      }
      const pcs = iq.pieceCount
      return { qtyHtml: fmtQty(pcs), qtyPlain: fmtQty(pcs), unit: 'Copë' }
    },
    printOrder(order) {
      if (!order) return

      const createdAt = this.formatDate(order.created_at)
      const invoiceDateFormatted = this.formatDate(order.created_at)
      const isPaid = order.is_paid === true || order.is_paid === 1
      const hasVat = order.has_vat === true || order.has_vat === 1
      const vatRate = 18
      const fmtNum = (n) => (n != null && !isNaN(n) ? Number(n).toFixed(2) : '-')
      const fmtQty = (n) => {
        if (n == null || isNaN(n)) return '-'
        const num = Number(n)
        return Number.isInteger(num) ? String(num) : num.toFixed(2)
      }
      const amountBeforeVat = hasVat && order.total_amount ? parseFloat(order.total_amount) / 1.18 : (order.amount_before_vat != null ? parseFloat(order.amount_before_vat) : (order.total_amount ? parseFloat(order.total_amount) : null))
      const vatAmount = hasVat && order.total_amount ? parseFloat(order.total_amount) - (parseFloat(order.total_amount) / 1.18) : (order.vat_amount != null ? parseFloat(order.vat_amount) : 0)
      const totalItemDiscounts = (order.items || []).reduce((sum, item) => sum + (parseFloat(item.discount_amount) || 0), 0)
      const orderDiscount = parseFloat(order.discount_amount) || 0
      const hasDiscount = totalItemDiscounts > 0.009 || orderDiscount > 0.009
      const totalDiscountAmount = totalItemDiscounts + orderDiscount
      const valueBeforeDiscount = (order.subtotal != null ? parseFloat(order.subtotal) : null) || (order.items || []).reduce((s, i) => s + (parseFloat(i.total_price) || 0), 0)

      const invoiceItemRowCells = (item, idx) => {
        const barcode = (item.barcode != null && item.barcode !== '') ? String(item.barcode) : (item.product && item.product.barcode != null && item.product.barcode !== '' ? String(item.product.barcode) : '')
        const qp = this.invoiceLineQtyParts(item, fmtQty)
        const piecesForLine = this.orderItemInvoiceQty(item).pieceCount
        const unitPrice = item.unit_price != null ? parseFloat(item.unit_price) : null
        const lineTotal = item.total_price != null ? parseFloat(item.total_price) : (unitPrice ? unitPrice * piecesForLine : 0)
        const unitPriceNoVat = hasVat && unitPrice ? unitPrice / 1.18 : unitPrice
        const discountPct = 0
        const base = '<td class="inv-num">' + (idx + 1) + '</td>' +
          '<td class="inv-code">' + (barcode || '-') + '</td>' +
          '<td class="inv-desc">' + (item.product_name || '') + '</td>' +
          '<td class="inv-qty">' + qp.qtyHtml + '</td>' +
          '<td class="inv-unit">' + qp.unit + '</td>'
        if (hasVat) {
          let rest = '<td class="inv-num">' + (unitPriceNoVat != null ? fmtNum(unitPriceNoVat) : '-') + '</td>'
          if (hasDiscount) rest += '<td class="inv-num">' + fmtNum(discountPct) + '</td>'
          rest += '<td class="inv-num">' + vatRate + '</td>' +
            '<td class="inv-num">' + (unitPrice != null ? fmtNum(unitPrice) : '-') + '</td>' +
            '<td class="inv-num">' + (lineTotal > 0 ? fmtNum(lineTotal) : '-') + '</td>'
          return base + rest
        }
        let rest = ''
        if (hasDiscount) rest += '<td class="inv-num">' + fmtNum(discountPct) + '</td>'
        rest += '<td class="inv-num">' + (unitPrice != null ? fmtNum(unitPrice) : '-') + '</td>' +
          '<td class="inv-num">' + (lineTotal > 0 ? fmtNum(lineTotal) : '-') + '</td>'
        return base + rest
      }

      const itemsHeaderHtml = '<th>No</th><th>Barcode</th><th>Emertimi</th><th>Sasia</th><th>Njesia</th>' +
        (hasVat ? '<th>Çmimi pa TVSH</th>' : '') +
        (hasDiscount ? '<th>Rabati %</th>' : '') +
        (hasVat ? '<th>TVSH %</th><th>Çmimi me TVSH</th><th>Vlera me TVSH</th>' : '<th>Çmimi</th><th>Vlera</th>')

      const itemsRows = (order.items || [])
        .map((item, idx) => '<tbody class="inv-tbody-item" style="page-break-inside:avoid;break-inside:avoid;-webkit-region-break:avoid"><tr>' + invoiceItemRowCells(item, idx) + '</tr></tbody>')
        .join('')

      const buyerName = (order.business_name || order.customer_name || 'N/A').trim()
      const buyerAddress = order.location_street_number || ''
      const buyerCity = (order.location_city || order.city || 'N/A').trim()
      const buyerFiscal = (order.fiscal_number || '').trim() || '-'
      const expDate = invoiceDateFormatted

      const taxBase18 = amountBeforeVat != null ? fmtNum(amountBeforeVat) : '0.00'
      const taxVat18 = hasVat ? fmtNum(vatAmount) : '0.00'
      const taxVal18 = order.total_amount ? fmtNum(parseFloat(order.total_amount)) : '0.00'
      const valBeforeDiscount = valueBeforeDiscount != null ? fmtNum(valueBeforeDiscount) : '0.00'
      const discountVal = hasDiscount ? fmtNum(totalDiscountAmount) : '0.00'
      const valNoVat = amountBeforeVat != null ? fmtNum(amountBeforeVat) : '0.00'
      const paymentDone = isPaid ? (order.total_amount ? fmtNum(parseFloat(order.total_amount)) : '0.00') : '0.00'
      const totalForPay = order.total_amount ? fmtNum(parseFloat(order.total_amount)) : '-'
      const mbetjaVal = order.total_amount ? (isPaid ? '0.00' : fmtNum(parseFloat(order.total_amount))) : '-'
      const paymentBadgeHtml = isPaid
        ? '<span class="inv-paid inv-paid--yes">E Paguar</span>'
        : '<span class="inv-paid inv-paid--no">E PaPaguar</span>'

      const htmlContent = '<!DOCTYPE html><html lang="sq">' +
        '<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">' +
        '<title>Faturë ' + (order.order_number || 'N/A') + '</title>' +
        '<style>' +
        '*{box-sizing:border-box}' +
        'html,body{-webkit-print-color-adjust:exact;print-color-adjust:exact;color-adjust:exact}' +
        'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;padding:24px 32px;color:#111827;font-size:13px;line-height:1.4;max-width:900px;margin:0 auto;background:#fff}' +
        '.inv-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;margin-bottom:16px}' +
        '.inv-header{display:flex;flex-direction:column;gap:0;margin-bottom:20px;padding-bottom:16px;border-bottom:2px solid #0d9488}' +
        '.inv-header-row1{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;width:100%;margin-bottom:14px}' +
        '.inv-header-row2{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;width:100%}' +
        '.inv-header-address{text-align:right;flex:1;max-width:52%;display:flex;flex-direction:column;align-items:flex-end}' +
        '.inv-header-address .inv-title{margin-top:8px}.inv-header-address .inv-title:first-child{margin-top:0}' +
        '.inv-header-address>div:not(.inv-title){max-width:100%;word-break:break-word}' +
        '.inv-seller-contact{flex:1;min-width:200px;max-width:55%}' +
        '.inv-seller-contact .inv-title{margin-top:8px}.inv-seller-contact .inv-title:first-child{margin-top:0}' +
        '.inv-invoice-no-block{margin:0;padding:0;border:none;width:auto;max-width:280px;text-align:right;flex-shrink:0}' +
        '.inv-invoice-no-block .inv-nr{text-align:right;margin-top:4px}' +
        '.inv-title{font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px}' +
        '.inv-company{font-weight:700;font-size:18px;color:#0f766e;margin:0;padding:0;line-height:1.2;display:block;flex-shrink:0}' +
        '.inv-nr{text-align:right;font-weight:700;font-size:14px;color:#0d9488}' +
        '.inv-bleresi{background:#f8fafc;padding:12px 16px;border-radius:8px;margin-bottom:16px;border:1px solid #e2e8f0}' +
        '.inv-bleresi h3{font-size:12px;text-transform:uppercase;color:#64748b;margin:0 0 8px 0}' +
        '.inv-paid{display:inline-block;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:0.02em;border:1px solid transparent}' +
        '.inv-paid--yes{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}' +
        '.inv-paid--no{background:#fff7ed;color:#9a3412;border-color:#fed7aa}' +
        '.inv-meta{width:100%;border-collapse:collapse;margin-bottom:16px;font-size:12px}' +
        '.inv-meta th,.inv-meta td{border:1px solid #e2e8f0;padding:6px 10px;text-align:left}' +
        '.inv-meta th{background:#f1f5f9;font-weight:600;color:#475569}' +
        '.inv-table{width:100%;border-collapse:collapse;margin-bottom:16px;font-size:12px}' +
        '.inv-table th,.inv-table td{border:1px solid #e2e8f0;padding:6px 8px}' +
        '.inv-table th{background:#0d9488;color:#fff;font-weight:600;text-align:center}' +
        '.inv-table .inv-num{text-align:right}.inv-table .inv-desc{text-align:left}.inv-table .inv-code{text-align:center}' +
        '.inv-table .inv-qty{text-align:right;vertical-align:top}.inv-qty-stack{display:block;text-align:right}' +
        '.inv-qty-stack--pkg{display:flex;flex-direction:column;align-items:flex-end;gap:3px;text-align:right}' +
        '.inv-qty-stack--pkg .inv-qty-line{font-variant-numeric:tabular-nums;line-height:1.3;display:block}' +
        '.inv-qty-stack--pkg .inv-qty-line1{font-size:12px;font-weight:600;color:#0f172a;letter-spacing:-0.02em}' +
        '.inv-qty-stack--pkg .inv-qty-line2{font-size:11px;font-weight:400;color:#64748b}' +
        '.inv-qty-stack--pkg .inv-qty-line3{font-size:12px;font-weight:800;color:#0f172a;letter-spacing:-0.02em}' +
        '.inv-tax{width:100%;max-width:320px;margin-left:auto;border-collapse:collapse;font-size:12px;margin-bottom:12px}' +
        '.inv-tax th,.inv-tax td{border:1px solid #e2e8f0;padding:6px 10px;text-align:right}' +
        '.inv-tax th{background:#f1f5f9;font-weight:600}' +
        '.inv-totals{max-width:280px;margin-left:auto;font-size:13px}' +
        '.inv-totals .row{display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #e2e8f0}' +
        '.inv-totals .row.status{border-bottom:none;padding-bottom:6px}' +
        '.inv-totals .row.total{font-weight:700;font-size:15px;color:#0d9488;border-bottom:none;padding-top:8px;margin-top:4px;border-top:2px solid #0d9488}' +
        '.inv-footer{display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin-top:40px;padding-top:24px;border-top:2px solid #e2e8f0}' +
        '.inv-sig{min-width:200px;max-width:45%}' +
        '.inv-sig--left{text-align:left}' +
        '.inv-sig--right{text-align:right;margin-left:auto}' +
        '.inv-sig .line{border-top:1px solid #111827;padding-top:4px;margin-top:32px;font-weight:600;font-size:12px}' +
        '.inv-sig .sub{font-size:11px;color:#64748b;margin-top:4px}' +
        '.inv-bank{font-size:12px;color:#64748b;margin-top:16px}' +
        '@page{size:A4;margin:12mm}' +
        '@media print{' +
        'body{padding:0!important;max-width:100%!important;margin:0!important;background:#fff!important}' +
        '.inv-table-wrap{overflow:visible!important;margin-left:0!important;margin-right:0!important;padding:0!important;-webkit-overflow-scrolling:auto!important}' +
        '.inv-table{min-width:0!important;width:100%!important;page-break-inside:auto}' +
        '.inv-meta{page-break-inside:auto}' +
        'thead{display:table-header-group}' +
        'tbody.inv-tbody-item{break-inside:avoid!important;page-break-inside:avoid!important}' +
        'tbody.inv-tbody-item tr{break-inside:avoid!important;page-break-inside:avoid!important}' +
        'tbody.inv-tbody-item td{break-inside:avoid!important;page-break-inside:avoid!important}' +
        '.inv-bleresi,.inv-totals,.inv-tax,.inv-footer,.inv-bank,.inv-sig{break-inside:avoid;page-break-inside:avoid}' +
        '.inv-footer{flex-direction:row!important;justify-content:space-between!important;align-items:flex-end!important;gap:32px!important}' +
        '.inv-footer .inv-sig--right{margin-left:auto!important;text-align:right!important}' +
        '.inv-footer .inv-sig--left{text-align:left!important}' +
        '.inv-footer{border-top-color:#e2e8f0!important}' +
        '.inv-header{border-bottom-color:#0d9488!important}' +
        '.inv-table th{background:#0d9488!important;color:#fff!important}' +
        '.inv-meta th,.inv-tax th{background:#f1f5f9!important}' +
        '}' +
        '@media (max-width:768px){' +
        'body{padding:12px 16px;font-size:12px;max-width:100%}' +
        '.inv-header-row1{display:flex!important;flex-direction:row!important;justify-content:space-between!important;align-items:flex-start!important;gap:10px!important;margin-bottom:12px!important}' +
        '.inv-header-row2{display:flex!important;flex-direction:row!important;justify-content:space-between!important;align-items:flex-start!important;gap:10px!important}' +
        '.inv-company{font-size:15px!important;line-height:1.15!important;margin:0!important;max-width:44%!important;flex:0 1 auto!important;min-width:0!important;word-break:break-word!important}' +
        '.inv-header-address{flex:1!important;min-width:0!important;max-width:56%!important;text-align:right!important;align-items:flex-end!important;font-size:11px!important}' +
        '.inv-header-address .inv-title{font-size:10px!important;letter-spacing:0.03em!important}' +
        '.inv-seller-contact{flex:1!important;min-width:0!important;max-width:55%!important;font-size:11px!important}' +
        '.inv-invoice-no-block{flex:0 0 auto!important;max-width:45%!important;text-align:right!important;min-width:0!important}' +
        '.inv-invoice-no-block .inv-nr{text-align:right!important;font-size:13px!important}' +
        '.inv-invoice-no-block .inv-title{text-align:right!important}' +
        '.inv-meta{font-size:11px}.inv-meta th,.inv-meta td{padding:6px 8px}' +
        '.inv-table-wrap{margin-left:-16px;margin-right:-16px;padding:0 16px}' +
        '.inv-table{font-size:11px;min-width:720px}' +
        '.inv-table.inv-table--simple{min-width:480px}' +
        '.inv-table th,.inv-table td{padding:6px 4px}' +
        '.inv-tax{max-width:100%;font-size:11px}' +
        '.inv-totals{max-width:100%;font-size:12px}' +
        '.inv-footer{flex-direction:column;gap:24px;margin-top:24px;align-items:stretch}' +
        '.inv-sig{min-width:auto;max-width:100%;text-align:left}' +
        '.inv-sig--right{margin-left:0;text-align:left}.inv-sig .line{margin-top:16px}' +
        '.inv-qty-stack--pkg{gap:4px}' +
        '.inv-qty-stack--pkg .inv-qty-line1{font-size:11px;font-weight:600}' +
        '.inv-qty-stack--pkg .inv-qty-line2{font-size:10px;font-weight:400;color:#64748b}' +
        '.inv-qty-stack--pkg .inv-qty-line3{font-size:12px;font-weight:800;margin-top:1px}' +
        '}' +
        '</style></head><body>' +
        '<div class="inv-header">' +
        '<div class="inv-header-row1">' +
        '<h2 class="inv-company">Aron Trade</h2>' +
        '<div class="inv-header-address">' +
        '<div class="inv-title">Adresë</div><div>Ferizaj, Kosovë, Rruga Lidhja e Prizrenit</div>' +
        '<div class="inv-title">Nrb</div><div>' + (order.company_nrb || '—') + '</div>' +
        '<div class="inv-title">Tvsh</div><div>' + (order.company_tvsh || '—') + '</div>' +
        '</div></div>' +
        '<div class="inv-header-row2">' +
        '<div class="inv-seller-contact">' +
        '<div class="inv-title">Nrf / NIPT</div><div>' + (order.company_nrf || '—') + '</div>' +
        '<div class="inv-title">tel</div><div>+383 48 75 66 46 / +383 44 82 43 14</div>' +
        '<div class="inv-title">email</div><div>' + OFFICIAL_ORDER_EMAIL + '</div>' +
        '</div>' +
        '<div class="inv-invoice-no-block">' +
        '<div class="inv-title">Nr. Faturës</div>' +
        '<div class="inv-nr">' + (order.order_number || 'N/A') + '</div>' +
        '</div></div></div>' +
        '<div class="inv-bleresi">' +
        '<h3>Bleresi</h3>' +
        '<div><strong>' + buyerName + '</strong></div>' +
        (buyerAddress ? '<div>Adresa: ' + buyerAddress + '</div>' : '') +
        '<div>Qyteti: ' + buyerCity + '</div>' +
        '<div>No fiskal: ' + buyerFiscal + '</div>' +
        '<div>Numri unik: ' + buyerFiscal + '</div>' +
        (order.phone ? '<div>Telefon: ' + order.phone + '</div>' : '') +
        '</div>' +
        '<div class="inv-table-wrap"><table class="inv-meta">' +
        '<tr><th>Data fatura</th><th>Kushtet</th><th>Data e skadimit</th><th>User</th><th>Referenca</th><th>Lokacioni</th></tr>' +
        '<tr><td>' + invoiceDateFormatted + '</td><td></td><td>' + expDate + '</td><td>Klient</td><td></td><td>' + (order.location_unit_name || '-') + '</td></tr>' +
        '</table></div>' +
        '<div class="inv-table-wrap"><table class="inv-table' + (!hasVat && !hasDiscount ? ' inv-table--simple' : '') + '">' +
        '<thead><tr>' + itemsHeaderHtml + '</tr></thead>' + itemsRows + '</table></div>' +
        (hasVat
          ? '<table class="inv-tax">' +
            '<tr><th>Normat Tatimore</th><th>Baza</th><th>TVSH</th><th>Vlera</th></tr>' +
            '<tr><td>TVSH 0%</td><td>0.00</td><td>0.00</td><td>0.00</td></tr>' +
            '<tr><td>TVSH 18%</td><td>' + taxBase18 + '</td><td>' + taxVat18 + '</td><td>' + taxVal18 + '</td></tr>' +
            '</table>'
          : '') +
        '<div class="inv-totals">' +
        '<div class="row status"><span>Statusi</span><span>' + paymentBadgeHtml + '</span></div>' +
        (hasDiscount
          ? '<div class="row"><span>Vlera para zbritjes</span><span>' + valBeforeDiscount + '</span></div>' +
            '<div class="row"><span>Rabati</span><span>' + discountVal + '</span></div>'
          : '') +
        (hasVat
          ? '<div class="row"><span>Vlera pa TVSH</span><span>' + valNoVat + '</span></div>' +
            '<div class="row"><span>TVSH</span><span>' + taxVat18 + '</span></div>'
          : '') +
        '<div class="row total"><span>Vlera për pagesë (EUR)</span><span>' + totalForPay + '</span></div>' +
        '<div class="row"><span>Pagesa</span><span>' + paymentDone + '</span></div>' +
        '<div class="row"><span>Mbetja</span><span>' + mbetjaVal + '</span></div>' +
        '</div>' +
        '<div class="inv-footer">' +
        '<div class="inv-sig inv-sig--left"><div class="line">Dërgoi</div><div class="sub">Aron Trade</div></div>' +
        '<div class="inv-sig inv-sig--right"><div class="line">Pranoi</div><div class="sub">' + buyerName + '</div></div>' +
        '</div>' +
        '<div class="inv-bank">Llogaria bankare:</div>' +
        '</body></html>'

      const printWindow = window.open('', '_blank')
      if (!printWindow) {
        alert('Lejoni hapjen e dritareve të reja për të printuar porosinë.')
        return
      }
      printWindow.document.open()
      printWindow.document.write(htmlContent)
      printWindow.document.close()
      printWindow.focus()
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
        fiscal_number: clonedOrder.fiscal_number ? String(clonedOrder.fiscal_number).trim().toUpperCase() : '',
        paid_at_local: clonedOrder.paid_at ? this.toDatetimeLocalValue(clonedOrder.paid_at) : ''
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
          is_paid: !!this.editingOrder.is_paid,
          paid_at: this.editingOrder.is_paid
            ? (this.editingOrder.paid_at_local ? new Date(this.editingOrder.paid_at_local).toISOString() : new Date().toISOString())
            : null,
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
    },

    toDatetimeLocalValue(value) {
      if (!value) return ''
      const d = new Date(value)
      const pad = (n) => String(n).padStart(2, '0')
      return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
    }
  }
}
</script>

