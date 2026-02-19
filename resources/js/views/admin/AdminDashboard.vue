<template>
  <AdminLayout>
    <div class="p-4 sm:p-6 lg:p-8 min-h-screen">
      <!-- Welcome Section -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
          Mirë se vini, {{ adminStore?.user?.name || 'Admin' }}! 👋
        </h1>
        <p class="text-gray-600">
          Këtu është një përmbledhje e shpejtë e aktivitetit të sistemit.
        </p>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
        <p class="text-gray-600">Duke ngarkuar të dhënat...</p>
      </div>
      
      <!-- Content when loaded -->
      <div v-else class="space-y-8">

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Sales -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-blue-800">Total Shitje</h3>
            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-blue-900 mb-1">{{ stats.totalSales || 0 }}</p>
          <p class="text-sm text-blue-700">{{ formatPrice(stats.totalRevenue || 0) }}</p>
        </div>

        <!-- Paid Orders -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-green-800">Të Paguara</h3>
            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-green-900 mb-1">{{ stats.paidCount || 0 }}</p>
          <p class="text-sm text-green-700">{{ formatPrice(stats.paidTotal || 0) }}</p>
        </div>

        <!-- Unpaid Orders -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border border-red-200 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-red-800">Të Papaguara</h3>
            <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-red-900 mb-1">{{ stats.unpaidCount || 0 }}</p>
          <p class="text-sm text-red-700">{{ formatPrice(stats.unpaidTotal || 0) }}</p>
        </div>

        <!-- Total Clients -->
        <div 
          v-if="adminStore.canManageClients()"
          class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200 shadow-sm hover:shadow-md transition-shadow"
        >
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-purple-800">Klientët</h3>
            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-purple-900 mb-1">{{ stats.totalClients || 0 }}</p>
          <p class="text-sm text-purple-700">Klientë aktivë</p>
        </div>

        <!-- Total Products -->
        <div 
          v-if="adminStore.canManageProducts()"
          class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 border border-indigo-200 shadow-sm hover:shadow-md transition-shadow"
        >
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-indigo-800">Produktet</h3>
            <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-indigo-900 mb-1">{{ stats.totalProducts || 0 }}</p>
          <p class="text-sm text-indigo-700">Produkte në sistem</p>
        </div>
      </div>

      <!-- All Admin Pages -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Faqet e Admin Panel</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <!-- Clients -->
          <router-link
            to="/admin/clients"
            class="group relative bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border-2 border-green-200 shadow-lg hover:shadow-xl hover:border-green-400 transition-all transform hover:-translate-y-1"
          >
            <div class="flex flex-col items-center text-center">
              <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-green-600">Klientët</h3>
              <p class="text-sm text-gray-600 mb-3">Menaxho klientët dhe çmimet</p>
              <span class="text-xs text-green-600 font-semibold">Hap →</span>
            </div>
          </router-link>

          <!-- Products -->
          <router-link
            to="/admin/products"
            class="group relative bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border-2 border-purple-200 shadow-lg hover:shadow-xl hover:border-purple-400 transition-all transform hover:-translate-y-1"
          >
            <div class="flex flex-col items-center text-center">
              <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-purple-600">Produktet</h3>
              <p class="text-sm text-gray-600 mb-3">Menaxho produktet dhe kategoritë</p>
              <span class="text-xs text-purple-600 font-semibold">Hap →</span>
            </div>
          </router-link>

          <!-- Stock -->
          <router-link
            to="/admin/stock"
            class="group relative bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl p-6 border-2 border-orange-200 shadow-lg hover:shadow-xl hover:border-orange-400 transition-all transform hover:-translate-y-1"
          >
            <div class="flex flex-col items-center text-center">
              <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-orange-600">Stoku</h3>
              <p class="text-sm text-gray-600 mb-3">Menaxho stokun e produkteve</p>
              <span class="text-xs text-orange-600 font-semibold">Hap →</span>
            </div>
          </router-link>

          <!-- Supplier Invoices -->
          <router-link
            to="/admin/supplier-invoices"
            class="group relative bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-6 border-2 border-indigo-200 shadow-lg hover:shadow-xl hover:border-indigo-400 transition-all transform hover:-translate-y-1"
          >
            <div class="flex flex-col items-center text-center">
              <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-indigo-600">Faturat e Furnitorëve</h3>
              <p class="text-sm text-gray-600 mb-3">Menaxho fatura të furnitorëve</p>
              <span class="text-xs text-indigo-600 font-semibold">Hap →</span>
            </div>
          </router-link>

          <!-- Trash -->
          <router-link
            to="/admin/trash"
            class="group relative bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl p-6 border-2 border-gray-200 shadow-lg hover:shadow-xl hover:border-gray-400 transition-all transform hover:-translate-y-1"
          >
            <div class="flex flex-col items-center text-center">
              <div class="w-16 h-16 bg-gradient-to-br from-gray-500 to-slate-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-gray-700">Historia e Fshirjeve</h3>
              <p class="text-sm text-gray-600 mb-3">Shiko dhe restaurë produkte të fshira</p>
              <span class="text-xs text-gray-600 font-semibold">Hap →</span>
            </div>
          </router-link>

          <!-- Sales -->
          <router-link
            to="/admin/sales"
            class="group relative bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-6 border-2 border-blue-200 shadow-lg hover:shadow-xl hover:border-blue-400 transition-all transform hover:-translate-y-1"
          >
            <div class="flex flex-col items-center text-center">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600">Shitjet</h3>
              <p class="text-sm text-gray-600 mb-3">Shiko dhe menaxho porositë</p>
              <span class="text-xs text-blue-600 font-semibold">Hap →</span>
            </div>
          </router-link>

          <!-- Users - Only for Admin -->
          <router-link
            v-if="adminStore.canManage() || adminStore.isAdmin"
            to="/admin/users"
            class="group relative bg-gradient-to-br from-red-50 to-pink-50 rounded-xl p-6 border-2 border-red-200 shadow-lg hover:shadow-xl hover:border-red-400 transition-all transform hover:-translate-y-1"
          >
            <div class="flex flex-col items-center text-center">
              <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-red-600">Adminat</h3>
              <p class="text-sm text-gray-600 mb-3">Menaxho adminat dhe rolet</p>
              <span class="text-xs text-red-600 font-semibold">Hap →</span>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Recent Orders -->
      <div v-if="adminStore.canViewSales()" class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
          <h2 class="text-xl font-bold text-gray-900">Porositë e Fundit</h2>
          <router-link
            to="/admin/sales"
            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
          >
            Shiko të gjitha →
          </router-link>
        </div>
        <div class="p-6">
          <div v-if="loading" class="text-center py-8 text-gray-500">
            Duke ngarkuar...
          </div>
          <div v-else-if="recentOrders.length === 0" class="text-center py-8 text-gray-500">
            Nuk ka porosi të reja
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="order in recentOrders"
              :key="order.id"
              class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <div>
                <p class="font-semibold text-gray-900">Porosia #{{ order.order_number }}</p>
                <p class="text-sm text-gray-600">{{ order.customer_name || order.client?.name || 'Klient' }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(order.created_at) }}</p>
              </div>
              <div class="text-right">
                <p class="font-bold text-gray-900">{{ formatPrice(order.total_amount) }}</p>
                <span
                  :class="[
                    'inline-block px-2 py-1 text-xs rounded-full',
                    order.is_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ order.is_paid ? 'E Paguar' : 'Jo e Paguar' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { adminStore } from '../../stores/adminStore'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import axios from 'axios'

export default {
  name: 'AdminDashboard',
  components: {
    AdminLayout
  },
  setup() {
    const loading = ref(true)
    const stats = ref({
      totalSales: 0,
      totalRevenue: 0,
      paidCount: 0,
      paidTotal: 0,
      unpaidCount: 0,
      unpaidTotal: 0,
      totalClients: 0,
      totalProducts: 0
    })
    const recentOrders = ref([])

    const loadStats = async () => {
      try {
        // Load payment stats
        const paymentStats = await axios.get('/api/orders/stats/payments')
        if (paymentStats.data.success) {
          stats.value = {
            ...stats.value,
            ...paymentStats.data.data,
            totalSales: paymentStats.data.data.totalCount || 0,
            totalRevenue: paymentStats.data.data.totalAmount || 0
          }
        }

        // Load clients count (if admin)
        if (adminStore.canManageClients()) {
          try {
            const clientsRes = await axios.get('/api/clients', { params: { per_page: 1 } })
            stats.value.totalClients = clientsRes.data.total || 0
          } catch (e) {
            console.error('Error loading clients count:', e)
          }
        }

        // Load products count (if admin)
        if (adminStore.canManageProducts()) {
          try {
            const productsRes = await axios.get('/api/admin/products', { params: { per_page: 1 } })
            stats.value.totalProducts = productsRes.data.total || 0
          } catch (e) {
            console.error('Error loading products count:', e)
          }
        }

        // Load recent orders
        const ordersRes = await axios.get('/api/sales', { params: { per_page: 5 } })
        if (ordersRes.data.data) {
          recentOrders.value = ordersRes.data.data
        }
      } catch (error) {
        console.error('Error loading dashboard stats:', error)
      } finally {
        loading.value = false
      }
    }

    const formatPrice = (price) => {
      if (price === null || price === undefined) return '€0.00'
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    }

    const formatDate = (date) => {
      if (!date) return '-'
      return new Date(date).toLocaleString('sq-AL', {
        dateStyle: 'medium',
        timeStyle: 'short'
      })
    }

    onMounted(async () => {
      // Scroll to top when component mounts
      window.scrollTo({ top: 0, behavior: 'smooth' })
      
      // Load user data first
      adminStore.loadUser()
      
      // If user not loaded, try to get from API
      if (!adminStore.user) {
        try {
          const token = localStorage.getItem('admin_token')
          if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
            const res = await axios.get('/api/admin/check')
            if (res.data.success && res.data.data.user) {
              adminStore.setUser(res.data.data.user)
            }
          }
        } catch (error) {
          console.error('Error loading user:', error)
        }
      }
      
      loadStats()
    })

    return {
      adminStore,
      loading,
      stats,
      recentOrders,
      formatPrice,
      formatDate
    }
  }
}
</script>
