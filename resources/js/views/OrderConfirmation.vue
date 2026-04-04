<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="text-6xl mb-4">✅</div>
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
          Konfirmimi i Porosisë
        </h1>
        <p class="text-lg text-gray-600">
          Ju lutem kontrolloni të dhënat para dërgimit të porosisë
        </p>
      </div>

      <!-- Order Details Card -->
      <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
        <!-- Customer Information -->
        <div class="mb-8 pb-8 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            👤 Të Dhënat e Porositësit
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Emri i Porositësit</label>
              <p class="text-lg font-semibold text-gray-900">{{ customerData.name }}</p>
            </div>
            <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Emri i Biznisit</label>
              <p class="text-lg font-semibold text-gray-900">{{ customerData.storeName }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Numri Fiskal</label>
              <p class="text-lg font-semibold text-gray-900">{{ customerData.fiscalNumber }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Qyteti</label>
              <p class="text-lg font-semibold text-gray-900">{{ customerData.city }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Telefon/Viber</label>
              <p class="text-lg font-semibold text-primary-600">{{ customerData.phone }}</p>
            </div>
            <div v-if="customerData.locationUnitName">
              <label class="block text-sm font-medium text-gray-500 mb-1">Pika/Njësia</label>
              <p class="text-lg font-semibold text-gray-900">{{ customerData.locationUnitName }}</p>
            </div>
            <div v-if="customerData.locationStreetNumber">
              <label class="block text-sm font-medium text-gray-500 mb-1">Adresa</label>
              <p class="text-lg font-semibold text-gray-900">{{ customerData.locationStreetNumber }}</p>
            </div>
            <div v-if="customerData.locationCity">
              <label class="block text-sm font-medium text-gray-500 mb-1">Vendi/Qyteti i Pikës</label>
              <p class="text-lg font-semibold text-gray-900">{{ customerData.locationCity }}</p>
            </div>
            <div v-if="customerData.locationPhone">
              <label class="block text-sm font-medium text-gray-500 mb-1">Telefon i Pikës</label>
              <p class="text-lg font-semibold text-primary-600">{{ customerData.locationPhone }}</p>
            </div>
          </div>
        </div>

        <!-- Products -->
        <div class="mb-8 pb-8 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            🛒 Produktet e Zgjedhura
          </h2>
          <div class="space-y-4">
            <div 
              v-for="(item, index) in cartItems" 
              :key="item.id"
              class="flex flex-col sm:flex-row gap-4 p-4 bg-gray-50 rounded-lg"
            >
              <div class="flex-shrink-0">
                <img 
                  :src="item.image_url || item.image_path" 
                  :alt="item.name"
                  class="w-20 h-20 object-cover rounded-lg"
                />
              </div>
              <div class="flex-grow">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                  {{ index + 1 }}. {{ item.name }}
                </h3>
                <div class="flex flex-wrap gap-4 text-sm">
                  <span class="text-gray-600">
                    <strong v-if="item.sold_by_package && item.pieces_per_package">Komplete:</strong>
                    <strong v-else>Sasia:</strong>
                    {{ item.quantity }}
                    <span v-if="item.sold_by_package && item.pieces_per_package" class="text-gray-500">
                      ({{ item.quantity * item.pieces_per_package }} copa)
                    </span>
                  </span>
                  <span v-if="item.price" class="text-primary-600 font-semibold">
                    <strong>Çmimi:</strong> {{ formatPrice(item.price) }}/copë
                    <span v-if="item.sold_by_package && item.pieces_per_package" class="text-gray-500 text-xs">
                      ({{ item.pieces_per_package }} copa/Komplete)
                    </span>
                  </span>
                  <span v-else class="text-gray-600">
                    <strong>Çmimi:</strong> Sipas kërkesës
                  </span>
                  <span v-if="item.price" class="text-primary-600 font-bold">
                    <strong>Total:</strong> {{ formatPrice(getItemTotal(item)) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div>
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            📊 Përmbledhje e Porosisë
          </h2>
          <div class="space-y-3">
            <div class="flex justify-between text-lg">
              <span class="text-gray-700">Total Produkte:</span>
              <span class="font-semibold text-gray-900">{{ totalItems }} produkt(e)</span>
            </div>
            <div v-if="totalPrice > 0" class="flex justify-between text-lg">
              <span class="text-gray-700">Vlera Totale:</span>
              <span class="font-bold text-primary-600 text-2xl">
                {{ formatPrice(totalPrice) }}
              </span>
            </div>
            <div v-else class="text-gray-600">
              Çmimi sipas kërkesës për të gjitha produktet
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">
          Dërgo Porosinë
        </h3>

        <div
          v-if="shareNotice"
          class="mb-5 rounded-xl border px-4 py-3 text-sm leading-relaxed flex gap-3 items-start"
          :class="
            shareNotice.type === 'error'
              ? 'bg-red-50 border-red-200 text-red-900'
              : 'bg-emerald-50 border-emerald-200 text-emerald-950'
          "
          role="status"
        >
          <div class="flex-1 space-y-1">
            <p v-for="(line, idx) in shareNotice.lines" :key="idx">
              {{ line }}
            </p>
            <div v-if="lastInvoicePdfUrl" class="pt-2">
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg bg-white px-3 py-2 text-xs font-semibold text-emerald-800 border border-emerald-200 hover:bg-emerald-50"
                @click="openPdf(lastInvoicePdfUrl)"
              >
                Hap PDF
              </button>
            </div>
          </div>
          <button
            type="button"
            class="shrink-0 rounded-lg px-2 py-1 text-xs font-medium text-gray-600 hover:bg-white/80"
            @click="shareNotice = null"
          >
            Mbyll
          </button>
        </div>

        <p class="text-sm text-gray-600 mb-6 text-center">
          Dërgoni përmbledhjen / faturën te AronTrade: email, Viber (të dyjat linjat e biznesit) ose WhatsApp. Me një klik, platforma e krijon faturën PDF në background; email e dërgon si attachment, ndërsa Viber/WhatsApp marrin mesazhin me link të PDF (clipboard përdoret kur duhet).
        </p>
        
        <div class="space-y-3">
          <button 
            @click="sendOrderToEmail"
            :disabled="sendingEmail || shareBusy"
            :class="[
              'w-full font-medium py-4 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-3 text-lg',
              sendingEmail || shareBusy
                ? 'bg-gray-400 text-white cursor-not-allowed'
                : 'bg-blue-600 hover:bg-blue-700 text-white shadow-lg'
            ]"
          >
            <span v-if="sendingEmail">⏳</span>
            <span v-else>📧</span>
            {{ sendingEmail ? 'Duke përgatitur / dërguar...' : 'Dërgo në Email Zyrtar' }}
          </button>
          
          <div class="flex items-center gap-2 my-4">
            <div class="flex-1 border-t border-gray-300"></div>
            <span class="text-sm text-gray-500">ose</span>
            <div class="flex-1 border-t border-gray-300"></div>
          </div>
          
          <button 
            type="button"
            @click="sendOrderToViber"
            :disabled="shareBusy"
            class="w-full bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-4 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-3 text-lg"
          >
            <span v-if="sharingViber">⏳</span>
            <span v-else>💬</span>
            {{ sharingViber ? 'Duke përgatitur...' : 'Dërgo në Viber' }}
          </button>
          
          <button 
            type="button"
            @click="sendOrderToWhatsApp"
            :disabled="shareBusy"
            class="w-full bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-4 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-3 text-lg"
          >
            <span v-if="sharingWhatsApp">⏳</span>
            <span v-else>📱</span>
            {{ sharingWhatsApp ? 'Duke përgatitur...' : 'Dërgo në WhatsApp' }}
          </button>

          <div class="flex gap-3 mt-6">
            <button 
              @click="goBackToCart"
              class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-4 rounded-lg transition-colors duration-200"
            >
              ← Kthehu te Shporta
            </button>
            <button 
              @click="goToProducts"
              class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200"
            >
              Shiko Produkte të Tjera →
            </button>
          </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200">
          <p class="text-sm text-gray-600 text-center">
            Ose na kontaktoni direkt:<br>
            📞 048 75 66 46 / 044 82 43 14<br>
            📧 {{ officialOrderEmail }}<br>
            📍 Ferizaj, Kosovë, Rruga Lidhja E Prizerent
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import cartStore from '../store/cart'
import axios from 'axios'
import { OFFICIAL_ORDER_EMAIL, VIBER_NUMBERS_E164, WHATSAPP_ORDER_E164 } from '../config/site'
import { buildInvoiceHtmlAndText, openInvoicePrintWindowForPdf } from '../utils/orderInvoiceBuilder'

export default {
  name: 'OrderConfirmation',
  data() {
    return {
      officialOrderEmail: OFFICIAL_ORDER_EMAIL,
      cartStore: cartStore,
      customerData: {
        name: '',
        storeName: '',
        fiscalNumber: '',
        city: '',
        phone: '',
        locationUnitName: '',
        locationStreetNumber: '',
        locationPhone: '',
        locationViber: '',
        locationCity: ''
      },
      cartItems: [],
      totalItems: 0,
      totalPrice: 0,
      sendingEmail: false,
      emailSent: false,
      sharingViber: false,
      sharingWhatsApp: false,
      viberNextIndex: 0,
      lastInvoicePdfUrl: '',
      /** { type: 'success'|'error', lines: string[] } | null — pa alert() bllokues */
      shareNotice: null
    }
  },
  computed: {
    shareBusy() {
      return this.sharingViber || this.sharingWhatsApp
    }
  },
  mounted() {
    // Get customer data from route params or localStorage
    const customerDataFromRoute = this.$route.params.customerData
    if (customerDataFromRoute) {
      try {
        this.customerData = JSON.parse(decodeURIComponent(customerDataFromRoute))
      } catch (e) {
        console.error('Error parsing customer data from route:', e)
        // Fallback to localStorage
        this.loadCustomerDataFromStorage()
      }
    } else {
      // Fallback: try to get from localStorage
      this.loadCustomerDataFromStorage()
    }

    // Get cart items
    this.cartItems = this.cartStore.items || []
    this.totalItems = this.cartStore.totalItems || 0
    this.totalPrice = this.cartStore.totalPrice || 0

    // Validate data
    if (this.cartItems.length === 0) {
      alert('Shporta juaj është e zbrazët!')
      this.$router.push('/produktet')
      return
    }

    if (
      !this.customerData ||
      !this.customerData.name ||
      !this.customerData.storeName ||
      !this.customerData.fiscalNumber ||
      !this.customerData.city
    ) {
      alert('Të dhënat e klientit nuk janë të plotë!')
      this.$router.push('/shporta')
      return
    }
  },
  methods: {
    loadCustomerDataFromStorage() {
      const savedData = localStorage.getItem('gastrotrade_order_data')
      if (savedData) {
        try {
          this.customerData = JSON.parse(savedData)
        } catch (e) {
          console.error('Error parsing customer data from storage:', e)
          this.$router.push('/shporta')
        }
      } else {
        // Also try to get from customer data in localStorage
        const customerDataStr = localStorage.getItem('gastrotrade_customer_data')
        if (customerDataStr) {
          try {
            this.customerData = JSON.parse(customerDataStr)
          } catch (e) {
            console.error('Error parsing customer data:', e)
          }
        }
        
        // If still no data, redirect to cart
        if (
          !this.customerData ||
          !this.customerData.name ||
          !this.customerData.storeName ||
          !this.customerData.fiscalNumber ||
          !this.customerData.city
        ) {
          this.$router.push('/shporta')
        }
      }
    },
    formatPrice(price) {
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },
    getItemTotal(item) {
      if (!item.price) return 0
      // For products sold by package: price per piece × quantity (packages) × pieces per package
      if (item.sold_by_package && item.pieces_per_package) {
        return item.price * item.quantity * item.pieces_per_package
      }
      // For regular products: price per piece × quantity
      return item.price * item.quantity
    },
    validateOrderContext() {
      if (
        !this.customerData ||
        !this.customerData.name ||
        !this.customerData.storeName ||
        !this.customerData.fiscalNumber ||
        !this.customerData.city
      ) {
        alert('Ju lutem plotësoni të gjitha fushat e detyrueshme para dërgimit të porosisë!')
        this.$router.push('/shporta')
        return false
      }
      if (!this.cartItems || this.cartItems.length === 0) {
        alert('Shporta juaj është e zbrazët!')
        this.$router.push('/produktet')
        return false
      }
      if (!this.cartStore || !this.cartStore.items || this.cartStore.items.length === 0) {
        alert('Gabim: Shporta nuk është e disponueshme. Ju lutem rifreskoni faqen.')
        return false
      }
      return true
    },
    buildOrderModelForInvoice() {
      const orderNumber =
        this.$route.params.orderNumber ||
        `GT-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-${Date.now().toString().slice(-6)}`
      const nowIso = new Date().toISOString()
      return {
        order_number: orderNumber,
        customer_name: this.customerData.name,
        business_name: this.customerData.storeName,
        fiscal_number: this.customerData.fiscalNumber,
        city: this.customerData.city,
        phone: this.customerData.phone || null,
        location_unit_name: this.customerData.locationUnitName || null,
        location_street_number: this.customerData.locationStreetNumber || null,
        location_city: this.customerData.locationCity || null,
        location_phone: this.customerData.locationPhone || null,
        location_viber: this.customerData.locationViber || null,
        total_items: this.totalItems,
        subtotal: null,
        discount_amount: 0,
        discount_type: null,
        discount_value: 0,
        total_amount: this.totalPrice > 0 ? this.totalPrice : null,
        has_vat: false,
        vat_amount: null,
        amount_before_vat: null,
        created_at: nowIso,
        is_paid: false,
        paid_at: null,
        items: this.cartItems.map(item => ({
          product_name: item.name,
          barcode: item.barcode != null ? item.barcode : '',
          quantity: item.quantity,
          sold_by_package: !!item.sold_by_package,
          pieces_per_package: item.pieces_per_package || null,
          unit_type: item.sold_by_package && item.pieces_per_package ? 'package' : 'cp',
          unit_price: item.price || null,
          discount_amount: 0,
          discount_type: null,
          discount_value: 0,
          total_price: item.price ? this.getItemTotal(item) : null
        }))
      }
    },
    /** Faturë HTML + tekst (i njëjti pamje si në shportë) */
    prepareInvoiceAssets() {
      const order = this.buildOrderModelForInvoice()
      return buildInvoiceHtmlAndText(order)
    },
    async generateInvoicePdfUrl(orderNumber, invoiceHtml) {
      const resp = await axios.post('/api/orders/invoice-pdf', {
        order_number: orderNumber,
        invoice_html: invoiceHtml
      })
      const data = resp?.data?.data
      const pdfUrl = data?.pdf_url
      const pdfPath = data?.pdf_path
      const resolvedUrl = pdfUrl || (pdfPath ? `${window.location.origin}${pdfPath}` : '')
      if (!resp?.data?.success || !resolvedUrl) {
        throw new Error(resp?.data?.message || 'Nuk u krijua linku i PDF.')
      }
      return resolvedUrl
    },
    scheduleInvoicePdf(html) {
      if (!html) return
      setTimeout(() => {
        openInvoicePrintWindowForPdf(html)
      }, 450)
    },
    pushShareNotice(type, lines) {
      this.shareNotice = { type, lines }
    },
    openViberChat(number) {
      // window.location është mënyra më e besueshme për URL scheme.
      // Në desktop, nëse Viber nuk është i instaluar/konfiguruar, shfletuesi mund të hap një tab bosh.
      window.location.href = `viber://chat?number=${number}`
    },
    openPdf(url) {
      if (!url) return
      // Same-tab navigation -> nuk bllokohet si popup.
      window.location.href = url
    },
    copyToClipboardPromise(text) {
      return new Promise(resolve => {
        if (!text || text.length === 0) {
          resolve()
          return
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard
            .writeText(text)
            .then(() => resolve())
            .catch(() => this.fallbackCopyToClipboard(text, resolve))
        } else {
          this.fallbackCopyToClipboard(text, resolve)
        }
      })
    },
    /**
     * Viber: kopjon tekstin e faturës, hap bisedën e parë; PDF hapet për «Ruaj si PDF».
     * Për linjën e dytë të biznesit: ngjisni përsëri në bisedën tjetër (pa shfaqur numra në UI).
     */
    async sendOrderToViber() {
      if (!this.validateOrderContext()) return
      if (this.sharingViber) return
      this.sharingViber = true
      this.shareNotice = null
      this.lastInvoicePdfUrl = ''
      try {
        const orderModel = this.buildOrderModelForInvoice()
        const { html, plainText } = buildInvoiceHtmlAndText(orderModel)
        const pdfUrl = await this.generateInvoicePdfUrl(orderModel.order_number, html)
        this.lastInvoicePdfUrl = pdfUrl
        const fullText = `${plainText}\n\nPDF: ${pdfUrl}`
        await this.copyToClipboardPromise(fullText)

        const nums = VIBER_NUMBERS_E164.length ? VIBER_NUMBERS_E164 : ['38348756646', '38344824314']
        const idx = this.viberNextIndex % nums.length
        const target = nums[idx]
        this.viberNextIndex = (idx + 1) % nums.length
        this.openViberChat(target)

        const lines = [
          '✅ PDF u krijua në background dhe u shtua te mesazhi.',
          '📋 Mesazhi + linku i PDF u kopjua në clipboard — ngjisni (Ctrl+V / Cmd+V) dhe dërgoni në Viber.',
          '✅ U hap linja e biznesit në Viber. Klikoni prapë “Dërgo në Viber” për ta hapur linjën tjetër.',
          'Në desktop pa Viber të instaluar, linku mund të hap një faqe bosh — në atë rast hapeni këtë faqe nga telefoni ku e keni Viber.',
          'Linku i PDF punon edhe nëse mesazhi pritet.'
        ]
        this.pushShareNotice('success', lines)
      } catch (e) {
        console.error(e)
        this.pushShareNotice('error', ['Gabim gjatë përgatitjes së dërgimit. Provoni përsëri.'])
      } finally {
        this.sharingViber = false
      }
    },
    /** WhatsApp: mesazh me tekst (wa.me); PDF përmes print dialog */
    async sendOrderToWhatsApp() {
      if (!this.validateOrderContext()) return
      if (this.sharingWhatsApp) return
      this.sharingWhatsApp = true
      this.shareNotice = null
      this.lastInvoicePdfUrl = ''
      try {
        const orderModel = this.buildOrderModelForInvoice()
        const { html, plainText } = buildInvoiceHtmlAndText(orderModel)
        const pdfUrl = await this.generateInvoicePdfUrl(orderModel.order_number, html)
        this.lastInvoicePdfUrl = pdfUrl
        const fullText = `${plainText}\n\nPDF: ${pdfUrl}`
        const waUrl = `https://wa.me/${WHATSAPP_ORDER_E164}?text=${encodeURIComponent(fullText)}`
        window.open(waUrl, '_blank', 'noopener,noreferrer')
        this.pushShareNotice('success', [
          '✅ PDF u krijua në background dhe u shtua si link në mesazh.',
          'WhatsApp u hap me përmbledhjen e porosisë (tekst + link). Mesazhi shumë i gjatë mund të pritet nga kufizimet e WhatsApp.',
          'Nëse WhatsApp nuk e pranon të gjithë tekstin, linku i PDF mbetet i mjaftueshëm.'
        ])
      } catch (e) {
        console.error(e)
        this.pushShareNotice('error', ['Gabim gjatë përgatitjes së dërgimit. Provoni përsëri.'])
      } finally {
        this.sharingWhatsApp = false
      }
    },
    copyToClipboard(text, callback) {
      if (!text || text.length === 0) {
        console.error('No text to copy')
        if (callback) callback()
        return
      }

      // Try modern clipboard API first
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
          console.log('Text copied to clipboard successfully')
          if (callback) {
            setTimeout(() => callback(), 100) // Small delay to ensure clipboard is ready
          }
        }).catch((err) => {
          console.error('Clipboard API failed, trying fallback:', err)
          // Fallback for older browsers
          this.fallbackCopyToClipboard(text, callback)
        })
      } else {
        // Fallback for older browsers
        this.fallbackCopyToClipboard(text, callback)
      }
    },
    fallbackCopyToClipboard(text, callback) {
      const textArea = document.createElement('textarea')
      textArea.value = text
      textArea.style.position = 'fixed'
      textArea.style.left = '-999999px'
      textArea.style.top = '-999999px'
      textArea.style.opacity = '0'
      document.body.appendChild(textArea)
      textArea.focus()
      textArea.select()
      
      try {
        const successful = document.execCommand('copy')
        document.body.removeChild(textArea)
        
        if (successful) {
          console.log('Text copied using fallback method')
          if (callback) {
            setTimeout(() => callback(), 100)
          }
        } else {
          console.error('Fallback copy command failed')
          // Last resort: show the message in a prompt
          const userCopied = confirm('Kopjoni mesazhin e mëposhtëm dhe klikoni OK:\n\n' + text.substring(0, 200) + '...')
          if (callback) callback()
        }
      } catch (err) {
        console.error('Fallback copy failed:', err)
        document.body.removeChild(textArea)
        // Last resort: show the message
        const userCopied = confirm('Kopjoni mesazhin e mëposhtëm dhe klikoni OK:\n\n' + text.substring(0, 200) + '...')
        if (callback) callback()
      }
    },
    goBackToCart() {
      this.$router.push('/shporta')
    },
    goToProducts() {
      this.$router.push('/produktet')
    },
    async sendOrderToEmail() {
      if (!this.validateOrderContext()) return

      this.shareNotice = null
      this.lastInvoicePdfUrl = ''
      this.sendingEmail = true
      this.emailSent = false

      try {
        const orderModel = this.buildOrderModelForInvoice()
        const { html } = buildInvoiceHtmlAndText(orderModel)
        // edhe për email e ruajmë linkun, që admini ta hapë nëse do (PDF ruhet në public storage)
        try {
          const pdfUrl = await this.generateInvoicePdfUrl(orderModel.order_number, html)
          this.lastInvoicePdfUrl = pdfUrl
        } catch (e) {
          // nëse PDF API dështoi, emaili ende mund të dërgohet pa link (por pa attachment s'ka kuptim).
          // e lëmë të vazhdojë; endpoint-i i emailit do të gjenerojë PDF nga invoice_html.
        }

        const orderData = {
          order_number: orderModel.order_number,
          created_at: orderModel.created_at
        }

        const response = await axios.post('/api/orders/send-email', {
          order_data: orderData,
          customer_data: this.customerData,
          cart_items: this.cartItems,
          total_items: this.totalItems,
          total_price: this.totalPrice,
          invoice_html: html
        })

        if (response.data.success) {
          this.emailSent = true
          const d = response.data.delivery
          if (d && d.reached_inbox === false) {
            console.warn('Order email not delivered to inbox (mail mode).', d)
          }
          this.pushShareNotice('success', [
            '✅ Porosia u dërgua për verifikim. Do t’ju kontaktojmë së shpejti.',
            '📎 Fatura PDF u krijua në background dhe u dërgua si bashkëngjitje në email.'
          ])
        } else {
          throw new Error(response.data.message || 'Gabim i panjohur')
        }
      } catch (error) {
        console.error('Error sending order email:', error)
        const errorMessage = error.response?.data?.message || error.message || 'Gabim gjatë dërgimit të emailit'
        this.pushShareNotice('error', [
          `❌ ${errorMessage}`,
          'Ju lutem provoni përsëri ose dërgoni porosinë nëpërmjet Viber.'
        ])
      } finally {
        this.sendingEmail = false
      }
    }
  }
}
</script>

