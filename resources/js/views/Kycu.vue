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
        phone: ''
      },
      identifyingClient: false,
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

    if (this.cartStore.client) {
      const c = this.cartStore.client
      this.customerData.name = c.name || this.customerData.name
      this.customerData.storeName = c.store_name || this.customerData.storeName
      this.customerData.fiscalNumber = c.fiscal_number || this.customerData.fiscalNumber
      this.customerData.city = c.city || this.customerData.city
      this.customerData.phone = c.phone || this.customerData.phone
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
      localStorage.setItem('gastrotrade_customer_data', JSON.stringify(this.customerData))
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
        return
      }
      if (this.identifyingClient) return

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
          this.persistCustomerData()
          return
        }
        this.cartStore.clearClient()
      } catch (error) {
        console.error('Error identifying client:', error)
        this.cartStore.clearClient()
      } finally {
        this.identifyingClient = false
      }
    },
    logoutClient() {
      this.cartStore.clearClient()
      this.customerData = {
        name: '',
        storeName: '',
        fiscalNumber: '',
        city: '',
        phone: ''
      }
      localStorage.removeItem('gastrotrade_customer_data')
    }
  }
}
</script>
