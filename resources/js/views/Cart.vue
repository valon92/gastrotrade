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

        <!-- Të dhënat e biznesit për klientët e regjistruar (edhe kur shporta është e zbrazët) -->
        <div class="mt-10 bg-white rounded-xl shadow-lg p-6 border border-gray-200">
          <h3 class="text-xl font-semibold text-gray-900 mb-2">
            Të dhënat e biznesit
          </h3>
          <p class="text-sm text-gray-600 mb-6">
            Klientët e regjistruar me çmime të caktuara: plotësoni më poshtë që të identifikoheni. Kur shtoni produkte, do të shfaqen çmimet tuaja.
          </p>

          <div v-if="hasClientPrices" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-800">
              ✅ Klienti i identifikuar: <strong>{{ cartStore.client.name }}</strong>. Kur shtoni produkte në shportë, do të aplikohen çmimet tuaja.
            </p>
          </div>
          <div v-else-if="identifyingClient" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">🔍 Duke identifikuar klientin...</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label for="emptyCustomerName" class="block text-sm font-medium text-gray-700 mb-1">Emri i porositësit</label>
              <input
                id="emptyCustomerName"
                type="text"
                v-model="customerData.name"
                @input="persistCustomerData"
                placeholder="Emri juaj"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              />
            </div>
            <div>
              <label for="emptyStoreName" class="block text-sm font-medium text-gray-700 mb-1">Emri i biznisit <span class="text-red-500">*</span></label>
              <input
                id="emptyStoreName"
                type="text"
                v-model="customerData.storeName"
                placeholder="Emri i biznesit"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              />
            </div>
            <div>
              <label for="emptyFiscalNumber" class="block text-sm font-medium text-gray-700 mb-1">Numri fiskal <span class="text-red-500">*</span></label>
              <input
                id="emptyFiscalNumber"
                type="text"
                v-model="customerData.fiscalNumber"
                placeholder="Nr. fiskal"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 uppercase tracking-wide"
              />
            </div>
            <div>
              <label for="emptyCity" class="block text-sm font-medium text-gray-700 mb-1">Qyteti <span class="text-red-500">*</span></label>
              <input
                id="emptyCity"
                type="text"
                v-model="customerData.city"
                @input="persistCustomerData"
                placeholder="Qyteti"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              />
            </div>
            <div>
              <label for="emptyPhone" class="block text-sm font-medium text-gray-700 mb-1">Telefon / Viber</label>
              <input
                id="emptyPhone"
                type="tel"
                v-model="customerData.phone"
                @input="persistCustomerData"
                placeholder="+383 XX XXX XXX"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              />
            </div>
          </div>
          <p class="mt-4 text-xs text-gray-500">
            Pas plotësimit të emrit të biznisit dhe numrit fiskal, identifikimi bëhet automatikisht nëse jeni klient i regjistruar.
          </p>
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
                      ({{ item.pieces_per_package }} copa/kompleti)
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
                <div v-if="item.sold_by_package && item.pieces_per_package" class="text-sm text-gray-600">
                  <span v-if="canBuyByPieces(item)">
                    ({{ getPiecesQuantity(item) }} copa)
                  </span>
                  <span v-else>
                    ({{ item.quantity }} kompleti = {{ item.quantity * item.pieces_per_package }} copa)
                  </span>
                </div>
                <button 
                  @click="removeItem(item.id)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Hiq
                </button>
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

            <!-- Info: fill data first to get prices -->
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
              <p class="text-sm text-amber-900">
                <strong>Para se të ruani ose dërgoni porosinë</strong>, plotësoni të dhënat e porositësit më poshtë. Nëse jeni klient i regjistruar (me çmime të caktuara), pas plotësimit të <strong>Emrit të Biznisit</strong> dhe <strong>Numrit Fiskal</strong> do të shfaqen automatikisht çmimet tuaja për këtë porosi.
              </p>
            </div>
            
            <!-- Client Identification Notice -->
            <div v-if="hasClientPrices" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
              <p class="text-sm text-green-800">
                ✅ Klienti i identifikuar: <strong>{{ cartStore.client.name }}</strong><br>
                Çmimet e personalizuara janë të aplikuara
              </p>
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

            <!-- Customer Information Form - fill first so order is informed with correct prices -->
            <div class="mb-6 pb-6 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Të Dhënat e Porositësit
              </h3>
              <div class="space-y-3">
                <div>
                  <label for="customerName" class="block text-sm font-medium text-gray-700 mb-1">
                    Emri i Porositësit <span class="text-red-500">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="customerName"
                    v-model="customerData.name"
                    @input="persistCustomerData"
                    required
                    placeholder="Shkruani emrin tuaj"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
                <div>
                  <label for="storeName" class="block text-sm font-medium text-gray-700 mb-1">
                    Emri i Biznisit <span class="text-red-500">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="storeName"
                    v-model="customerData.storeName"
                    required
                    placeholder="Shkruani emrin e biznisit"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
                <div>
                  <label for="fiscalNumber" class="block text-sm font-medium text-gray-700 mb-1">
                    Numri Fiskal <span class="text-red-500">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="fiscalNumber"
                    v-model="customerData.fiscalNumber"
                    required
                    placeholder="Shkruani numrin fiskal të biznesit"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 uppercase tracking-wide"
                  />
                  <p class="mt-1 text-xs text-gray-500">
                    Numri fiskal duhet të përputhet me të dhënat e regjistruara nga administratori për të shfaqur çmimet e personalizuara.
                  </p>
                </div>
                
                <!-- Location Selection - Only show if client has multiple locations -->
                <div v-if="cartStore.client && clientLocations.length > 1">
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
                      <template v-if="location.street_number">
                        - {{ location.street_number }}
                      </template>
                      <template v-if="location.notes">
                        ({{ location.notes }})
                      </template>
                    </option>
                  </select>
                  <p class="mt-1 text-xs text-gray-500">
                    Kompania ka {{ clientLocations.length }} pika/njësi. Ju lutem zgjidhni pikën/njësinë për këtë porosi.
                  </p>
                </div>
                <!-- Show single location info if only one location exists -->
                <div v-else-if="cartStore.client && clientLocations.length === 1" class="mt-2 p-2 bg-gray-50 border border-gray-200 rounded-lg">
                  <p class="text-xs text-gray-600">
                    📍 Pika/Njësia: <strong>{{ clientLocations[0].unit_name }}</strong>
                    <template v-if="clientLocations[0].street_number">
                      - {{ clientLocations[0].street_number }}
                    </template>
                    <template v-if="clientLocations[0].notes">
                      ({{ clientLocations[0].notes }})
                    </template>
                  </p>
                </div>
                
                <div>
                  <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                    Qyteti <span class="text-red-500">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="city"
                    v-model="customerData.city"
                    @input="persistCustomerData"
                    required
                    placeholder="Shkruani qytetin"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                    Numri i Telefonit/Viber
                  </label>
                  <input 
                    type="tel" 
                    id="phone"
                    v-model="customerData.phone"
                    @input="persistCustomerData"
                    placeholder="+383 XX XXX XXX ose 0XX XXX XXX"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  />
                  <p class="mt-1 text-xs text-gray-500">
                    Numri juaj për komunikim në Viber (opsional)
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
                <p class="text-xs mt-2 text-gray-500">
                  Plotësoni <strong>Emrin e Biznisit</strong> dhe <strong>Numrin Fiskal</strong> më sipër. Nëse jeni klient i regjistruar, çmimet e personalizuara do të aplikohen automatikisht dhe vlera totale do të përditësohet.
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
              
              <button 
                @click="printCurrentOrder"
                :disabled="!isFormValid || cartStore.items.length === 0"
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
              <div v-if="!customerData.storeName" class="text-sm text-gray-600">
                Shkruani emrin e biznisit për të parë porositë e ruajtura.
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
        phone: ''
      },
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
      pendingOrderNumber: null // Store order number before saving
    }
  },
  computed: {
    isAdmin() {
      return !!localStorage.getItem('admin_token')
    },
    isFormValid() {
      return this.customerData.name.trim() !== '' && 
             this.customerData.storeName.trim() !== '' && 
             this.customerData.fiscalNumber.trim() !== '' &&
             this.customerData.city.trim() !== ''
    },
    hasClientPrices() {
      return this.cartStore.client && Object.keys(this.cartStore.clientPrices).length > 0
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
    'customerData.storeName'(newBusinessName) {
      this.scheduleClientIdentification()
    },
    'customerData.fiscalNumber'(newFiscal) {
      if (typeof newFiscal === 'string') {
        const normalized = newFiscal.toUpperCase().replace(/\s+/g, '')
        if (normalized !== newFiscal) {
          this.customerData.fiscalNumber = normalized
          return
        }
      }
      this.scheduleClientIdentification()
    }
  },
  mounted() {
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

    if (this.customerData.storeName && this.customerData.fiscalNumber) {
      this.identifyClient(this.customerData.storeName, this.customerData.fiscalNumber)
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
          phone: ''
        }
        this.clientLocations = []
        this.selectedLocationId = null
        localStorage.removeItem('gastrotrade_customer_data')
        localStorage.removeItem('gastrotrade_order_data')
      }
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
        const response = await axios.post('/api/clients/find-by-business-and-fiscal', { 
          business_name: businessName.trim(),
          fiscal_number: fiscalNumber.trim()
        })
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
      localStorage.setItem('gastrotrade_customer_data', JSON.stringify(this.customerData))
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

      const printWindow = window.open('', '_blank', 'width=900,height=650')
      if (!printWindow) {
        alert('Lejoni hapjen e dritareve të reja për të printuar porosinë.')
        return
      }

      const totalAmount = order.total_amount ? this.formatPrice(order.total_amount) : 'Sipas kërkesës'
      const createdAt = this.formatDate(order.created_at)
      const paidAt = order.paid_at ? this.formatDate(order.paid_at) : null
      const isPaid = order.is_paid === true || order.is_paid === 1
      const paymentStatus = isPaid ? 'E PAGUAR' : 'JO E PAGUAR'
      const paymentStatusClass = isPaid ? 'color: #059669; font-weight: bold;' : 'color: #dc2626; font-weight: bold;'
      
      const itemsRows = (order.items || [])
        .map(item => {
          const quantityText = item.quantity_text || this.formatOrderItemQuantity(item)
          const unitPrice = item.unit_price ? this.formatPrice(item.unit_price) : 'Sipas kërkesës'
          
          // Calculate item subtotal
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
        location_unit_name: order.location_unit_name || null,
        location_street_number: order.location_street_number || null,
        location_city: order.location_city || null,
        location_phone: order.location_phone || null,
        location_viber: order.location_viber || null,
        total_amount: order.total_amount
      })

      const scriptTag = '<' + 'script' + '>'
      const scriptClose = '<' + '/' + 'script' + '>'

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
        // If location data exists, show ONLY location data, otherwise show client data
        (order.location_unit_name || order.location_street_number || order.location_city || order.location_phone || order.location_viber ? (
          '<div style="background-color: #f0f9ff; padding: 12px; border-radius: 6px; margin-bottom: 12px; border-left: 4px solid #3b82f6;">' +
          '<p style="margin: 0 0 8px 0; font-weight: bold; color: #1e40af; font-size: 14px;">📍 Të Dhënat e Pikës/Njësisë</p>' +
          (order.location_unit_name ? '<p style="margin: 4px 0;"><strong>Pika/Njësia:</strong> ' + order.location_unit_name + '</p>' : '') +
          (order.location_street_number ? '<p style="margin: 4px 0;"><strong>Adresa:</strong> ' + order.location_street_number + '</p>' : '') +
          (order.location_city ? '<p style="margin: 4px 0;"><strong>Vendi/Qyteti:</strong> ' + order.location_city + '</p>' : '') +
          (order.location_phone ? '<p style="margin: 4px 0;"><strong>Telefon:</strong> ' + order.location_phone + '</p>' : '') +
          (order.location_viber ? '<p style="margin: 4px 0;"><strong>Viber:</strong> ' + order.location_viber + '</p>' : '') +
          '</div>'
        ) : (
          // If no location data, show only client data
          '<div style="margin-bottom: 12px;">' +
          '<p><strong>Klienti:</strong> ' + (order.customer_name || 'N/A') + ' — ' + (order.business_name || 'N/A') + '</p>' +
          '<p><strong>Nr. Fiskal:</strong> ' + (order.fiscal_number || 'N/A') + '</p>' +
          '<p><strong>Qyteti:</strong> ' + (order.city || 'N/A') + '</p>' +
          '<p><strong>Telefon/Viber:</strong> ' + (order.phone || 'N/A') + '</p>' +
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
        '<p><strong>Totali i produkteve:</strong> ' + order.total_items + '</p>' +
        (order.subtotal ? '<p><strong>Nëntotali (para zbritjeve):</strong> ' + this.formatPrice(order.subtotal) + '</p>' : '') +
        (totalItemDiscounts > 0 ? '<p style="color: #dc2626;"><strong>Totali i zbritjeve të produkteve:</strong> -' + this.formatPrice(totalItemDiscounts) + '</p>' : '') +
        (order.discount_amount && order.discount_amount > 0 ? '<p style="color: #dc2626;"><strong>Zbritje e përgjithshme ' + (order.discount_type === 'percentage' ? order.discount_value + '%' : 'fikse') + ':</strong> -' + this.formatPrice(order.discount_amount) + '</p>' : '') +
        (order.has_vat === true || order.has_vat === 1 ? 
          '<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">' +
          '<p><strong>Shuma para TVSH:</strong> ' + (order.amount_before_vat ? this.formatPrice(order.amount_before_vat) : (order.total_amount ? this.formatPrice(parseFloat(order.total_amount) / 1.18) : '-')) + '</p>' +
          '<p><strong>TVSH (18%):</strong> ' + (order.vat_amount ? this.formatPrice(order.vat_amount) : (order.total_amount ? this.formatPrice(parseFloat(order.total_amount) - (parseFloat(order.total_amount) / 1.18)) : '-')) + '</p>' +
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
        // If location data exists, show only location name, otherwise show business/customer name
        (order.location_unit_name || order.location_street_number ? (
          '<p style="font-size: 12px; color: #6b7280; margin-top: 8px;">' + (order.location_unit_name || '') + '</p>' +
          (order.location_street_number ? '<p style="font-size: 12px; color: #6b7280; margin-top: 2px;">' + order.location_street_number + '</p>' : '')
        ) : (
          '<p style="font-size: 12px; color: #6b7280; margin-top: 8px;">' + (order.business_name || order.customer_name || '') + '</p>'
        )) +
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
        'let orderText = "📦 Faturë " + orderData.order_number + "\\n\\nKlienti: " + orderData.customer_name + " — " + orderData.business_name + "\\nQyteti: " + orderData.city + "\\nTelefon: " + orderData.phone;' +
        (order.location_unit_name ? 'orderText += "\\n📍 Pika/Njësia: " + orderData.location_unit_name;' : '') +
        (order.location_street_number ? 'orderText += "\\nAdresa: " + orderData.location_street_number;' : '') +
        (order.location_city ? 'orderText += "\\nVendi/Qyteti: " + orderData.location_city;' : '') +
        (order.location_phone ? 'orderText += "\\nTelefon: " + orderData.location_phone;' : '') +
        (order.location_viber ? 'orderText += "\\nViber: " + orderData.location_viber;' : '') +
        'orderText += "\\n\\nTotal: " + (orderData.total_amount ? orderData.total_amount.toFixed(2) + " €" : "Sipas kërkesës");' +
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
        'let orderText = "📦 Faturë " + orderData.order_number + "\\n\\nKlienti: " + orderData.customer_name + " — " + orderData.business_name + "\\nQyteti: " + orderData.city + "\\nTelefon: " + orderData.phone;' +
        (order.location_unit_name ? 'orderText += "\\n📍 Pika/Njësia: " + orderData.location_unit_name;' : '') +
        (order.location_street_number ? 'orderText += "\\nAdresa: " + orderData.location_street_number;' : '') +
        (order.location_city ? 'orderText += "\\nVendi/Qyteti: " + orderData.location_city;' : '') +
        (order.location_phone ? 'orderText += "\\nTelefon: " + orderData.location_phone;' : '') +
        (order.location_viber ? 'orderText += "\\nViber: " + orderData.location_viber;' : '') +
        'orderText += "\\n\\nTotal: " + (orderData.total_amount ? orderData.total_amount.toFixed(2) + " €" : "Sipas kërkesës");' +
        'const whatsappUrl = "https://wa.me/?text=" + encodeURIComponent(orderText);' +
        'window.open(whatsappUrl, "_blank");' +
        '}' +
        'function shareToGmail() {' +
        'const subject = encodeURIComponent("Faturë " + orderData.order_number);' +
        'let bodyText = "Faturë: " + orderData.order_number + "\\n\\nKlienti: " + orderData.customer_name + " — " + orderData.business_name + "\\nQyteti: " + orderData.city + "\\nTelefon: " + orderData.phone;' +
        (order.location_unit_name ? 'bodyText += "\\n📍 Pika/Njësia: " + orderData.location_unit_name;' : '') +
        (order.location_street_number ? 'bodyText += "\\nAdresa: " + orderData.location_street_number;' : '') +
        (order.location_city ? 'bodyText += "\\nVendi/Qyteti: " + orderData.location_city;' : '') +
        (order.location_phone ? 'bodyText += "\\nTelefon: " + orderData.location_phone;' : '') +
        (order.location_viber ? 'bodyText += "\\nViber: " + orderData.location_viber;' : '') +
        'bodyText += "\\n\\nTotal: " + (orderData.total_amount ? orderData.total_amount.toFixed(2) + " €" : "Sipas kërkesës");' +
        'const body = encodeURIComponent(bodyText);' +
        'const gmailUrl = "mailto:svalon95@gmail.com?subject=" + subject + "&body=" + body;' +
        'window.location.href = gmailUrl;' +
        '}' +
        scriptClose +
        '</body>' +
        '</html>'

      printWindow.document.write(htmlContent)

      printWindow.document.close()
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
</style>

