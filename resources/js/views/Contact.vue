<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-slate-100/70">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-slate-950 text-white py-20 sm:py-24">
      <div aria-hidden="true" class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.35),transparent_36%),radial-gradient(circle_at_bottom_right,rgba(37,99,235,0.28),transparent_34%)]"></div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <p class="inline-flex items-center rounded-full border border-white/20 bg-white/5 px-4 py-1.5 text-xs font-semibold tracking-[0.14em] text-primary-100 mb-5">
          ARONTRADE • KONTAKT
        </p>
        <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-6">
          Kontaktoni Me Ne
        </h1>
        <p class="text-lg md:text-2xl text-slate-200 max-w-3xl mx-auto">
          Jemi këtu për t'ju ndihmuar me çdo pyetje ose kërkesë
        </p>
      </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Contact Form -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-900/5 p-8">
          <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 mb-6">
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
                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl bg-slate-50/60 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
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
                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl bg-slate-50/60 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
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
                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl bg-slate-50/60 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
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
                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl bg-slate-50/60 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
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
                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl bg-slate-50/60 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                placeholder="Shkruani mesazhin tuaj këtu..."
              ></textarea>
            </div>
            
            <button 
              type="submit" 
              :disabled="submitting"
              class="w-full inline-flex justify-center items-center bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 text-lg py-3 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="submitting">Duke dërguar...</span>
              <span v-else>Dërgo Mesazhin</span>
            </button>
          </form>
          
          <div v-if="message" class="mt-6 p-4 rounded-xl border" :class="messageType === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'">
            {{ message }}
          </div>
        </div>

        <!-- Contact Information -->
        <div class="space-y-8">
          <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-900/5 p-8">
            <h3 class="text-2xl font-extrabold tracking-tight text-slate-900 mb-6">
              Informacion Kontakti
            </h3>
            
            <div class="space-y-6">
              <div class="flex items-start space-x-4">
                <div class="bg-primary-100 rounded-2xl p-3">
                  <span class="text-primary-600 text-xl">📞</span>
                </div>
                <div>
                  <h4 class="font-semibold text-slate-900">Telefoni</h4>
                  <p class="text-slate-600">048 75 66 46</p>
                  <p class="text-slate-600">044 82 43 14</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="bg-primary-100 rounded-2xl p-3">
                  <span class="text-primary-600 text-xl">📍</span>
                </div>
                <div>
                  <h4 class="font-semibold text-slate-900">Adresa</h4>
                  <p class="text-slate-600">Ferizaj, Kosovë</p>
                  <p class="text-slate-600">Rruga Lidhja E Prizerent</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="bg-primary-100 rounded-2xl p-3">
                  <span class="text-primary-600 text-xl">💬</span>
                </div>
                <div>
                  <h4 class="font-semibold text-slate-900">Viber</h4>
                  <p class="text-slate-600">+383 48 75 66 46</p>
                  <p class="text-slate-600">+383 44 82 43 14</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="bg-primary-100 rounded-2xl p-3">
                  <span class="text-primary-600 text-xl">🕒</span>
                </div>
                <div>
                  <h4 class="font-semibold text-slate-900">Orari i Punës</h4>
                  <p class="text-slate-600">E Hënë - E Premte: 08:00 - 18:00</p>
                  <p class="text-slate-600">E Shtunë: 09:00 - 15:00</p>
                  <p class="text-slate-600">E Dielë: Mbyllur</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-900/5 p-8">
            <h3 class="text-2xl font-extrabold tracking-tight text-slate-900 mb-6">
              Na Gjeni
            </h3>
            <p class="text-sm text-slate-500 mb-3">
              Harta interaktive – Ferizaj, Rruga Lidhja E Prizerent
            </p>
            <div ref="mapContainer" class="rounded-2xl overflow-hidden border border-slate-200 shadow-inner" style="height: 360px; min-height: 280px;"></div>
            <a
              href="https://www.openstreetmap.org/?mlat=42.3702&amp;mlon=21.1553#map=16/42.3702/21.1553"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1 mt-3 text-sm text-primary-600 hover:text-primary-700 font-semibold"
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
        .bindPopup('<strong>AronTrade</strong><br>Ferizaj, Rruga Lidhja E Prizerent')
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
