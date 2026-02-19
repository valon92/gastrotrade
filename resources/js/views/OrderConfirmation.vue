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
                  :src="item.image_path" 
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
                      ({{ item.pieces_per_package }} copa/kompleti)
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
        <p class="text-sm text-gray-600 mb-6 text-center">
          Dërgo porosinë në emailin zyrtar të GastroTrade dhe në Viber
        </p>
        
        <div class="space-y-3">
          <button 
            @click="sendOrderToEmail"
            :disabled="sendingEmail"
            :class="[
              'w-full font-medium py-4 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-3 text-lg',
              sendingEmail
                ? 'bg-gray-400 text-white cursor-not-allowed'
                : 'bg-blue-600 hover:bg-blue-700 text-white shadow-lg'
            ]"
          >
            <span v-if="sendingEmail">⏳</span>
            <span v-else>📧</span>
            {{ sendingEmail ? 'Duke dërguar në email...' : 'Dërgo në Email Zyrtar' }}
          </button>
          
          <div class="flex items-center gap-2 my-4">
            <div class="flex-1 border-t border-gray-300"></div>
            <span class="text-sm text-gray-500">ose</span>
            <div class="flex-1 border-t border-gray-300"></div>
          </div>
          
          <button 
            @click="sendOrderToViber('+38348756646')"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-4 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-3 text-lg"
          >
            💬 Dërgo në Viber
            <span class="text-base">+383 48 75 66 46</span>
          </button>
          
          <button 
            @click="sendOrderToViber('+38344824314')"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-4 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-3 text-lg"
          >
            💬 Dërgo në Viber
            <span class="text-base">+383 44 82 43 14</span>
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
            📧 svalon95@gmail.com<br>
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

export default {
  name: 'OrderConfirmation',
  data() {
    return {
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
      emailSent: false
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
    sendOrderToViber(phoneNumber) {
      // Validate customer data
      if (
        !this.customerData ||
        !this.customerData.name ||
        !this.customerData.storeName ||
        !this.customerData.fiscalNumber ||
        !this.customerData.city
      ) {
        alert('Ju lutem plotësoni të gjitha fushat e detyrueshme para dërgimit të porosisë!')
        this.$router.push('/shporta')
        return
      }

      // Validate cart has items
      if (!this.cartItems || this.cartItems.length === 0) {
        alert('Shporta juaj është e zbrazët!')
        this.$router.push('/produktet')
        return
      }

      try {
        // Validate cartStore exists
        if (!this.cartStore) {
          console.error('cartStore is not available')
          alert('Gabim: Shporta nuk është e disponueshme. Ju lutem rifreskoni faqen.')
          return
        }

        // Validate cartStore has items
        if (!this.cartStore.items || this.cartStore.items.length === 0) {
          console.error('cartStore.items is empty')
          alert('Shporta juaj është e zbrazët!')
          this.$router.push('/produktet')
          return
        }

        // Generate message (already encoded)
        let encodedMessage
        try {
          encodedMessage = this.cartStore.generateOrderMessage(this.customerData)
        } catch (genError) {
          console.error('Error generating message:', genError)
          const errorMessage = genError.message || 'Gabim i panjohur'
          
          if (errorMessage.includes('zbrazët')) {
            alert('Shporta juaj është e zbrazët! Ju lutem shtoni produkte në shportë para dërgimit të porosisë.')
            this.$router.push('/produktet')
          } else {
            alert(`Gabim në gjenerimin e mesazhit: ${errorMessage}\n\nJu lutem kontrolloni që të gjitha produktet janë të shtuar në shportë dhe provoni përsëri.`)
          }
          return
        }

        // Validate message was generated
        if (!encodedMessage || encodedMessage.length === 0) {
          console.error('Generated message is empty')
          alert('Gabim në gjenerimin e mesazhit. Ju lutem provoni përsëri.')
          return
        }

        // Decode message
        let decodedMessage
        try {
          decodedMessage = decodeURIComponent(encodedMessage)
        } catch (decodeError) {
          console.error('Error decoding message:', decodeError)
          // Try to use the message as is if decoding fails
          decodedMessage = encodedMessage
        }
        
        // Validate decoded message
        if (!decodedMessage || decodedMessage.length === 0) {
          console.error('Decoded message is empty')
          alert('Gabim në dekodimin e mesazhit. Ju lutem provoni përsëri.')
          return
        }
        
        // Clean phone number (remove + and spaces)
        const cleanPhone = phoneNumber.replace(/[\s\+]/g, '')
        
        // Format phone number for display
        const displayPhone = phoneNumber.replace('+383', '+383 ').replace(/(\d{2})(\d{3})(\d{3})/, '$1 $2 $3')
        
        // Try to open Viber
        // Format: viber://chat?number= (for mobile) or viber://pa?chatURI= (for desktop)
        const viberUrl = `viber://chat?number=${cleanPhone}`
        
        // Copy message to clipboard first
        this.copyToClipboard(decodedMessage, () => {
          // After copying, try to open Viber
          try {
            // Try to open Viber app
            window.location.href = viberUrl
            
            // Show instruction message
            setTimeout(() => {
              alert(`✅ Mesazhi u kopjua në clipboard!\n\n📱 Numri i Viber: ${displayPhone}\n\nNëse Viber u hap, thjesht ngjisni mesazhin (Ctrl+V ose Cmd+V) dhe dërgoni.\n\nNëse Viber nuk u hap, hapni Viber manualisht dhe dërgoni mesazhin në numrin: ${displayPhone}`)
            }, 300)
          } catch (error) {
            console.error('Error opening Viber:', error)
            // If opening fails, just show the message
            alert(`✅ Mesazhi u kopjua në clipboard!\n\n📱 Numri i Viber: ${displayPhone}\n\nJu lutem hapni Viber manualisht dhe dërgoni mesazhin në numrin: ${displayPhone}`)
          }
        })
      } catch (error) {
        console.error('Error in sendOrderToViber:', error)
        alert('Ndodhi një gabim gjatë dërgimit të porosisë. Ju lutem provoni përsëri.')
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
      // Validate customer data
      if (
        !this.customerData ||
        !this.customerData.name ||
        !this.customerData.storeName ||
        !this.customerData.fiscalNumber ||
        !this.customerData.city
      ) {
        alert('Ju lutem plotësoni të gjitha fushat e detyrueshme para dërgimit të porosisë!')
        this.$router.push('/shporta')
        return
      }

      // Validate cart has items
      if (!this.cartItems || this.cartItems.length === 0) {
        alert('Shporta juaj është e zbrazët!')
        this.$router.push('/produktet')
        return
      }

      this.sendingEmail = true
      this.emailSent = false

      try {
        // Get order number from route params or generate one
        const orderNumber = this.$route.params.orderNumber || `GT-${new Date().toISOString().slice(0,10).replace(/-/g,'')}-${Date.now().toString().slice(-6)}`
        
        const orderData = {
          order_number: orderNumber,
          created_at: new Date().toISOString()
        }

        const response = await axios.post('/api/orders/send-email', {
          order_data: orderData,
          customer_data: this.customerData,
          cart_items: this.cartItems,
          total_items: this.totalItems,
          total_price: this.totalPrice
        })

        if (response.data.success) {
          this.emailSent = true
          alert('✅ Porosia u dërgua me sukses në emailin zyrtar të GastroTrade!\n\nEmail: svalon95@gmail.com')
        } else {
          throw new Error(response.data.message || 'Gabim i panjohur')
        }
      } catch (error) {
        console.error('Error sending order email:', error)
        const errorMessage = error.response?.data?.message || error.message || 'Gabim gjatë dërgimit të emailit'
        alert(`❌ ${errorMessage}\n\nJu lutem provoni përsëri ose dërgoni porosinë nëpërmjet Viber.`)
      } finally {
        this.sendingEmail = false
      }
    }
  }
}
</script>

