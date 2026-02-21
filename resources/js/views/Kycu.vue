<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2 text-center">
        Kyçu
      </h1>
      <p class="text-center text-gray-600 mb-8">
        Të dhënat e biznesit
      </p>

      <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <p class="text-sm text-gray-600 mb-2">
          Klientët e regjistruar me çmime të caktuara: plotësoni më poshtë që të identifikoheni. Kur shtoni produkte, do të shfaqen çmimet tuaja.
        </p>
        <p class="text-xs text-gray-500 mb-4">
          Çmimet vendosen nga kompania GastroTrade, jo nga klientët.
        </p>

        <div class="text-sm text-gray-600 bg-blue-50 border border-blue-100 rounded-lg p-3 mb-6">
          <p class="mb-3">
            <strong>Klient i ri?</strong> Nëse dëshironi çmime të personalizuara për biznesin tuaj, fillimisht kontaktoni menaxhmentin e GastroTrade. Pas kontaktit do t'ju vendosen çmimet përkatëse dhe do të shfaqen këtu kur të identifikoheni.
          </p>
          <router-link
            to="/kontakt"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
          >
            Kontakto menaxhmentin
          </router-link>
        </div>

        <div v-if="hasClientPrices" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex flex-wrap items-center justify-between gap-3">
          <p class="text-sm text-green-800">
            ✅ Klienti i identifikuar: <strong>{{ cartStore.client.name }}</strong>. Kur shtoni produkte në shportë, do të aplikohen çmimet tuaja.
          </p>
          <button
            type="button"
            @click="logoutClient"
            class="shrink-0 px-3 py-1.5 text-sm font-medium text-green-800 bg-white border border-green-300 rounded-lg hover:bg-green-100 transition-colors"
          >
            Dil nga llogaria
          </button>
        </div>
        <div v-else-if="identifyingClient" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <p class="text-sm text-blue-800">🔍 Duke identifikuar klientin...</p>
        </div>
        <div v-else-if="loginError" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-800">{{ loginError }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label for="kycuCustomerName" class="block text-sm font-medium text-gray-700 mb-1">Emri i porositësit</label>
            <input
              id="kycuCustomerName"
              type="text"
              v-model="customerData.name"
              @input="persistCustomerData"
              placeholder="Emri juaj"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            />
          </div>
          <div>
            <label for="kycuStoreName" class="block text-sm font-medium text-gray-700 mb-1">Emri i biznisit <span class="text-red-500">*</span></label>
            <input
              id="kycuStoreName"
              type="text"
              v-model="customerData.storeName"
              placeholder="Emri i biznesit"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            />
          </div>
          <div>
            <label for="kycuFiscalNumber" class="block text-sm font-medium text-gray-700 mb-1">Numri fiskal <span class="text-red-500">*</span></label>
            <input
              id="kycuFiscalNumber"
              type="text"
              v-model="customerData.fiscalNumber"
              placeholder="Nr. fiskal"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 uppercase tracking-wide"
            />
          </div>
          <div>
            <label for="kycuCity" class="block text-sm font-medium text-gray-700 mb-1">Qyteti <span class="text-red-500">*</span></label>
            <input
              id="kycuCity"
              type="text"
              v-model="customerData.city"
              @input="persistCustomerData"
              placeholder="Qyteti"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            />
          </div>
          <div>
            <label for="kycuPhone" class="block text-sm font-medium text-gray-700 mb-1">Telefon / Viber</label>
            <input
              id="kycuPhone"
              type="tel"
              v-model="customerData.phone"
              @input="persistCustomerData"
              placeholder="+383 XX XXX XXX"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            />
          </div>
          <div class="sm:col-span-2">
            <label for="kycuPassword" class="block text-sm font-medium text-gray-700 mb-1">Fjalëkalimi</label>
            <div class="relative">
              <input
                id="kycuPassword"
                :type="showPassword ? 'text' : 'password'"
                v-model="customerData.password"
                @input="scheduleClientIdentification"
                placeholder="Vendosni fjalëkalimin (nëse ju është caktuar nga menaxhmenti)"
                autocomplete="current-password"
                class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700 rounded focus:outline-none focus:ring-2 focus:ring-primary-500/30"
                :aria-label="showPassword ? 'Fshih fjalëkalimin' : 'Shfaq fjalëkalimin'"
              >
              <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              </button>
            </div>
            <p class="mt-1 text-xs text-gray-500">
              Për siguri dhe privatësi. Nëse menaxhmenti ju ka caktuar fjalëkalim, vendoseni këtu.
            </p>
          </div>
        </div>

        <p class="mt-4 text-xs text-gray-500">
          Pas plotësimit të emrit të biznesit dhe numrit fiskal, identifikimi bëhet automatikisht nëse jeni klient i regjistruar.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
          <router-link
            to="/shporta"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-800 font-medium rounded-lg hover:bg-gray-200 transition-colors"
          >
            Shiko shportën
          </router-link>
          <router-link
            to="/produktet"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors"
          >
            Shiko produktet
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import cartStore from '../store/cart'
import axios from 'axios'

export default {
  name: 'Kycu',
  data() {
    return {
      cartStore,
      customerData: {
        name: '',
        storeName: '',
        fiscalNumber: '',
        city: '',
        phone: '',
        password: ''
      },
      showPassword: false,
      identifyingClient: false,
      loginError: null,
      phoneDebounce: null
    }
  },
  computed: {
    hasClientPrices() {
      return this.cartStore.client && Object.keys(this.cartStore.clientPrices).length > 0
    }
  },
  watch: {
    'customerData.storeName'() {
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

    // Plotëso vetëm fushat bosh nga klienti i kyqur, që të mos mbishkruhen ndryshimet e përdoruesit
    if (this.cartStore.client) {
      const c = this.cartStore.client
      if (!this.customerData.name?.trim()) this.customerData.name = c.name || ''
      if (!this.customerData.storeName?.trim()) this.customerData.storeName = c.store_name || ''
      if (!this.customerData.fiscalNumber?.trim()) this.customerData.fiscalNumber = c.fiscal_number || ''
      if (!this.customerData.city?.trim()) this.customerData.city = c.city || ''
      if (!this.customerData.phone?.trim()) this.customerData.phone = c.phone || ''
      this.persistCustomerData()
    }

    if (this.customerData.storeName && this.customerData.fiscalNumber) {
      this.identifyClient(this.customerData.storeName, this.customerData.fiscalNumber)
    }
  },
  beforeUnmount() {
    if (this.phoneDebounce) clearTimeout(this.phoneDebounce)
  },
  methods: {
    persistCustomerData() {
      const toStore = { ...this.customerData }
      delete toStore.password
      // Ruaj duke mbajtur të dhënat e tjera (p.sh. lokacioni nga shporta) që të jenë konsistente
      const existing = localStorage.getItem('gastrotrade_customer_data')
      const merged = existing ? { ...JSON.parse(existing), ...toStore } : toStore
      delete merged.password
      localStorage.setItem('gastrotrade_customer_data', JSON.stringify(merged))
    },
    scheduleClientIdentification() {
      if (this.phoneDebounce) clearTimeout(this.phoneDebounce)
      this.persistCustomerData()

      const businessName = (this.customerData.storeName || '').trim()
      const fiscalNumber = (this.customerData.fiscalNumber || '').trim()

      if (businessName.length < 2 || fiscalNumber.length < 3) {
        this.cartStore.clearClient()
        return
      }

      this.phoneDebounce = setTimeout(() => {
        this.identifyClient(businessName, fiscalNumber)
      }, 600)
    },
    async identifyClient(businessName, fiscalNumber) {
      if (!businessName || !fiscalNumber) {
        this.cartStore.clearClient()
        this.loginError = null
        return
      }
      if (this.identifyingClient) return

      this.identifyingClient = true
      this.loginError = null
      try {
        const payload = {
          business_name: businessName.trim(),
          fiscal_number: fiscalNumber.trim()
        }
        if (this.customerData.password !== undefined && this.customerData.password !== null) {
          payload.password = String(this.customerData.password)
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
          this.persistCustomerData()
          this.$router.push('/shporta')
          return
        }
        this.cartStore.clearClient()
      } catch (error) {
        if (error.response?.status === 401 && error.response?.data?.message) {
          this.loginError = error.response.data.message
        } else {
          this.loginError = 'Identifikimi dështoi. Kontrolloni të dhënat ose fjalëkalimin.'
        }
        this.cartStore.clearClient()
      } finally {
        this.identifyingClient = false
      }
    },
    logoutClient() {
      this.cartStore.clearClient()
      this.loginError = null
      this.customerData = {
        name: '',
        storeName: '',
        fiscalNumber: '',
        city: '',
        phone: '',
        password: ''
      }
      localStorage.removeItem('gastrotrade_customer_data')
    }
  }
}
</script>
