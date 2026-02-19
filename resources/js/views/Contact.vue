<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">
          Kontaktoni Me Ne
        </h1>
        <p class="text-xl md:text-2xl text-primary-100">
          Jemi këtu për t'ju ndihmuar me çdo pyetje ose kërkesë
        </p>
      </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Contact Form -->
        <div class="bg-white rounded-lg shadow-lg p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">
            Dërgoni një Mesazh
          </h2>
          
          <form @submit.prevent="submitForm" class="space-y-6">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Emri i Plotë
              </label>
              <input 
                type="text" 
                id="name" 
                v-model="form.name"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="Shkruani emrin tuaj të plotë"
              />
            </div>
            
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email
              </label>
              <input 
                type="email" 
                id="email" 
                v-model="form.email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="email@example.com"
              />
            </div>
            
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                Telefoni
              </label>
              <input 
                type="tel" 
                id="phone" 
                v-model="form.phone"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="+383 XX XXX XXX"
              />
            </div>
            
            <div>
              <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                Subjekti
              </label>
              <select 
                id="subject" 
                v-model="form.subject"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="">Zgjidhni një subjekt</option>
                <option value="general">Pyetje të Përgjithshme</option>
                <option value="products">Informacion për Produkte</option>
                <option value="order">Porosi</option>
                <option value="support">Mbështetje Teknike</option>
                <option value="other">Tjetër</option>
              </select>
            </div>
            
            <div>
              <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                Mesazhi
              </label>
              <textarea 
                id="message" 
                v-model="form.message"
                required
                rows="5"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="Shkruani mesazhin tuaj këtu..."
              ></textarea>
            </div>
            
            <button 
              type="submit" 
              :disabled="submitting"
              class="w-full btn-primary text-lg py-3 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="submitting">Duke dërguar...</span>
              <span v-else>Dërgo Mesazhin</span>
            </button>
          </form>
          
          <div v-if="message" class="mt-6 p-4 rounded-md" :class="messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            {{ message }}
          </div>
        </div>

        <!-- Contact Information -->
        <div class="space-y-8">
          <div class="bg-white rounded-lg shadow-lg p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">
              Informacion Kontakti
            </h3>
            
            <div class="space-y-6">
              <div class="flex items-start space-x-4">
                <div class="bg-primary-100 rounded-full p-3">
                  <span class="text-primary-600 text-xl">📧</span>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">Email</h4>
                  <p class="text-gray-600">svalon95@gmail.com</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="bg-primary-100 rounded-full p-3">
                  <span class="text-primary-600 text-xl">📞</span>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">Telefoni</h4>
                  <p class="text-gray-600">048 75 66 46</p>
                  <p class="text-gray-600">044 82 43 14</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="bg-primary-100 rounded-full p-3">
                  <span class="text-primary-600 text-xl">📍</span>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">Adresa</h4>
                  <p class="text-gray-600">Ferizaj, Kosovë</p>
                  <p class="text-gray-600">Rruga Lidhja E Prizerent</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="bg-primary-100 rounded-full p-3">
                  <span class="text-primary-600 text-xl">💬</span>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">Viber</h4>
                  <p class="text-gray-600">+383 48 75 66 46</p>
                  <p class="text-gray-600">+383 44 82 43 14</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="bg-primary-100 rounded-full p-3">
                  <span class="text-primary-600 text-xl">🕒</span>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">Orari i Punës</h4>
                  <p class="text-gray-600">E Hënë - E Premte: 08:00 - 18:00</p>
                  <p class="text-gray-600">E Shtunë: 09:00 - 15:00</p>
                  <p class="text-gray-600">E Dielë: Mbyllur</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow-lg p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">
              Na Gjeni
            </h3>
            <p class="text-sm text-gray-500 mb-3">
              Harta interaktive – Ferizaj, Rruga Lidhja E Prizerent
            </p>
            <div ref="mapContainer" class="rounded-lg overflow-hidden border border-gray-200 shadow-inner" style="height: 360px; min-height: 280px;"></div>
            <a
              href="https://www.openstreetmap.org/?mlat=42.3702&amp;mlon=21.1553#map=16/42.3702/21.1553"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-block mt-3 text-sm text-primary-600 hover:text-primary-700 font-medium"
            >
              Hap në OpenStreetMap →
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Contact',
  data() {
    return {
      form: {
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: ''
      },
      submitting: false,
      message: '',
      messageType: '',
      map: null
    }
  },
  mounted() {
    this.initMap()
  },
  beforeUnmount() {
    if (this.map) {
      this.map.remove()
      this.map = null
    }
  },
  methods: {
    initMap() {
      if (!this.$refs.mapContainer) return
      const loadLeaflet = () => {
        if (window.L) {
          this.createMap()
          return
        }
        const link = document.createElement('link')
        link.rel = 'stylesheet'
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
        link.crossOrigin = 'anonymous'
        document.head.appendChild(link)
        const script = document.createElement('script')
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
        script.crossOrigin = 'anonymous'
        script.onload = () => this.createMap()
        document.head.appendChild(script)
      }
      loadLeaflet()
    },
    createMap() {
      if (!this.$refs.mapContainer || !window.L) return
      this.map = window.L.map(this.$refs.mapContainer).setView([42.3702, 21.1553], 16)
      window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
      }).addTo(this.map)
      const icon = window.L.icon({
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41]
      })
      window.L.marker([42.3702, 21.1553], { icon })
        .addTo(this.map)
        .bindPopup('<strong>GastroTrade</strong><br>Ferizaj, Rruga Lidhja E Prizerent')
        .openPopup()
    },
    async submitForm() {
      this.submitting = true
      this.message = ''
      
      try {
        // Simulate form submission
        await new Promise(resolve => setTimeout(resolve, 2000))
        
        this.message = 'Mesazhi u dërgua me sukses! Do t\'ju kontaktojmë së shpejti.'
        this.messageType = 'success'
        
        // Reset form
        this.form = {
          name: '',
          email: '',
          phone: '',
          subject: '',
          message: ''
        }
      } catch (error) {
        this.message = 'Gabim në dërgimin e mesazhit. Ju lutemi provoni përsëri.'
        this.messageType = 'error'
      } finally {
        this.submitting = false
      }
    }
  }
}
</script>
