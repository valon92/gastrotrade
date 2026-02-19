<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside 
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-gray-900 to-gray-800 text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <div class="flex flex-col h-full">
        <!-- Logo & Brand -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-gray-700">
          <div class="flex items-center gap-3">
            <svg class="w-10 h-10 flex-shrink-0 text-primary-600" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect width="40" height="40" rx="8" fill="currentColor"/>
              <text x="20" y="26" text-anchor="middle" fill="white" font-family="system-ui, sans-serif" font-weight="700" font-size="16">GT</text>
            </svg>
            <div>
              <h1 class="text-lg font-bold">GastroTrade</h1>
              <p class="text-xs text-gray-400">Admin Panel</p>
            </div>
          </div>
          <button 
            @click="toggleSidebar"
            class="lg:hidden text-gray-400 hover:text-white"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- User Info -->
        <div class="px-6 py-4 border-b border-gray-700">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center">
              <span class="text-sm font-semibold">{{ userInitials }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium truncate">{{ adminStore.user?.name || 'Admin' }}</p>
              <p class="text-xs text-gray-400 truncate">{{ adminStore.user?.email || '' }}</p>
              <span 
                :class="[
                  'inline-block mt-1 px-2 py-0.5 text-xs rounded-full',
                  adminStore.isAdmin ? 'bg-green-500/20 text-green-300' : 'bg-blue-500/20 text-blue-300'
                ]"
              >
                {{ adminStore.isAdmin ? 'Admin' : 'Order Manager' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
          <!-- Dashboard -->
          <router-link
            to="/admin/dashboard"
            class="nav-item"
            active-class="nav-item-active"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
          </router-link>

          <!-- Sales - Available for all -->
          <router-link
            v-if="adminStore.canViewSales()"
            to="/admin/sales"
            class="nav-item"
            active-class="nav-item-active"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Shitjet</span>
          </router-link>

          <!-- Clients - Admin only -->
          <router-link
            v-if="adminStore.canManageClients()"
            to="/admin/clients"
            class="nav-item"
            active-class="nav-item-active"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Klientët</span>
          </router-link>

          <!-- Products - Admin only -->
          <router-link
            v-if="adminStore.canManageProducts()"
            to="/admin/products"
            class="nav-item"
            active-class="nav-item-active"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span>Produktet</span>
          </router-link>

          <!-- Stock - Admin only -->
          <router-link
            v-if="adminStore.canManageStock()"
            to="/admin/stock"
            class="nav-item"
            active-class="nav-item-active"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Stoku</span>
          </router-link>

          <!-- Supplier Invoices - Admin only -->
          <router-link
            v-if="adminStore.canManageSuppliers()"
            to="/admin/supplier-invoices"
            class="nav-item"
            active-class="nav-item-active"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Faturat e Furnitorëve</span>
          </router-link>

          <!-- Trash - Admin only -->
          <router-link
            v-if="adminStore.canViewTrash()"
            to="/admin/trash"
            class="nav-item"
            active-class="nav-item-active"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span>Historia e Fshirjeve</span>
          </router-link>

          <!-- Users - Admin only -->
          <router-link
            v-if="adminStore.canManage()"
            to="/admin/users"
            class="nav-item"
            active-class="nav-item-active"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Adminat</span>
          </router-link>
        </nav>

        <!-- Logout -->
        <div class="px-4 py-4 border-t border-gray-700">
          <button
            @click="logout"
            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-300 hover:bg-red-500/10 rounded-lg transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Dil</span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Overlay for mobile -->
    <div
      v-if="sidebarOpen"
      @click="toggleSidebar"
      class="fixed inset-0 bg-black/50 z-40 lg:hidden"
    ></div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col lg:ml-64">
      <!-- Top Header -->
      <header class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
        <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
          <button
            @click="toggleSidebar"
            class="lg:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          
          <div class="flex-1 flex items-center justify-between">
            <div>
              <h2 class="text-xl font-semibold text-gray-900">{{ pageTitle }}</h2>
            </div>
            
            <div class="flex items-center gap-4">
              <!-- Notifications (placeholder) -->
              <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 00-2-2h-1a2 2 0 00-2 2v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto bg-gray-50" ref="mainContent">
        <slot />
      </main>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { adminStore } from '../../stores/adminStore'
import axios from 'axios'

export default {
  name: 'AdminLayout',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const sidebarOpen = ref(false)

    const toggleSidebar = () => {
      sidebarOpen.value = !sidebarOpen.value
    }

    const userInitials = computed(() => {
      if (!adminStore.user?.name) return 'A'
      const names = adminStore.user.name.split(' ')
      if (names.length >= 2) {
        return (names[0][0] + names[1][0]).toUpperCase()
      }
      return names[0][0].toUpperCase()
    })

    const pageTitle = computed(() => {
      const titles = {
        '/admin/dashboard': 'Dashboard',
        '/admin/sales': 'Shitjet',
        '/admin/clients': 'Klientët',
        '/admin/products': 'Produktet',
        '/admin/stock': 'Stoku',
        '/admin/supplier-invoices': 'Faturat e Furnitorëve',
        '/admin/trash': 'Historia e Fshirjeve',
        '/admin/users': 'Adminat'
      }
      return titles[route.path] || 'Admin Panel'
    })

    const logout = () => {
      adminStore.clearUser()
      localStorage.removeItem('admin_token')
      delete axios.defaults.headers.common['Authorization']
      router.push('/admin/login')
    }

    // Watch for route changes and scroll to top
    watch(() => route.path, () => {
      // Scroll to top when route changes
      setTimeout(() => {
        window.scrollTo({ top: 0, behavior: 'smooth' })
        // Also scroll main content area if it exists
        const mainContent = document.querySelector('main.flex-1')
        if (mainContent) {
          mainContent.scrollTo({ top: 0, behavior: 'smooth' })
        }
      }, 100)
    })
    
    onMounted(() => {
      // Scroll to top when layout mounts
      window.scrollTo({ top: 0, behavior: 'smooth' })
      
      adminStore.loadUser()
      
      // Load user from API if not in store
      if (!adminStore.user) {
        const token = localStorage.getItem('admin_token')
        if (token) {
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
          axios.get('/api/admin/check')
            .then(res => {
              if (res.data.success) {
                adminStore.setUser(res.data.data.user)
              }
            })
            .catch(() => {
              logout()
            })
        }
      }
    })

    return {
      adminStore,
      sidebarOpen,
      toggleSidebar,
      userInitials,
      pageTitle,
      logout
    }
  }
}
</script>

<style scoped>
.nav-item {
  @apply flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-300 rounded-lg transition-all duration-200 hover:bg-gray-700/50 hover:text-white;
}

.nav-item-active {
  @apply bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg;
}
</style>
