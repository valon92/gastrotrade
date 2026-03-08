<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-8 text-center">
        Shporta e Blerjeve
      </h1>

      <div v-if="cartStore.items.length === 0" class="max-w-2xl mx-auto">
        <div class="text-center py-8">
          <div class="text-6xl mb-4">🛒</div>
          <h2 class="text-2xl font-semibold text-gray-700 mb-4">
            Shporta juaj është e zbrazët
          </h2>
          <p class="text-gray-600 mb-6">
            Shtoni produkte në shportë për të vazhduar me porosinë
          </p>
          <router-link to="/produktet" class="btn-primary text-lg inline-block">
            Shiko Produktet
          </router-link>
        </div>

        <!-- Identifikimi: kur i kyqur nuk shfaqet ftoja për identifikim -->
        <div class="mt-10 bg-white rounded-xl shadow-lg p-6 border border-gray-200">
          <h3 class="text-xl font-semibold text-gray-900 mb-2">
            Identifikimi
          </h3>
          <template v-if="!cartStore.client">
            <p class="text-sm text-gray-600 mb-4">
              Për të parë çmimet tuaja në shportë, identifikohu me <strong>email</strong> dhe <strong>fjalëkalim</strong> në faqen Kyçu.
            </p>
            <div class="flex flex-wrap gap-3">
              <router-link
                to="/kycu"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
              >
                Kyçu
              </router-link>
              <router-link
                to="/regjistrohu"
                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
              >
                Regjistrohu
              </router-link>
            </div>
          </template>
          <div v-else class="p-4 bg-green-50 border border-green-200 rounded-lg flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-green-800">
              ✅ I identifikuar: <strong>{{ cartStore.client.name || cartStore.client.store_name || cartStore.client.email }}</strong>
            </p>
            <div class="flex items-center gap-2 shrink-0">
              <button
                type="button"
                @click="openChangePasswordModal"
                class="px-3 py-1.5 text-sm font-medium text-primary-600 bg-white border border-primary-300 rounded-lg hover:bg-primary-50 transition-colors"
              >
                Ndrysho fjalëkalimin
              </button>
              <button
                type="button"
                @click="logoutClient"
                class="px-3 py-1.5 text-sm font-medium text-green-800 bg-white border border-green-300 rounded-lg hover:bg-green-100 transition-colors"
              >
                Dil
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2 space-y-4">
          <!-- Search Bar -->
          <div class="bg-white rounded-lg shadow-lg p-4">
            <div class="relative">
              <input 
                type="text"
                v-model="searchQuery"
                placeholder="Kërko produkte në shportë..."
                class="w-full px-4 py-3 pl-12 pr-4 text-base border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
              />
              <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">🔍</span>
              <button
                v-if="searchQuery"
                @click="clearSearch"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xl"
                title="Pastro kërkimin"
              >
                ✕
              </button>
            </div>
            <p v-if="searchQuery && filteredCartItems.length === 0" class="mt-2 text-sm text-gray-500">
              Nuk u gjet asnjë produkt me këtë kërkim.
            </p>
            <p v-else-if="searchQuery" class="mt-2 text-sm text-gray-500">
              U gjetën {{ filteredCartItems.length }} produkt(e)
            </p>
          </div>
          
          <div 
            v-for="item in filteredCartItems" 
            :key="item.id"
            class="bg-white rounded-lg shadow-lg p-6 flex flex-col sm:flex-row gap-4"
          >
            <div class="flex-shrink-0">
              <img 
                :src="item.image_path" 
                :alt="item.name"
                class="w-24 h-24 object-cover rounded-lg"
              />
            </div>
            <div class="flex-grow">
              <div class="flex justify-between items-start mb-2">
                <h3 class="text-xl font-semibold text-gray-900">
                  {{ item.name }}
                </h3>
                <div v-if="item.price" class="text-right">
                  <p class="text-sm text-gray-600">
                    {{ formatPrice(item.price) }}
                    <span class="text-xs">
                      /copë
                    </span>
                    <span v-if="item.sold_by_package && item.pieces_per_package" class="text-xs text-gray-500">
                      ({{ item.pieces_per_package }} copa/Komplete)
                    </span>
                  </p>
                  <p class="text-lg font-bold text-primary-600">
                    Total: {{ formatPrice(getItemTotal(item)) }}
                  </p>
                </div>
                <div v-else class="text-right text-gray-600">
                  <p>Çmimi sipas kërkesës</p>
                </div>
              </div>
              
              <div v-if="item.discount_amount > 0" class="mt-1 text-xs text-green-600 font-semibold">
                Zbritje e aplikuar nga administratori: -{{ formatPrice(item.discount_amount) }}
              </div>
              
              <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                <div class="flex items-center gap-4">
                  <label class="text-sm font-medium text-gray-700">
                    <span v-if="item.sold_by_package && item.pieces_per_package && canBuyByPieces(item)">
                      Copa:
                    </span>
                    <span v-else-if="item.sold_by_package && item.pieces_per_package">
                      Komplete:
                    </span>
                    <span v-else>
                      Sasia:
                    </span>
                  </label>
                  <div class="flex items-center border rounded-lg">
                    <button 
                      @click="decreaseQuantity(item.id)"
                      class="px-3 py-1 hover:bg-gray-100 transition-colors"
                    >
                      -
                    </button>
                    <input 
                      type="number" 
                      :value="item.sold_by_package && item.pieces_per_package && canBuyByPieces(item) ? getPiecesQuantity(item) : item.quantity" 
                      @input="updateQuantity(item.id, $event.target.value, item)"
                      :min="item.sold_by_package && item.pieces_per_package && canBuyByPieces(item) ? 1 : 1"
                      :step="item.sold_by_package && item.pieces_per_package && canBuyByPieces(item) ? 1 : 1"
                      class="w-20 text-center border-0 focus:ring-0"
                    />
                    <button 
                      @click="increaseQuantity(item.id)"
                      class="px-3 py-1 hover:bg-gray-100 transition-colors"
                    >
                      +
                    </button>
                  </div>
                </div>
                <div class="flex items-center justify-between gap-2 md:gap-4 flex-wrap w-full md:w-auto">
                  <div v-if="item.sold_by_package && item.pieces_per_package" class="text-sm text-gray-600">
                    <span v-if="canBuyByPieces(item)">
                      ({{ getPiecesQuantity(item) }} copa)
                    </span>
                    <span v-else>
                      ({{ item.quantity }} Komplete = {{ item.quantity * item.pieces_per_package }} copa)
                    </span>
                  </div>
                  <button 
                    @click="removeItem(item.id)"
                    class="text-red-600 hover:text-red-800 text-sm font-medium md:ml-auto"
                  >
                    Hiq
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Add More Products Button -->
          <div class="mt-6">
            <router-link 
              to="/produktet"
              class="flex items-center justify-center gap-2 w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition-colors duration-200"
            >
              <span class="text-xl">➕</span>
              <span>Shto Produkte Tjera</span>
            </router-link>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-lg p-6 sticky top-24">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
              Përmbledhje e Porosisë
            </h2>

            <!-- Shfaq vetëm kur klienti NUK është i kyqur -->
            <div v-if="!cartStore.client" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
              <p class="text-sm text-amber-900">
                <strong>Për të parë çmimet tuaja</strong>, identifikohu me <strong>email</strong> dhe <strong>fjalëkalim</strong> në faqen Kyçu.
              </p>
              <p class="text-xs text-amber-800/90 mt-2 pt-2 border-t border-amber-200/50">
                Nuk keni llogari? Regjistrohuni vetëm me email dhe fjalëkalim.
              </p>
              <div class="flex flex-wrap gap-2 mt-2">
                <router-link
                  to="/kycu"
                  class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary-600 text-white text-xs font-medium rounded-md hover:bg-primary-700 transition-colors"
                >
                  Kyçu
                </router-link>
                <router-link
                  to="/regjistrohu"
                  class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-md hover:bg-amber-700 transition-colors"
                >
                  Regjistrohu
                </router-link>
              </div>
            </div>
            
            <!-- Client Identification Notice -->
            <div v-if="hasClientPrices" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg flex flex-wrap items-center justify-between gap-3">
              <p class="text-sm text-green-800">
                ✅ I identifikuar: <strong>{{ cartStore.client.name || cartStore.client.store_name || cartStore.client.email }}</strong><br>
                Çmimet e personalizuara janë të aplikuara
              </p>
              <div class="flex items-center gap-2 shrink-0">
                <button
                  type="button"
                  @click="openChangePasswordModal"
                  class="px-3 py-1.5 text-sm font-medium text-primary-600 bg-white border border-primary-300 rounded-lg hover:bg-primary-50 transition-colors"
                >
                  Ndrysho fjalëkalimin
                </button>
                <button
                  type="button"
                  @click="logoutClient"
                  class="px-3 py-1.5 text-sm font-medium text-green-800 bg-white border border-green-300 rounded-lg hover:bg-green-100 transition-colors"
                >
                  Dil nga llogaria
                </button>
              </div>
            </div>
            
            <div v-else-if="identifyingClient" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
              <p class="text-sm text-blue-800">
                🔍 Duke identifikuar klientin...
              </p>
            </div>
            <!-- Success message - show when order is saved -->
            <transition name="fade">
              <div v-if="saveSuccess && savedOrder" class="mb-4 p-4 bg-green-50 border-2 border-green-400 rounded-lg shadow-md">
                <div class="flex flex-col gap-3">
                  <div class="flex items-center gap-2">
                    <span class="text-2xl">✅</span>
                    <p class="text-base font-semibold text-green-900">
                      Porosia #{{ savedOrder.order_number }} u ruajt me sukses!
                    </p>
                  </div>
                  <div class="flex flex-wrap gap-2">
                    <button 
                      @click="printOrder(savedOrder)"
                      class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-green-900 bg-white border border-green-300 rounded-md hover:bg-green-100 transition-colors duration-200"
                    >
                      🖨 Printo Porosinë
                    </button>
                    <a 
                      v-if="isAdmin"
                      :href="`/admin/sales?order=${savedOrder.order_number}`"
                      target="_blank"
                      class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-blue-900 bg-white border border-blue-300 rounded-md hover:bg-blue-100 transition-colors duration-200"
                    >
                      🔗 Shiko në Admin
                    </a>
                  </div>
                </div>
              </div>
            </transition>

            <!-- Të dhënat e klientit: vetëm informacion (plotësohen te Kyçu/Regjistrohu) -->
            <div class="mb-6 pb-6 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Të Dhënat e Porositësit
              </h3>
              <div v-if="!cartStore.client" class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">
                  Të dhënat plotësohen kur identifikoheni me email dhe fjalëkalim.
                </p>
                <router-link to="/kycu" class="inline-flex items-center gap-2 px-3 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                  Kyçu
                </router-link>
              </div>
              <div v-else class="space-y-3">
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg space-y-2 text-sm">
                  <p v-if="cartStore.client.email"><span class="text-gray-500 font-medium">Email:</span> {{ cartStore.client.email }}</p>
                  <p v-if="displayNameForOrder && displayNameForOrder !== cartStore.client.email"><span class="text-gray-500 font-medium">Emri:</span> {{ displayNameForOrder }}</p>
                  <p v-else-if="!cartStore.client.email"><span class="text-gray-500 font-medium">Emri:</span> {{ cartStore.client.name || '–' }}</p>
                  <p v-if="cartStore.client.fiscal_number"><span class="text-gray-500 font-medium">Nr. fiskal:</span> {{ cartStore.client.fiscal_number }}</p>
                  <p v-if="cartStore.client.phone || cartStore.client.viber"><span class="text-gray-500 font-medium">Telefon/Viber:</span> {{ cartStore.client.phone || cartStore.client.viber }}</p>
                </div>
                <!-- Fushat e detyrueshme për të aktivizuar butonat (Qyteti dhe/ose Emri i biznesit) -->
                <p class="text-xs text-amber-700 font-medium">Plotësoni të paktën njërën për të aktivizuar butonat e porosisë:</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label for="orderCity" class="block text-sm font-medium text-gray-700 mb-1">Qyteti</label>
                    <input
                      id="orderCity"
                      v-model.trim="customerData.city"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      placeholder="P.sh. Ferizaj, Prishtinë"
                      @input="persistCustomerData"
                    />
                  </div>
                  <div>
                    <label for="orderStoreName" class="block text-sm font-medium text-gray-700 mb-1">Emri i biznesit</label>
                    <input
                      id="orderStoreName"
                      v-model.trim="customerData.storeName"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      placeholder="P.sh. Dyqani im"
                      @input="persistCustomerData"
                    />
                  </div>
                </div>
                <!-- Zgjedhja e pikës/njësisë nëse ka më shumë se një -->
                <div v-if="clientLocations.length > 1">
                  <label for="locationSelect" class="block text-sm font-medium text-gray-700 mb-1">
                    Zgjidhni Pikën/Njësinë <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="locationSelect"
                    v-model="selectedLocationId"
                    @change="updateLocationData"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
                  >
                    <option value="">-- Zgjidhni Pikën/Njësinë --</option>
                    <option 
                      v-for="location in clientLocations" 
                      :key="location.id" 
                      :value="location.id"
                    >
                      {{ location.unit_name }}
                      <template v-if="location.street_number"> – {{ location.street_number }}</template>
                      <template v-if="location.notes"> ({{ location.notes }})</template>
                    </option>
                  </select>
                  <p class="mt-1 text-xs text-gray-500">
                    Kompania ka {{ clientLocations.length }} pika/njësi. Zgjidhni pikën për këtë porosi.
                  </p>
                </div>
                <div v-else-if="clientLocations.length === 1" class="p-2 bg-gray-50 border border-gray-200 rounded-lg">
                  <p class="text-xs text-gray-600">
                    📍 Pika/Njësia: <strong>{{ clientLocations[0].unit_name }}</strong>
                    <template v-if="clientLocations[0].street_number"> – {{ clientLocations[0].street_number }}</template>
                    <template v-if="clientLocations[0].notes"> ({{ clientLocations[0].notes }})</template>
                  </p>
                </div>
              </div>
            </div>
            
            <div class="space-y-4 mb-6">
              <div class="flex justify-between text-gray-700">
                <span>Total Produkte:</span>
                <span class="font-semibold">{{ cartStore.totalItems }}</span>
              </div>
              
              <!-- Show total price if available -->
              <div v-if="cartStore.totalPrice > 0" class="space-y-2">
                <div class="flex justify-between text-gray-700">
                  <span>Nëntotali:</span>
                  <span class="font-semibold">
                    {{ formatPrice(hasVat ? amountBeforeVat : cartStore.totalPrice) }}
                  </span>
                </div>
                
                <div v-if="cartStore.generalDiscountAmount > 0" class="pt-2 border-t border-gray-200">
                  <div class="flex justify-between text-red-600 text-sm">
                    <span>Zbritje e përgjithshme (aplikuar nga administratori):</span>
                    <span class="font-semibold">-{{ formatPrice(cartStore.generalDiscountAmount) }}</span>
                  </div>
                </div>
                
                <div v-if="hasVat && cartStore.totalPrice > 0" class="pt-2 border-t border-gray-200">
                  <div class="flex justify-between text-gray-700 text-sm">
                    <span>TVSH (18%):</span>
                    <span class="font-semibold">{{ formatPrice(vatAmount) }}</span>
                  </div>
                </div>
                
                <div class="flex justify-between text-gray-700 pt-2 border-t border-gray-200">
                  <span>Vlera Totale{{ hasVat ? ' me TVSH' : '' }}:</span>
                  <span class="font-bold text-primary-600 text-xl">
                    {{ formatPrice(hasVat ? totalWithVat : cartStore.totalPrice) }}
                  </span>
                </div>
                <!-- Show calculation breakdown for all products with prices -->
                <div class="text-xs text-gray-500 pt-2 border-t border-gray-200">
                  <p class="mb-1"><strong>Si llogaritet:</strong></p>
                  <div 
                    v-for="item in cartStore.items.filter(i => i.price)" 
                    :key="item.id" 
                    class="text-xs mb-1"
                  >
                    <div>
                      <span v-if="item.sold_by_package && item.pieces_per_package && canBuyByPieces(item)">
                        • {{ item.name }}: {{ formatPrice(item.price) }} × {{ getPiecesQuantity(item) }}copa = {{ formatPrice(getItemBaseTotal(item)) }}
                      </span>
                      <span v-else-if="item.sold_by_package && item.pieces_per_package">
                        • {{ item.name }}: {{ formatPrice(item.price) }} × {{ item.quantity }}(komplete) × {{ item.pieces_per_package }}cp = {{ formatPrice(getItemBaseTotal(item)) }}
                      </span>
                      <span v-else>
                        • {{ item.name }}: {{ formatPrice(item.price) }} × {{ item.quantity }} = {{ formatPrice(getItemBaseTotal(item)) }}
                      </span>
                    </div>
                    <div v-if="item.discount_amount > 0" class="pl-4 text-red-600">
                      − Zbritje: {{ formatPrice(item.discount_amount) }}
                    </div>
                    <div class="pl-4 font-semibold text-gray-700">
                      = {{ formatPrice(getItemTotal(item)) }}
                    </div>
                  </div>
                  <div v-if="cartStore.generalDiscountAmount > 0" class="mt-1 text-red-600">
                    Zbritje e përgjithshme: -{{ formatPrice(cartStore.generalDiscountAmount) }}
                  </div>
                </div>
              </div>
              
              <!-- Show message if no prices available -->
              <div v-else class="text-gray-600 text-sm">
                <p>Çmimi sipas kërkesës për të gjitha produktet</p>
                <p v-if="!cartStore.client" class="text-xs mt-2 text-gray-500">
                  <router-link to="/kycu" class="text-primary-600 hover:underline font-medium">Identifikohu</router-link> me email dhe fjalëkalim për të parë çmimet tuaja.
                </p>
                <p v-else class="text-xs mt-2 text-gray-500">
                  Çmimet për ju nuk janë akoma të caktuara nga administratori.
                </p>
              </div>
            </div>

            <div class="space-y-3">
              <!-- VAT Toggle -->
              <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input 
                    type="checkbox" 
                    v-model="hasVat"
                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  >
                  <span class="text-sm font-medium text-gray-700">
                    {{ hasVat ? '✓ Faturë me TVSH' : 'Faturë pa TVSH' }}
                  </span>
                </label>
                <p class="text-xs text-gray-500 mt-1">
                  Shënoni nëse faktura duhet të jetë me TVSH (18%)
                </p>
                <div v-if="hasVat && cartStore.totalPrice > 0" class="mt-2 text-xs text-gray-600">
                  <p><strong>Shuma para TVSH:</strong> {{ formatPrice(amountBeforeVat) }}</p>
                  <p><strong>TVSH (18%):</strong> {{ formatPrice(vatAmount) }}</p>
                  <p><strong>Totali me TVSH:</strong> {{ formatPrice(totalWithVat) }}</p>
                </div>
              </div>

              <!-- Pse butonat nuk punojnë? -->
              <div 
                v-if="(!isFormValid || cartStore.items.length === 0) && !savingOrder && !submittingOrder" 
                class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800"
              >
                <p class="font-medium mb-1">Për të aktivizuar butonat më poshtë:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                  <li v-if="!cartStore.client">
                    <router-link to="/kycu" class="underline font-medium">Identifikohu</router-link> me email dhe fjalëkalim (faqja Kyçu).
                  </li>
                  <li v-else-if="!((cartStore.client.name || cartStore.client.email || customerData.name?.trim()) && (cartStore.client.city || cartStore.client.store_name || customerData.city?.trim() || customerData.storeName?.trim()))">
                    Plotëso <strong>Qyteti</strong> dhe/ose <strong>Emri i biznesit</strong> në fushat «Të dhënat e porositësit» më sipër (mjafton njëra).
                  </li>
                  <li v-else-if="clientLocations.length > 1 && !selectedLocationId">
                    Zgjidh pikën / njësinë e dërgimit nga lista.
                  </li>
                  <li v-if="cartStore.items.length === 0">
                    Shto të paktën një produkt në shportë.
                  </li>
                </ul>
              </div>
              
              <button 
                @click="printCurrentOrder"
                :disabled="!isFormValid || cartStore.items.length === 0"
                :title="(!isFormValid || cartStore.items.length === 0) ? 'Identifikohu, plotëso të dhënat dhe shto produkte në shportë' : 'Printo faturën'"
                :class="[
                  'w-full font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2',
                  (!isFormValid || cartStore.items.length === 0)
                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                    : 'bg-purple-600 hover:bg-purple-700 text-white'
                ]"
              >
                🖨 Printo Faturën
              </button>
              <button 
                @click="submitOrder"
                :disabled="!isFormValid || savingOrder || submittingOrder || cartStore.items.length === 0"
                :title="(!isFormValid || cartStore.items.length === 0) ? 'Identifikohu, plotëso të dhënat dhe shto produkte në shportë' : 'Ruaj dhe shko te dërgimi (Viber/email)'"
                :class="[
                  'w-full font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2',
                  (!isFormValid || savingOrder || submittingOrder || cartStore.items.length === 0)
                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                    : 'bg-green-600 hover:bg-green-700 text-white shadow-lg'
                ]"
              >
                {{ submittingOrder ? 'Duke dërguar...' : '📤 Dergo Porosin' }}
              </button>
              
              <button 
                @click="saveOrder"
                :disabled="!isFormValid || savingOrder || submittingOrder || cartStore.items.length === 0"
                :title="(!isFormValid || cartStore.items.length === 0) ? 'Identifikohu, plotëso të dhënat dhe shto produkte në shportë' : 'Ruaj porosinë në sistem'"
                :class="[
                  'w-full font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2',
                  (!isFormValid || savingOrder || submittingOrder || cartStore.items.length === 0)
                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                    : 'bg-blue-600 hover:bg-blue-700 text-white'
                ]"
              >
                {{ savingOrder ? 'Duke ruajtur...' : '💾 Ruaj Porosinë' }}
              </button>

              <button 
                @click="clearCart"
                class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors duration-200"
              >
                Pastro Shportën
              </button>
            </div>

            <div class="mt-8">
              <h3 class="text-lg font-semibold text-gray-900 mb-3">
                Historia e Porosive
              </h3>
              <div v-if="!cartStore.client" class="text-sm text-gray-600">
                <router-link to="/kycu" class="text-primary-600 hover:underline">Identifikohu</router-link> për të parë porositë e ruajtura.
              </div>
              <div v-else>
                <div v-if="historyLoading" class="flex items-center gap-2 text-sm text-gray-600">
                  <span class="inline-block h-4 w-4 border-2 border-primary-600 border-t-transparent rounded-full animate-spin"></span>
                  Duke ngarkuar historinë e porosive...
                </div>
                <div v-else-if="historyError" class="text-sm text-red-600">
                  {{ historyError }}
                </div>
                <div v-else-if="ordersHistory.length === 0" class="text-sm text-gray-600">
                  Nuk ka porosi të ruajtura për këtë klient ende.
                </div>
                <div v-else class="space-y-4">
                  <div 
                    v-for="(order, index) in uniqueOrdersHistory" 
                    :key="`order-${order.id}-${index}`" 
                    class="border border-gray-200 rounded-lg p-4"
                  >
                    <div class="flex justify-between text-sm text-gray-500">
                      <span>{{ formatDate(order.created_at) }}</span>
                      <span class="font-semibold text-gray-900">#{{ order.order_number }}</span>
                    </div>
                    <div class="mt-2 text-sm text-gray-700">
                      <p><strong>Produkte:</strong> {{ order.total_items }}</p>
                      <p><strong>Vlera Totale:</strong> {{ order.total_amount ? formatPrice(order.total_amount) : 'Sipas kërkesës' }}</p>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                      <button 
                        @click="printOrder(order)"
                        class="px-3 py-2 text-sm rounded-md border border-primary-200 text-primary-700 hover:bg-primary-50 transition-colors duration-200"
                      >
                        🖨 Printo
                      </button>
                    </div>
                    <details class="mt-3 text-sm text-gray-600">
                      <summary class="cursor-pointer text-primary-600">Shiko produktet</summary>
                      <ul class="mt-2 space-y-1 list-disc list-inside">
                        <li v-for="item in order.items" :key="item.id">
                          {{ item.product_name }} — {{ formatOrderItemQuantity(item) }}
                          <span v-if="item.unit_price">
                            · {{ formatPrice(item.unit_price) }}
                          </span>
                        </li>
                      </ul>
                    </details>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
              <p class="text-sm text-gray-600 text-center">
                Ose na kontaktoni direkt:<br>
                📞 048 75 66 46 / 044 82 43 14<br>
                📧 svalon95@gmail.com
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Ndrysho fjalëkalimin (vetëm për klientët e kyqur me email) -->
    <div
      v-if="showChangePasswordModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
      @click.self="closeChangePasswordModal"
    >
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ndrysho fjalëkalimin</h3>
        <form @submit.prevent="submitChangePassword" class="space-y-4">
          <div v-if="changePasswordError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            {{ changePasswordError }}
          </div>
          <div v-if="Object.keys(changePasswordErrors).length" class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
            <ul class="list-disc list-inside space-y-1">
              <li v-for="(msgs, field) in changePasswordErrors" :key="field">
                {{ Array.isArray(msgs) ? msgs[0] : msgs }}
              </li>
            </ul>
          </div>
          <div v-if="changePasswordSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
            Fjalëkalimi u ndryshua me sukses.
          </div>
          <div>
            <label for="cp-current" class="block text-sm font-medium text-gray-700 mb-1">Fjalëkalimi aktual <span class="text-red-500">*</span></label>
            <input
              id="cp-current"
              v-model="changePasswordForm.current_password"
              type="password"
              required
              autocomplete="current-password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="Fjalëkalimi aktual"
            />
          </div>
          <div>
            <label for="cp-new" class="block text-sm font-medium text-gray-700 mb-1">Fjalëkalimi i ri <span class="text-red-500">*</span></label>
            <input
              id="cp-new"
              v-model="changePasswordForm.password"
              type="password"
              required
              minlength="6"
              autocomplete="new-password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="Të paktën 6 karaktere"
            />
          </div>
          <div>
            <label for="cp-confirm" class="block text-sm font-medium text-gray-700 mb-1">Konfirmo fjalëkalimin e ri <span class="text-red-500">*</span></label>
            <input
              id="cp-confirm"
              v-model="changePasswordForm.password_confirmation"
              type="password"
              required
              autocomplete="new-password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="Përsëritni fjalëkalimin e ri"
            />
          </div>
          <div class="flex gap-3 pt-2">
            <button
              type="button"
              @click="closeChangePasswordModal"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
            >
              Anulo
            </button>
            <button
              type="submit"
              :disabled="changePasswordLoading"
              class="flex-1 px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ changePasswordLoading ? 'Duke ruajtur...' : 'Ruaj' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Faturë në faqe (mobile + fallback): butonat Ndaj/Printo në header që janë gjithmonë të klikueshëm -->
    <Teleport to="body">
      <Transition name="invoice-modal">
        <div
          v-if="showInvoiceModal"
          class="fixed inset-0 z-[9999] flex flex-col bg-white"
          role="dialog"
          aria-label="Faturë"
        >
          <div class="flex items-center justify-between flex-shrink-0 px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Faturë</h2>
            <button
              type="button"
              @click="closeInvoiceModal"
              class="p-2 rounded-lg text-gray-600 hover:bg-gray-200 transition-colors"
              aria-label="Mbyll"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>
          <iframe
            v-if="invoiceHtml"
            :srcdoc="invoiceHtml"
            class="flex-1 w-full border-0 min-h-0"
            title="Faturë"
            sandbox="allow-scripts allow-same-origin allow-popups"
          />
          <div class="flex-shrink-0 px-3 py-3 border-t border-gray-200 bg-gray-50 flex flex-wrap gap-2 justify-center">
            <button
              type="button"
              @click="shareInvoiceViber"
              class="inline-flex items-center justify-center gap-1.5 min-h-[44px] px-4 py-2.5 rounded-xl text-white text-sm font-medium shadow-sm hover:opacity-90 active:scale-[0.98] transition-all cursor-pointer touch-manipulation select-none"
              style="background:#7360F2"
            >
              💬 Viber
            </button>
            <button
              type="button"
              @click="shareInvoiceWhatsApp"
              class="inline-flex items-center justify-center gap-1.5 min-h-[44px] px-4 py-2.5 rounded-xl text-white text-sm font-medium shadow-sm hover:opacity-90 active:scale-[0.98] transition-all cursor-pointer touch-manipulation select-none"
              style="background:#25D366"
            >
              📱 WhatsApp
            </button>
            <button
              type="button"
              @click="shareInvoiceGmail"
              class="inline-flex items-center justify-center gap-1.5 min-h-[44px] px-4 py-2.5 rounded-xl text-white text-sm font-medium shadow-sm hover:opacity-90 active:scale-[0.98] transition-all cursor-pointer touch-manipulation select-none"
              style="background:#EA4335"
            >
              📧 Gmail
            </button>
            <button
              type="button"
              @click="printInvoiceFromModal"
              class="inline-flex items-center justify-center gap-1.5 min-h-[44px] px-4 py-2.5 rounded-xl bg-gray-600 text-white text-sm font-medium shadow-sm hover:bg-gray-700 active:scale-[0.98] transition-colors cursor-pointer touch-manipulation select-none"
            >
              🖨 Printo
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script>
import cartStore from '../store/cart'
import axios from 'axios'

export default {
  name: 'Cart',
  data() {
    return {
      cartStore: cartStore,
      customerData: {
        name: '',
        storeName: '',
        fiscalNumber: '',
        city: '',
        phone: '',
        password: ''
      },
      showPassword: false,
      selectedLocationId: null,
      clientLocations: [],
      identifyingClient: false,
      savingOrder: false,
      submittingOrder: false,
      saveSuccess: false,
      savedOrder: null,
      hasVat: false,
      ordersHistory: [],
      historyLoading: false,
      historyError: null,
      phoneDebounce: null,
      orderHistoryLoaded: false,
      searchQuery: '',
      pendingOrderNumber: null,
      showChangePasswordModal: false,
      changePasswordForm: {
        current_password: '',
        password: '',
        password_confirmation: ''
      },
      changePasswordError: '',
      changePasswordErrors: {},
      changePasswordSuccess: false,
      changePasswordLoading: false,
      showInvoiceModal: false,
      invoiceHtml: '',
      invoiceShareText: '',
      invoiceOrderNumber: ''
    }
  },
  computed: {
    isAdmin() {
      return !!localStorage.getItem('admin_token')
    },
    isFormValid() {
      if (!this.cartStore.client) return false
      const c = this.cartStore.client
      const city = typeof this.customerData.city === 'string' ? this.customerData.city.trim() : ''
      const storeName = typeof this.customerData.storeName === 'string' ? this.customerData.storeName.trim() : ''
      const hasName = !!(c.name || c.email || (this.customerData.name && String(this.customerData.name).trim()))
      const hasCityOrBusiness = !!(c.city || c.store_name || city || storeName)
      const hasLocation = this.clientLocations.length <= 1 || (this.clientLocations.length > 1 && this.selectedLocationId)
      return hasName && hasCityOrBusiness && hasLocation
    },
    hasClientPrices() {
      return this.cartStore.client && Object.keys(this.cartStore.clientPrices).length > 0
    },
    /** Emri për të shfaqur te "Të dhënat e porositësit" (jo email-in, që shfaqet veç) */
    displayNameForOrder() {
      if (!this.cartStore.client) return ''
      const c = this.cartStore.client
      const fromForm = this.customerData.name && String(this.customerData.name).trim()
      if (fromForm) return this.customerData.name.trim()
      return c.name || c.store_name || ''
    },
    vatAmount() {
      if (!this.hasVat || !this.cartStore.totalPrice || this.cartStore.totalPrice <= 0) {
        return 0
      }
      // Totali aktual përfshin TVSH-në
      // TVSH = Totali me TVSH / 6.5555
      return this.cartStore.totalPrice / 6.5555
    },
    amountBeforeVat() {
      if (!this.hasVat || !this.cartStore.totalPrice || this.cartStore.totalPrice <= 0) {
        return this.cartStore.totalPrice
      }
      // Shuma para TVSH = Totali me TVSH - TVSH
      return this.cartStore.totalPrice - this.vatAmount
    },
    totalWithVat() {
      // Totali me TVSH është totali aktual kur TVSH është e aktivizuar
      if (!this.hasVat || !this.cartStore.totalPrice || this.cartStore.totalPrice <= 0) {
        return this.cartStore.totalPrice
      }
      return this.cartStore.totalPrice
    },
    uniqueOrdersHistory() {
      // Remove duplicates by order ID
      const seen = new Set()
      return this.ordersHistory.filter(order => {
        if (seen.has(order.id)) {
          return false
        }
        seen.add(order.id)
        return true
      })
    },
    filteredCartItems() {
      if (!this.searchQuery || this.searchQuery.trim() === '') {
        return this.cartStore.items
      }
      
      const query = this.searchQuery.toLowerCase().trim()
      return this.cartStore.items.filter(item => {
        return item.name && item.name.toLowerCase().includes(query)
      })
    }
  },
  watch: {
    'customerData.fiscalNumber'(newFiscal) {
      if (typeof newFiscal === 'string') {
        const normalized = newFiscal.toUpperCase().replace(/\s+/g, '')
        if (normalized !== newFiscal) this.customerData.fiscalNumber = normalized
      }
    }
  },
  async mounted() {
    // Initialize item discounts
    this.cartStore.items.forEach(item => {
      if (!item.discount_amount) item.discount_amount = 0
      if (!item.discount_type) item.discount_type = ''
      if (!item.discount_value) item.discount_value = 0
    })
    
    // Scroll to top when component is mounted
    window.scrollTo({ top: 0, behavior: 'smooth' })
    
    const savedData = localStorage.getItem('gastrotrade_customer_data')
    if (savedData) {
      try {
        const parsed = JSON.parse(savedData)
        this.customerData = { ...this.customerData, ...parsed }
      } catch (error) {
        console.error('Error loading saved customer data:', error)
      }
    }

    // Rifresko të dhënat e klientit nga API (që të merren ndryshimet nga admin: qytet, emër biznesi)
    const clientToken = localStorage.getItem('client_token')
    if (clientToken) {
      try {
        const res = await axios.get('/api/client/me')
        if (res.data.success && res.data.data) {
          await this.cartStore.setClient(res.data.data)
        }
      } catch (_) {
        // Token i pavlefshëm – mbaj të dhënat ekzistuese
      }
    }

    // Plotëso nga klienti i identifikuar (pas rifreskimit nga API ose nga localStorage)
    if (this.cartStore.client) {
      const c = this.cartStore.client
      if (!this.customerData.name?.trim()) this.customerData.name = c.name || c.store_name || c.email || ''
      // Prefero të dhënat nga admin (qytet, emër biznesi) që të aktivizohen butonat
      if (c.store_name?.trim()) this.customerData.storeName = c.store_name
      else if (!this.customerData.storeName?.trim()) this.customerData.storeName = ''
      if (c.city?.trim()) this.customerData.city = c.city
      else if (!this.customerData.city?.trim()) this.customerData.city = ''
      if (!this.customerData.fiscalNumber?.trim()) this.customerData.fiscalNumber = c.fiscal_number || ''
      if (!this.customerData.phone?.trim()) this.customerData.phone = c.phone || c.viber || ''
      this.persistCustomerData()
      if (c.locations && Array.isArray(c.locations) && c.locations.length > 0) {
        this.clientLocations = c.locations.filter(loc => loc && (loc.is_active !== false && loc.is_active !== 0))
        if (this.clientLocations.length === 1) this.selectedLocationId = this.clientLocations[0].id
      }
      if (!this.historyLoading) this.loadOrderHistory(null, c.id)
    }

    // Hap modal "Ndrysho fjalëkalimin" nëse erdhi nga Navbar (link me ?ndrysho=1)
    if (this.$route.query.ndrysho === '1' && this.cartStore.client) {
      this.$nextTick(() => {
        this.openChangePasswordModal()
      })
      this.$router.replace({ path: '/shporta', query: {} })
    }
  },
  beforeUnmount() {
    if (this.phoneDebounce) {
      clearTimeout(this.phoneDebounce)
    }
  },
  methods: {
    formatPrice(price) {
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
    formatOrderItemQuantity(item) {
      if (!item) return ''
      if (item.sold_by_package && item.pieces_per_package) {
        const totalPieces = item.quantity * item.pieces_per_package
        return `${item.quantity} komplete (${totalPieces} copa)`
      }
      return `${item.quantity} copë`
    },
    getItemBaseTotal(item) {
      if (!item.price) return 0
      if (this.canBuyByPieces(item) && item.actual_pieces) {
        return item.price * item.actual_pieces
      }
      if (item.sold_by_package && item.pieces_per_package) {
        return item.price * item.quantity * item.pieces_per_package
      }
      return item.price * item.quantity
    },
    getItemTotal(item) {
      if (!item.price) return 0
      
      const subtotal = this.getItemBaseTotal(item)
      // Apply item discount
      const discount = item.discount_amount || 0
      return Math.max(0, subtotal - discount)
    },
    updateGeneralDiscount() {
      this.cartStore.generalDiscountType = this.generalDiscountType
      this.cartStore.generalDiscountValue = this.generalDiscountValue
      this.cartStore.calculateGeneralDiscount()
    },
    increaseQuantity(productId) {
      const item = this.cartStore.items.find(item => item.id === productId)
      if (item) {
        if (this.canBuyByPieces(item) && item.sold_by_package && item.pieces_per_package) {
          // Increase by 1 piece
          const currentPieces = item.actual_pieces || (item.quantity * item.pieces_per_package)
          const newPieces = currentPieces + 1
          const newPackages = Math.ceil(newPieces / item.pieces_per_package)
          this.cartStore.updateQuantity(productId, newPackages)
          item.actual_pieces = newPieces
        } else {
          this.cartStore.updateQuantity(productId, item.quantity + 1)
        }
      }
    },
    decreaseQuantity(productId) {
      const item = this.cartStore.items.find(item => item.id === productId)
      if (item) {
        if (this.canBuyByPieces(item) && item.sold_by_package && item.pieces_per_package) {
          // Decrease by 1 piece
          const currentPieces = item.actual_pieces || (item.quantity * item.pieces_per_package)
          if (currentPieces > 1) {
            const newPieces = currentPieces - 1
            const newPackages = Math.ceil(newPieces / item.pieces_per_package)
            this.cartStore.updateQuantity(productId, newPackages)
            item.actual_pieces = newPieces
          } else {
            this.cartStore.removeItem(productId)
          }
        } else {
          if (item.quantity > 1) {
            this.cartStore.updateQuantity(productId, item.quantity - 1)
          } else {
            this.cartStore.removeItem(productId)
          }
        }
      }
    },
    updateQuantity(productId, quantity, item) {
      const qty = parseInt(quantity)
      if (qty > 0 && item) {
        // If client can buy by pieces and product is sold by package
        if (this.canBuyByPieces(item) && item.sold_by_package && item.pieces_per_package) {
          // Convert pieces to packages (round up)
          const packages = Math.ceil(qty / item.pieces_per_package)
          this.cartStore.updateQuantity(productId, packages)
          // Store the actual pieces quantity in the item
          item.actual_pieces = qty
        } else {
          this.cartStore.updateQuantity(productId, qty)
        }
      }
    },
    canBuyByPieces(item) {
      // Check if this specific product allows piece sales for this client
      return this.cartStore.client && this.cartStore.clientPieceSales[item.id] && item.sold_by_package && item.pieces_per_package
    },
    getPiecesQuantity(item) {
      if (this.canBuyByPieces(item)) {
        // If we have stored actual pieces, use that, otherwise calculate from packages
        return item.actual_pieces || (item.quantity * item.pieces_per_package)
      }
      return item.quantity * (item.pieces_per_package || 1)
    },
    removeItem(productId) {
      if (confirm('A jeni të sigurt që dëshironi të hiqni këtë produkt nga shporta?')) {
        this.cartStore.removeItem(productId)
      }
    },
    clearCart() {
      if (confirm('A jeni të sigurt që dëshironi të pastroni të gjithë shportën?')) {
        this.cartStore.clear()
        this.cartStore.clearClient()
        this.savedOrder = null
        this.saveSuccess = false
        this.customerData = {
          name: '',
          storeName: '',
          fiscalNumber: '',
          city: '',
          phone: '',
          password: ''
        }
        this.clientLocations = []
        this.selectedLocationId = null
        localStorage.removeItem('gastrotrade_customer_data')
        localStorage.removeItem('gastrotrade_order_data')
      }
    },
    openChangePasswordModal() {
      this.showChangePasswordModal = true
      this.changePasswordError = ''
      this.changePasswordErrors = {}
      this.changePasswordSuccess = false
      this.changePasswordForm = { current_password: '', password: '', password_confirmation: '' }
    },
    closeChangePasswordModal() {
      this.showChangePasswordModal = false
      this.changePasswordError = ''
      this.changePasswordErrors = {}
      this.changePasswordForm = { current_password: '', password: '', password_confirmation: '' }
    },
    async submitChangePassword() {
      this.changePasswordError = ''
      this.changePasswordErrors = {}
      this.changePasswordSuccess = false
      this.changePasswordLoading = true
      const token = localStorage.getItem('client_token')
      if (!token) {
        this.changePasswordError = 'Duhet të jeni i kyqur.'
        this.changePasswordLoading = false
        return
      }
      try {
        const res = await axios.put('/api/client/password', this.changePasswordForm, {
          headers: { Authorization: `Bearer ${token}` }
        })
        if (res.data.success) {
          this.changePasswordSuccess = true
          setTimeout(() => {
            this.closeChangePasswordModal()
          }, 1500)
        }
      } catch (e) {
        if (e.response?.data?.errors) this.changePasswordErrors = e.response.data.errors
        if (e.response?.data?.message) this.changePasswordError = e.response.data.message
        if (!this.changePasswordError && e.message) this.changePasswordError = 'Nuk u ndryshua fjalëkalimi. Provoni përsëri.'
      } finally {
        this.changePasswordLoading = false
      }
    },
    /** Dil nga llogaria e klientit – i njëjti sistem për të gjithë klientët. */
    logoutClient() {
      this.cartStore.logoutSession()
      this.clientLocations = []
      this.selectedLocationId = null
      this.customerData = {
        name: '',
        storeName: '',
        fiscalNumber: '',
        city: '',
        phone: '',
        password: ''
      }
      localStorage.removeItem('gastrotrade_customer_data')
      this.ordersHistory = []
      this.orderHistoryLoaded = false
    },
    scheduleClientIdentification() {
      if (this.phoneDebounce) {
        clearTimeout(this.phoneDebounce)
      }

      const businessName = (this.customerData.storeName || '').trim()
      const fiscalNumber = (this.customerData.fiscalNumber || '').trim()

      this.persistCustomerData()

      if (businessName.length < 2 || fiscalNumber.length < 3) {
        this.ordersHistory = []
        this.orderHistoryLoaded = false
        this.cartStore.clearClient()
        return
      }

      this.persistCustomerData()

      this.phoneDebounce = setTimeout(() => {
        this.identifyClient(businessName, fiscalNumber)
      }, 600)
    },
    async identifyClient(businessName, fiscalNumber) {
      if (!businessName || !fiscalNumber) {
        this.cartStore.clearClient()
        return
      }

      if (this.identifyingClient) {
        return
      }

      this.identifyingClient = true
      try {
        const payload = {
          business_name: businessName.trim(),
          fiscal_number: fiscalNumber.trim()
        }
        if (this.customerData.password !== undefined && this.customerData.password !== null && String(this.customerData.password).trim() !== '') {
          payload.password = String(this.customerData.password).trim()
        }
        const response = await axios.post('/api/clients/find-by-business-and-fiscal', payload)
        if (response.data.success && response.data.data) {
          const client = response.data.data
          await this.cartStore.setClient(client)
          
          if (!this.customerData.name) this.customerData.name = client.name
          if (!this.customerData.storeName) this.customerData.storeName = client.store_name || ''
          this.customerData.fiscalNumber = client.fiscal_number || fiscalNumber
          if (!this.customerData.city) this.customerData.city = client.city || ''
          if (!this.customerData.phone && client.phone) this.customerData.phone = client.phone
          
          // Load client locations
          this.clientLocations = client.locations && Array.isArray(client.locations) && client.locations.length > 0 
            ? client.locations.filter(loc => loc && (loc.is_active !== false && loc.is_active !== 0))
            : []
          
          // Auto-select if only one location
          if (this.clientLocations.length === 1) {
            this.selectedLocationId = this.clientLocations[0].id
          } else {
            this.selectedLocationId = null
          }
          
          this.persistCustomerData()
          
          // Load order history only if not already loading
          if (!this.historyLoading) {
            await this.loadOrderHistory(null, client.id)
          }
          return
        }

        this.cartStore.clearClient()
        this.clientLocations = []
        this.selectedLocationId = null
        if (!this.historyLoading) {
          await this.loadOrderHistory(null, null)
        }
      } catch (error) {
        console.error('Error identifying client:', error)
        this.cartStore.clearClient()
        this.clientLocations = []
        this.selectedLocationId = null
        if (!this.historyLoading) {
          await this.loadOrderHistory(null, null)
        }
      } finally {
        this.identifyingClient = false
      }
    },
    async loadOrderHistory(phone, clientId = null) {
      if (!phone && !clientId) {
        this.ordersHistory = []
        this.orderHistoryLoaded = false
        return
      }

      // Prevent duplicate loading
      if (this.historyLoading) {
        return
      }

      this.historyLoading = true
      this.historyError = null
      this.orderHistoryLoaded = false
      
      try {
        const response = await axios.get('/api/orders/history', {
          params: {
            phone,
            client_id: clientId
          }
        })
        
        // Remove duplicates by order ID
        const orders = response.data.data || []
        const uniqueOrders = orders.filter((order, index, self) => 
          index === self.findIndex((o) => o.id === order.id)
        )
        
        // Sort by created_at descending (newest first)
        uniqueOrders.sort((a, b) => {
          const dateA = new Date(a.created_at)
          const dateB = new Date(b.created_at)
          return dateB - dateA
        })
        
        this.ordersHistory = uniqueOrders
        this.orderHistoryLoaded = true
        
        // Hide success message after history is loaded
        if (this.saveSuccess) {
          setTimeout(() => {
            this.saveSuccess = false
            this.savedOrder = null
          }, 3000)
        }
      } catch (error) {
        console.error('Error loading order history:', error)
        this.historyError = 'Nuk mund të ngarkohen porositë. Ju lutem provoni përsëri.'
        this.orderHistoryLoaded = false
      } finally {
        this.historyLoading = false
      }
    },
    persistCustomerData() {
      const toStore = { ...this.customerData }
      delete toStore.password
      const existing = localStorage.getItem('gastrotrade_customer_data')
      const merged = existing ? { ...JSON.parse(existing), ...toStore } : toStore
      delete merged.password
      localStorage.setItem('gastrotrade_customer_data', JSON.stringify(merged))
    },
    updateLocationData() {
      // Update customerData with location data when location is selected
      if (this.selectedLocationId && this.clientLocations.length > 0) {
        const selectedLocation = this.clientLocations.find(loc => loc.id === this.selectedLocationId)
        if (selectedLocation) {
          this.customerData.locationUnitName = selectedLocation.unit_name || ''
          this.customerData.locationStreetNumber = selectedLocation.street_number || ''
          this.customerData.locationPhone = selectedLocation.phone || ''
          this.customerData.locationViber = selectedLocation.viber || ''
          this.customerData.locationCity = selectedLocation.notes || ''
          this.persistCustomerData()
        }
      } else {
        // Clear location data if no location is selected
        this.customerData.locationUnitName = ''
        this.customerData.locationStreetNumber = ''
        this.customerData.locationPhone = ''
        this.customerData.locationViber = ''
        this.customerData.locationCity = ''
        this.persistCustomerData()
      }
    },
    generateOrderNumber() {
      // Generate order number in the same format as backend
      const now = new Date()
      const datePart = now.getFullYear().toString() + 
        String(now.getMonth() + 1).padStart(2, '0') + 
        String(now.getDate()).padStart(2, '0')
      
      // Generate random 4-character string
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
      let randomPart = ''
      for (let i = 0; i < 4; i++) {
        randomPart += chars.charAt(Math.floor(Math.random() * chars.length))
      }
      
      // For count part, we'll use a timestamp-based approach since we don't have access to DB count
      // This ensures uniqueness even if multiple orders are created in the same second
      const countPart = String(Date.now() % 1000).padStart(3, '0')
      
      return `GT-${datePart}-${countPart}${randomPart}`
    },
    async saveOrder() {
      if (!this.isFormValid) {
        alert('Ju lutem plotësoni të gjitha fushat e detyrueshme!')
        return
      }

      // Check if client has multiple locations and location is not selected
      if (this.cartStore.client && this.clientLocations.length > 1 && !this.selectedLocationId) {
        alert('Ju lutem zgjidhni pikën/njësinë për këtë porosi!')
        return
      }

      if (!this.cartStore.items.length) {
        alert('Shporta juaj është e zbrazët!')
        return
      }

      this.persistCustomerData()

      // Generate order number if not already generated
      if (!this.pendingOrderNumber) {
        this.pendingOrderNumber = this.generateOrderNumber()
      }

      this.savingOrder = true
      try {
        const payload = {
          client_id: this.cartStore.client ? this.cartStore.client.id : null,
          client_location_id: this.selectedLocationId || null,
          order_number: this.pendingOrderNumber, // Send order number to backend
          customer: {
            name: this.customerData.name,
            business_name: this.customerData.storeName.trim(),
            fiscal_number: this.customerData.fiscalNumber.trim(),
            city: this.customerData.city,
            phone: this.customerData.phone || null,
            viber: this.customerData.phone || null
          },
          items: this.cartStore.items.map(item => {
            // If customer bought by pieces (has actual_pieces), send actual_pieces as quantity
            // and set sold_by_package to false so backend knows quantity is already in pieces
            const canBuyByPieces = this.canBuyByPieces(item) && item.actual_pieces
            const quantity = canBuyByPieces ? item.actual_pieces : item.quantity
            const soldByPackage = canBuyByPieces ? false : !!item.sold_by_package
            
            // Determine unit_type based on how the item was bought
            // If bought by pieces, unit_type is 'cp' (copa)
            // If bought by package, unit_type is 'package' (kompleti)
            let unitType = null
            if (canBuyByPieces) {
              unitType = 'cp' // Bought by pieces
            } else if (item.sold_by_package && item.pieces_per_package) {
              unitType = 'package' // Bought by package
            } else {
              unitType = 'cp' // Default to pieces
            }
            
            return {
              product_id: item.id,
              name: item.name,
              quantity: quantity,
              sold_by_package: soldByPackage,
              pieces_per_package: item.pieces_per_package,
              unit_type: unitType,
              unit_price: item.price,
              total_price: item.price ? this.getItemTotal(item) : null,
              discount_amount: item.discount_amount || 0,
              discount_type: item.discount_type || null,
              discount_value: item.discount_value || 0
            }
          }),
          totals: {
            total_items: this.cartStore.totalItems,
            subtotal: this.cartStore.subtotal,
            discount_amount: this.cartStore.generalDiscountAmount || 0,
            discount_type: this.cartStore.generalDiscountType || null,
            discount_value: this.cartStore.generalDiscountValue || 0,
            total_amount: this.cartStore.totalPrice > 0 ? this.cartStore.totalPrice : null
          },
          has_vat: this.hasVat,
          vat_amount: this.hasVat && this.cartStore.totalPrice > 0 ? this.vatAmount : null,
          amount_before_vat: this.hasVat && this.cartStore.totalPrice > 0 ? this.amountBeforeVat : null,
          is_paid: false,
          paid_at: null
        }

        const response = await axios.post('/api/orders', payload)
        this.savedOrder = response.data.data
        this.saveSuccess = true
        
        // Clear pending order number after successful save
        this.pendingOrderNumber = null
        
        // Store location data in savedOrder and customerData
        if (this.selectedLocationId && this.clientLocations.length > 0) {
          const selectedLocation = this.clientLocations.find(loc => loc.id === this.selectedLocationId)
          if (selectedLocation) {
            // Add location data to savedOrder for printing
            this.savedOrder.location_unit_name = selectedLocation.unit_name
            this.savedOrder.location_street_number = selectedLocation.street_number
            this.savedOrder.location_phone = selectedLocation.phone
            this.savedOrder.location_viber = selectedLocation.viber
            this.savedOrder.location_city = selectedLocation.notes
            
            // Store location data in customerData for OrderConfirmation page
            this.customerData.locationUnitName = selectedLocation.unit_name
            this.customerData.locationStreetNumber = selectedLocation.street_number
            this.customerData.locationPhone = selectedLocation.phone
            this.customerData.locationViber = selectedLocation.viber
            this.customerData.locationCity = selectedLocation.notes
            this.persistCustomerData()
          }
        }
        
        // Show success notification with link to admin sales
        const orderNumber = response.data.data.order_number
        const adminToken = localStorage.getItem('admin_token')
        const viewInAdmin = adminToken ? 
          `\n\n🔗 Shiko në Admin: ${window.location.origin}/admin/sales?order=${orderNumber}` : 
          ''
        alert(`✅ Porosia #${orderNumber} u ruajt me sukses!${viewInAdmin}`)
        
        // Store order number for potential navigation
        if (adminToken) {
          localStorage.setItem('last_saved_order', orderNumber)
        }
        
        // Load order history only once after saving
        if (!this.historyLoading) {
          await this.loadOrderHistory(
            this.customerData.phone || null, 
            this.cartStore.client ? this.cartStore.client.id : null
          )
        }
      } catch (error) {
        console.error('Error saving order:', error)
        console.error('Error details:', error.response?.data)
        let errorMessage = 'Gabim gjatë ruajtjes së porosisë. Ju lutem provoni përsëri.'
        if (error.response && error.response.data) {
          if (error.response.data.message) {
            errorMessage = error.response.data.message
          } else if (error.response.data.errors) {
            const errors = Object.values(error.response.data.errors).flat()
            errorMessage = errors.join('\n')
          } else if (error.response.data.error) {
            errorMessage = error.response.data.error
          }
        }
        alert(errorMessage)
      } finally {
        this.savingOrder = false
      }
    },
    printOrder(order) {
      if (!order) return

      // Debug: Log order location data
      let hasLocationData = !!(order.location_unit_name || order.location_street_number || order.location_city || order.location_phone || order.location_viber)
      console.log('Print order - Order location data:', {
        location_unit_name: order.location_unit_name,
        location_street_number: order.location_street_number,
        location_city: order.location_city,
        location_phone: order.location_phone,
        location_viber: order.location_viber,
        hasLocationData: hasLocationData,
        selectedLocationId: this.selectedLocationId
      })
      
      // If location data is missing but we have selectedLocationId, try to get it
      if (!hasLocationData && this.selectedLocationId && this.clientLocations && this.clientLocations.length > 0) {
        const selectedLocation = this.clientLocations.find(loc => loc.id === this.selectedLocationId)
        if (selectedLocation) {
          console.log('Adding missing location data from selectedLocationId:', selectedLocation)
          order.location_unit_name = selectedLocation.unit_name || null
          order.location_street_number = selectedLocation.street_number || null
          order.location_city = selectedLocation.notes || null
          order.location_phone = selectedLocation.phone || null
          order.location_viber = selectedLocation.viber || null
          hasLocationData = !!(order.location_unit_name || order.location_street_number || order.location_city || order.location_phone || order.location_viber)
          console.log('Updated order with location data:', {
            location_unit_name: order.location_unit_name,
            location_street_number: order.location_street_number,
            hasLocationData: hasLocationData
          })
        }
      }

      const totalAmount = order.total_amount ? this.formatPrice(order.total_amount) : 'Sipas kërkesës'
      const createdAt = this.formatDate(order.created_at)
      const invoiceDateFormatted = this.formatDate(order.created_at)
      const paidAt = order.paid_at ? this.formatDate(order.paid_at) : null
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
      const valueBeforeDiscount = (order.subtotal != null ? parseFloat(order.subtotal) : null) || (order.items || []).reduce((s, i) => s + (parseFloat(i.total_price) || 0), 0)

      const itemsRows = (order.items || [])
        .map((item, idx) => {
          const barcode = (item.barcode != null && item.barcode !== '') ? String(item.barcode) : (item.product && item.product.barcode != null && item.product.barcode !== '' ? String(item.product.barcode) : '')
          const qty = parseFloat(item.quantity) || 1
          const unitPrice = item.unit_price != null ? parseFloat(item.unit_price) : null
          const lineTotal = item.total_price != null ? parseFloat(item.total_price) : (unitPrice ? unitPrice * qty : 0)
          const unitPriceNoVat = hasVat && unitPrice ? unitPrice / 1.18 : unitPrice
          const discountPct = 0
          return '<tr>' +
            '<td class="inv-num">' + (idx + 1) + '</td>' +
            '<td class="inv-code">' + (barcode || '-') + '</td>' +
            '<td class="inv-desc">' + (item.product_name || '') + '</td>' +
            '<td class="inv-qty">' + fmtQty(qty) + '</td>' +
            '<td class="inv-unit">Copë</td>' +
            '<td class="inv-num">' + (unitPriceNoVat != null ? fmtNum(unitPriceNoVat) : '-') + '</td>' +
            '<td class="inv-num">' + fmtNum(discountPct) + '</td>' +
            '<td class="inv-num">' + (hasVat ? vatRate : 0) + '</td>' +
            '<td class="inv-num">' + (unitPrice != null ? fmtNum(unitPrice) : '-') + '</td>' +
            '<td class="inv-num">' + (lineTotal > 0 ? fmtNum(lineTotal) : '-') + '</td>' +
            '</tr>'
        })
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
      const discountVal = totalItemDiscounts > 0 ? fmtNum(totalItemDiscounts) : '0.00'
      const valNoVat = amountBeforeVat != null ? fmtNum(amountBeforeVat) : '0.00'
      const paymentDone = isPaid ? (order.total_amount ? fmtNum(parseFloat(order.total_amount)) : '0.00') : '0.00'
      const totalForPay = order.total_amount ? fmtNum(parseFloat(order.total_amount)) : '-'
      const mbetjaVal = order.total_amount ? (isPaid ? '0.00' : fmtNum(parseFloat(order.total_amount))) : '-'

      const itemsTextLines = (order.items || [])
        .map((item, idx) => {
          const barcode = (item.barcode != null && item.barcode !== '') ? String(item.barcode) : (item.product && item.product.barcode ? String(item.product.barcode) : '-')
          const qty = parseFloat(item.quantity) || 1
          const unitPrice = item.unit_price != null ? parseFloat(item.unit_price) : null
          const lineTotal = item.total_price != null ? parseFloat(item.total_price) : (unitPrice ? unitPrice * qty : 0)
          const unitPriceNoVat = hasVat && unitPrice ? unitPrice / 1.18 : unitPrice
          return (idx + 1) + '\t' + (barcode || '-') + '\t' + (item.product_name || '') + '\t' + fmtQty(qty) + '\tCopë\t' + (unitPriceNoVat != null ? fmtNum(unitPriceNoVat) : '-') + '\t0.00\t' + (hasVat ? 18 : 0) + '\t' + (unitPrice != null ? fmtNum(unitPrice) : '-') + '\t' + (lineTotal > 0 ? fmtNum(lineTotal) : '-')
        })
        .join('\n')

      const fullInvoiceText =
        'ARON TRADE\n' +
        'Nrf/NIPT: ' + (order.company_nrf || '—') + '\n' +
        'tel: +383 48 75 66 46 / +383 44 82 43 14\n' +
        'email: svalon95@gmail.com\n' +
        'Adresë: Ferizaj, Kosovë, Rruga Lidhja e Prizrenit\n' +
        'Nrb: ' + (order.company_nrb || '—') + '\n' +
        'Tvsh: ' + (order.company_tvsh || '—') + '\n\n' +
        'Nr. Faturës: ' + (order.order_number || 'N/A') + '\n\n' +
        'BLERESI\n' +
        buyerName + '\n' +
        (buyerAddress ? 'Adresa: ' + buyerAddress + '\n' : '') +
        'Qyteti: ' + buyerCity + '\n' +
        'No fiskal: ' + buyerFiscal + '\n' +
        'Numri unik: ' + buyerFiscal + '\n' +
        (order.phone ? 'Telefon: ' + order.phone + '\n' : '') + '\n' +
        'Data fatura\tKushtet\tData e skadimit\tUser\tReferenca\tLokacioni\n' +
        invoiceDateFormatted + '\t\t' + expDate + '\tKlient\t\t' + (order.location_unit_name || '-') + '\n\n' +
        'No\tBarcode\tEmertimi\tSasia\tNjesia\tCmimi pa TVSH\tRabati %\tTVSH %\tCmimi me TVSH\tVlera me TVSH\n' +
        itemsTextLines + '\n\n' +
        'Normat Tatimore\tBaza\tTVSH\tVlera\n' +
        'TVSH 0%\t0.00\t0.00\t0.00\n' +
        'TVSH 18%\t' + taxBase18 + '\t' + taxVat18 + '\t' + taxVal18 + '\n\n' +
        'Vlera para zbritjes\t' + valBeforeDiscount + '\n' +
        'Rabati\t' + discountVal + '\n' +
        'Vlera pa TVSH\t' + valNoVat + '\n' +
        'TVSH\t' + taxVat18 + '\n' +
        'Vlera për pagesë (EUR)\t' + totalForPay + '\n' +
        'Pagesa\t' + paymentDone + '\n' +
        'Mbetja\t' + mbetjaVal + '\n\n' +
        'Faturoi: Aron Trade\n' +
        'Dergoi: Aron Trade\n' +
        'Pranoi: ' + buyerName + '\n\n' +
        'Llogaria bankare: (vendosni nëse keni)'

      const htmlContent = '<!DOCTYPE html><html lang="sq">' +
        '<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">' +
        '<title>Faturë ' + (order.order_number || 'N/A') + '</title>' +
        '<style>' +
        '*{box-sizing:border-box}' +
        'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;padding:24px 32px;color:#111827;font-size:13px;line-height:1.4;max-width:900px;margin:0 auto}' +
        '.inv-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;margin-bottom:16px}' +
        '@media (max-width:768px){body{padding:12px 16px;font-size:12px;max-width:100%}.inv-header{flex-direction:column;gap:16px;align-items:stretch}.inv-seller{min-width:auto}.inv-company{font-size:16px}.inv-header>div:last-child{text-align:left}.inv-nr{text-align:left}.inv-meta{font-size:11px}.inv-meta th,.inv-meta td{padding:6px 8px}.inv-table-wrap{margin-left:-16px;margin-right:-16px;padding:0 16px}.inv-table{font-size:11px;min-width:720px}.inv-table th,.inv-table td{padding:6px 4px}.inv-tax{max-width:100%;font-size:11px}.inv-totals{max-width:100%;font-size:12px}.inv-footer{flex-direction:column;gap:24px;margin-top:24px;align-items:stretch}.inv-sig{min-width:auto;text-align:left}.inv-sig .line{margin-top:16px}.no-print{margin-top:16px;padding:12px}.no-print h3{font-size:13px}.no-print .btns{flex-direction:column;gap:8px}.no-print button{width:100%;padding:12px 18px;font-size:13px}}' +
        '@media print{body{padding:12px 16px}.no-print{display:none !important}}' +
        '.inv-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;padding-bottom:16px;border-bottom:2px solid #0d9488}' +
        '.inv-title{font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px}' +
        '.inv-seller{min-width:200px}.inv-company{font-weight:700;font-size:18px;color:#0f766e;margin:0 0 8px 0;display:block}' +
        '.inv-nr{text-align:right;font-weight:700;font-size:14px;color:#0d9488}' +
        '.inv-bleresi{background:#f8fafc;padding:12px 16px;border-radius:8px;margin-bottom:16px;border:1px solid #e2e8f0}' +
        '.inv-bleresi h3{font-size:12px;text-transform:uppercase;color:#64748b;margin:0 0 8px 0}' +
        '.inv-meta{width:100%;border-collapse:collapse;margin-bottom:16px;font-size:12px}' +
        '.inv-meta th,.inv-meta td{border:1px solid #e2e8f0;padding:6px 10px;text-align:left}' +
        '.inv-meta th{background:#f1f5f9;font-weight:600;color:#475569}' +
        '.inv-table{width:100%;border-collapse:collapse;margin-bottom:16px;font-size:12px}' +
        '.inv-table th,.inv-table td{border:1px solid #e2e8f0;padding:6px 8px}' +
        '.inv-table th{background:#0d9488;color:#fff;font-weight:600;text-align:center}' +
        '.inv-table .inv-num{text-align:right}.inv-table .inv-desc{text-align:left}.inv-table .inv-code{text-align:center}' +
        '.inv-tax{width:100%;max-width:320px;margin-left:auto;border-collapse:collapse;font-size:12px;margin-bottom:12px}' +
        '.inv-tax th,.inv-tax td{border:1px solid #e2e8f0;padding:6px 10px;text-align:right}' +
        '.inv-tax th{background:#f1f5f9;font-weight:600}' +
        '.inv-totals{max-width:280px;margin-left:auto;font-size:13px}' +
        '.inv-totals .row{display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #e2e8f0}' +
        '.inv-totals .row.total{font-weight:700;font-size:15px;color:#0d9488;border-bottom:none;padding-top:8px;margin-top:4px;border-top:2px solid #0d9488}' +
        '.inv-footer{display:flex;justify-content:space-between;align-items:flex-end;margin-top:40px;padding-top:24px;border-top:2px solid #e2e8f0}' +
        '.inv-sig{text-align:center;min-width:180px}' +
        '.inv-sig .line{border-top:1px solid #111827;padding-top:4px;margin-top:32px;font-weight:600;font-size:12px}' +
        '.inv-sig .sub{font-size:11px;color:#64748b;margin-top:4px}' +
        '.inv-bank{font-size:12px;color:#64748b;margin-top:16px}' +
        '.no-print{margin-top:24px;padding:16px;border-top:2px solid #e2e8f0;text-align:center}' +
        '.no-print h3{margin-bottom:12px;font-size:14px;color:#475569}' +
        '.no-print .btns{display:flex;gap:8px;justify-content:center;flex-wrap:wrap}' +
        '.no-print button{padding:10px 18px;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:500}' +
        '</style></head><body data-invoice-text="' + (fullInvoiceText || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '">' +
        '<div class="inv-header">' +
        '<div class="inv-seller">' +
        '<h2 class="inv-company">Aron Trade</h2>' +
        '<div class="inv-title">Nrf / NIPT</div><div>' + (order.company_nrf || '—') + '</div>' +
        '<div class="inv-title">tel</div><div>+383 48 75 66 46 / +383 44 82 43 14</div>' +
        '<div class="inv-title">email</div><div>svalon95@gmail.com</div>' +
        '</div>' +
        '<div style="text-align:right">' +
        '<div class="inv-title">Adresë</div><div>Ferizaj, Kosovë, Rruga Lidhja e Prizrenit</div>' +
        '<div class="inv-title">Nrb</div><div>' + (order.company_nrb || '—') + '</div>' +
        '<div class="inv-title">Tvsh</div><div>' + (order.company_tvsh || '—') + '</div>' +
        '</div>' +
        '<div>' +
        '<div class="inv-nr">' + (order.order_number || 'N/A') + '</div>' +
        '<div class="inv-title">Nr. Faturës</div>' +
        '</div></div>' +
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
        '<div class="inv-table-wrap"><table class="inv-table">' +
        '<thead><tr>' +
        '<th>No</th><th>Barcode</th><th>Emertimi</th><th>Sasia</th><th>Njesia</th>' +
        '<th>Cmimi pa TVSH</th><th>Rabati %</th><th>TVSH %</th><th>Cmimi me TVSH</th><th>Vlera me TVSH</th>' +
        '</tr></thead><tbody>' + itemsRows + '</tbody></table></div>' +
        '<table class="inv-tax">' +
        '<tr><th>Normat Tatimore</th><th>Baza</th><th>TVSH</th><th>Vlera</th></tr>' +
        '<tr><td>TVSH 0%</td><td>0.00</td><td>0.00</td><td>0.00</td></tr>' +
        '<tr><td>TVSH 18%</td><td>' + taxBase18 + '</td><td>' + taxVat18 + '</td><td>' + taxVal18 + '</td></tr>' +
        '</table>' +
        '<div class="inv-totals">' +
        '<div class="row"><span>Vlera para zbritjes</span><span>' + valBeforeDiscount + '</span></div>' +
        '<div class="row"><span>Rabati</span><span>' + discountVal + '</span></div>' +
        '<div class="row"><span>Vlera pa TVSH</span><span>' + valNoVat + '</span></div>' +
        '<div class="row"><span>TVSH</span><span>' + taxVat18 + '</span></div>' +
        '<div class="row total"><span>Vlera për pagesë (EUR)</span><span>' + (order.total_amount ? fmtNum(parseFloat(order.total_amount)) : '-') + '</span></div>' +
        '<div class="row"><span>Pagesa</span><span>' + paymentDone + '</span></div>' +
        '<div class="row"><span>Mbetja</span><span>' + (order.total_amount ? (isPaid ? '0.00' : fmtNum(parseFloat(order.total_amount))) : '-') + '</span></div>' +
        '</div>' +
        '<div class="inv-footer">' +
        '<div class="inv-sig"><div class="line">Faturoi</div><div class="sub">Aron Trade</div></div>' +
        '<div class="inv-sig"><div class="line">Dergoi</div><div class="sub">Aron Trade</div></div>' +
        '<div class="inv-sig"><div class="line">Pranoi</div><div class="sub">' + buyerName + '</div></div>' +
        '</div>' +
        '<div class="inv-bank">Llogaria bankare: (vendosni nëse keni)</div>' +
        '</body>' +
        '</html>'

      this.invoiceHtml = htmlContent
      this.invoiceShareText = fullInvoiceText
      this.invoiceOrderNumber = order.order_number || ''
      this.showInvoiceModal = true
    },
    closeInvoiceModal() {
      this.showInvoiceModal = false
      this.invoiceHtml = ''
      this.invoiceShareText = ''
      this.invoiceOrderNumber = ''
    },
    shareInvoiceViber() {
      const text = this.invoiceShareText || ''
      if (!text) return
      if (navigator.share) {
        navigator.share({ title: 'Faturë ' + this.invoiceOrderNumber, text }).catch(() => this.fallbackCopyShare(text, 'Viber'))
      } else {
        this.fallbackCopyShare(text, 'Viber')
      }
    },
    shareInvoiceWhatsApp() {
      const text = this.invoiceShareText || ''
      if (!text) return
      window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank')
    },
    shareInvoiceGmail() {
      const text = this.invoiceShareText || ''
      if (!text) return
      const subject = encodeURIComponent('Faturë ' + (this.invoiceOrderNumber || ''))
      const body = encodeURIComponent(text)
      window.location.href = 'mailto:svalon95@gmail.com?subject=' + subject + '&body=' + body
    },
    printInvoiceFromModal() {
      if (!this.invoiceHtml) return
      const w = window.open('', '_blank')
      if (!w) {
        alert('Lejoni hapjen e dritareve për të printuar.')
        return
      }
      w.document.write(this.invoiceHtml)
      w.document.close()
      w.focus()
      setTimeout(() => { w.print(); w.close() }, 300)
    },
    fallbackCopyShare(text, app) {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => alert('Fatura u kopjua. Ngjiteni në ' + app + '.')).catch(() => prompt('Kopjoni dhe ngjisni në ' + app + ':', text))
      } else {
        prompt('Kopjoni faturën dhe ngjisni në ' + app + ':', text)
      }
    },
    printCurrentOrder() {
      if (!this.isFormValid) {
        alert('Ju lutem plotësoni të gjitha fushat e detyrueshme!')
        return
      }

      if (!this.cartStore.items.length) {
        alert('Shporta juaj është e zbrazët!')
        return
      }

      // Get location data from selected location if available
      let locationData = {
        location_unit_name: null,
        location_street_number: null,
        location_city: null,
        location_phone: null,
        location_viber: null
      }
      
      // If location is selected, get fresh data from clientLocations
      if (this.selectedLocationId && this.clientLocations.length > 0) {
        const selectedLocation = this.clientLocations.find(loc => loc.id === this.selectedLocationId)
        if (selectedLocation) {
          locationData = {
            location_unit_name: selectedLocation.unit_name || null,
            location_street_number: selectedLocation.street_number || null,
            location_city: selectedLocation.notes || null,
            location_phone: selectedLocation.phone || null,
            location_viber: selectedLocation.viber || null
          }
          console.log('Location data for print:', locationData)
        }
      } else {
        // Fallback to customerData if no location selected
        locationData = {
          location_unit_name: this.customerData.locationUnitName || null,
          location_street_number: this.customerData.locationStreetNumber || null,
          location_city: this.customerData.locationCity || null,
          location_phone: this.customerData.locationPhone || null,
          location_viber: this.customerData.locationViber || null
        }
      }

      // Generate order number if not already generated
      if (!this.pendingOrderNumber) {
        this.pendingOrderNumber = this.generateOrderNumber()
      }

      // Debug: Log location data
      console.log('Print current order - Location data:', locationData)
      console.log('Print current order - Selected location ID:', this.selectedLocationId)
      console.log('Print current order - Client locations:', this.clientLocations)
      console.log('Print current order - Has location data:', !!(locationData.location_unit_name || locationData.location_street_number || locationData.location_city || locationData.location_phone || locationData.location_viber))
      
      // Create a temporary order object from current cart
      const tempOrder = {
        order_number: this.pendingOrderNumber,
        customer_name: this.customerData.name,
        business_name: this.customerData.storeName,
        fiscal_number: this.customerData.fiscalNumber,
        city: this.customerData.city,
        phone: this.customerData.phone || null,
        location_unit_name: locationData.location_unit_name || null,
        location_street_number: locationData.location_street_number || null,
        location_city: locationData.location_city || null,
        location_phone: locationData.location_phone || null,
        location_viber: locationData.location_viber || null,
        total_items: this.cartStore.totalItems,
        subtotal: this.cartStore.subtotal,
        discount_amount: this.cartStore.generalDiscountAmount || 0,
        discount_type: this.cartStore.generalDiscountType || null,
        discount_value: this.cartStore.generalDiscountValue || 0,
        total_amount: this.cartStore.totalPrice > 0 ? this.cartStore.totalPrice : null,
        has_vat: this.hasVat,
        vat_amount: this.hasVat && this.cartStore.totalPrice > 0 ? this.vatAmount : null,
        amount_before_vat: this.hasVat && this.cartStore.totalPrice > 0 ? this.amountBeforeVat : null,
        created_at: new Date().toISOString(),
        is_paid: false,
        paid_at: null,
        items: this.cartStore.items.map(item => {
          // Format quantity correctly
          let quantityText = ''
          if (item.sold_by_package && item.pieces_per_package) {
            if (this.canBuyByPieces(item) && item.actual_pieces) {
              const packages = Math.ceil(item.actual_pieces / item.pieces_per_package)
              quantityText = `${packages} komplete (${item.actual_pieces} copa)`
            } else {
              const totalPieces = item.quantity * item.pieces_per_package
              quantityText = `${item.quantity} komplete (${totalPieces} copa)`
            }
          } else {
            quantityText = `${item.quantity} copë`
          }
          
          return {
            id: item.id,
            product_name: item.name,
            barcode: item.barcode != null ? item.barcode : '',
            quantity: item.quantity,
            quantity_text: quantityText,
            sold_by_package: item.sold_by_package || false,
            pieces_per_package: item.pieces_per_package || null,
            unit_price: item.price || null,
            discount_amount: item.discount_amount || 0,
            discount_type: item.discount_type || null,
            discount_value: item.discount_value || 0,
            total_price: this.getItemTotal(item),
            pieces_per_package: item.pieces_per_package || null,
            unit_price: item.price,
            total_price: item.price ? this.getItemTotal(item) : null
          }
        })
      }

      this.printOrder(tempOrder)
    },
    buildOrderMessage(order) {
      if (!order) return ''

      let message = `📦 POROSI E RUAJTUR #${order.order_number || ''}\n\n`
      message += '👤 TË DHËNAT E POROSITËSIT:\n'
      message += `Emri: ${order.customer_name || 'N/A'}\n`
      message += `Biznesi: ${order.business_name || 'N/A'}\n`
      message += `Qyteti: ${order.city || 'N/A'}\n`
      if (order.phone) {
        message += `📞 Telefon/Viber: ${order.phone}\n`
      }
      message += '\n─────────────────────────\n\n'

      message += '🛒 PRODUKTET E ZGJEDHURA:\n\n'

      ;(order.items || []).forEach((item, index) => {
        const itemName = item.product_name || `Produkti ${index + 1}`
        const quantity = item.quantity || 1
        message += `${index + 1}. ${itemName}\n`

        if (item.unit_price !== null && item.unit_price !== undefined && !isNaN(item.unit_price) && item.unit_price > 0) {
          if (item.sold_by_package && item.pieces_per_package) {
            const itemTotal = item.total_price || (item.unit_price * quantity * item.pieces_per_package)
            message += `   €${parseFloat(item.unit_price).toFixed(2)} × ${quantity}(komplete) × ${item.pieces_per_package}cp = €${parseFloat(itemTotal).toFixed(2)}\n`
          } else {
            const itemTotal = item.total_price || (item.unit_price * quantity)
            message += `   €${parseFloat(item.unit_price).toFixed(2)} × ${quantity} = €${parseFloat(itemTotal).toFixed(2)}\n`
          }
        } else {
          if (item.sold_by_package && item.pieces_per_package) {
            message += `   Sasia: ${quantity} komplete (${quantity * item.pieces_per_package} copa)\n`
          } else {
            message += `   Sasia: ${quantity} copë\n`
          }
          message += '   Çmimi: Sipas kërkesës\n'
        }

        message += '\n'
      })

      message += '─────────────────────────\n\n'
      message += `📊 Total: ${order.total_items || 0} produkt(e)\n`
      if (order.total_amount && !isNaN(order.total_amount) && order.total_amount > 0) {
        message += `💰 Vlera totale: ${parseFloat(order.total_amount).toFixed(2)} €\n`
      } else {
        message += '💰 Vlera totale: Sipas kërkesës\n'
      }

      message += '\n📞 KONTAKT:\n'
      message += 'Email: svalon95@gmail.com\n'
      message += 'Telefon: 048 75 66 46 / 044 82 43 14\n'
      message += 'Viber: +383 48 75 66 46 / +383 44 82 43 14\n'
      message += '📍 Adresa: Ferizaj, Kosovë, Rruga Lidhja E Prizerent\n'

      return encodeURIComponent(message)
    },
    sendSavedOrderToViber(order, phoneNumber) {
      if (!order || !phoneNumber) return
      const message = this.buildOrderMessage(order)
      if (!message) return

      const viberUrl = `viber://chat?number=${encodeURIComponent(phoneNumber)}&text=${message}`
      try {
        window.open(viberUrl, '_blank')
      } catch (error) {
        console.error('Error opening Viber:', error)
        alert('Nuk mund të hapet Viber. Ju lutem provoni përsëri ose dërgojeni manualisht.')
      }
    },
    async submitOrder() {
      if (!this.isFormValid) {
        alert('Ju lutem plotësoni të gjitha fushat e detyrueshme!')
        return
      }

      // Check if client has multiple locations and location is not selected
      if (this.cartStore.client && this.clientLocations.length > 1 && !this.selectedLocationId) {
        alert('Ju lutem zgjidhni pikën/njësinë për këtë porosi!')
        return
      }

      if (!this.cartStore.items.length) {
        alert('Shporta juaj është e zbrazët!')
        return
      }

      this.submittingOrder = true
      
      try {
        // First save the order
        await this.saveOrder()
        
        // After successful save, navigate to OrderConfirmation page
        if (this.saveSuccess && this.savedOrder) {
          this.persistCustomerData()
          localStorage.setItem('gastrotrade_order_data', JSON.stringify(this.customerData))
          
          // Navigate to OrderConfirmation page with order data
          this.$router.push({
            name: 'OrderConfirmation',
            params: {
              customerData: encodeURIComponent(JSON.stringify(this.customerData)),
              orderNumber: this.savedOrder.order_number
            }
          })
        }
      } catch (error) {
        console.error('Error submitting order:', error)
        alert('Gabim gjatë dërgimit të porosisë. Ju lutem provoni përsëri.')
      } finally {
        this.submittingOrder = false
      }
    },
    sendOrderToViber(phoneNumber) {
      if (!this.isFormValid) {
        alert('Ju lutem plotësoni të gjitha fushat e detyrueshme!')
        return
      }

      this.persistCustomerData()

      localStorage.setItem('gastrotrade_order_data', JSON.stringify(this.customerData))
      
      this.$router.push({
        name: 'OrderConfirmation',
        params: {
          customerData: encodeURIComponent(JSON.stringify(this.customerData))
        }
      })
    },
    clearSearch() {
      this.searchQuery = ''
    }
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.invoice-modal-enter-active,
.invoice-modal-leave-active {
  transition: opacity 0.2s ease;
}
.invoice-modal-enter-from,
.invoice-modal-leave-to {
  opacity: 0;
}
</style>

