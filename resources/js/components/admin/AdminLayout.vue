<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside 
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white transform transition-transform duration-300 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        sidebarPinnedOpen ? 'lg:translate-x-0' : 'lg:-translate-x-full'
      ]"
    >
      <div class="flex flex-col h-full">
        <!-- Logo & Brand -->
        <div class="flex items-center justify-between h-16 px-5 border-b border-white/10">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-primary-500 flex items-center justify-center shadow-lg">
              <span class="text-white font-bold text-sm">GT</span>
            </div>
            <div class="min-w-0">
              <h1 class="text-base font-bold text-white truncate">GastroTrade</h1>
              <p class="text-xs text-white/60">Paneli i administrimit</p>
            </div>
          </div>
          <div class="flex items-center gap-1">
            <button 
              @click="hideSidebar"
              class="hidden lg:flex p-2 rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition-colors"
              title="Fsheh menunë"
              aria-label="Fsheh menunë"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
              </svg>
            </button>
            <button 
              @click="toggleSidebar"
              class="lg:hidden p-2 rounded-lg text-white/60 hover:text-white"
              aria-label="Mbyll menunë"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- User Info -->
        <div class="px-5 py-4 border-b border-white/10">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 flex-shrink-0 rounded-full bg-white/10 flex items-center justify-center text-white font-semibold text-sm ring-2 ring-white/20">
              {{ userInitials }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-white truncate">{{ displayName }}</p>
              <p class="text-xs text-white/60 truncate">{{ adminStore.user?.email || '' }}</p>
              <span 
                :class="[
                  'inline-block mt-1.5 px-2 py-0.5 text-xs font-medium rounded-md',
                  adminStore.isAdmin ? 'bg-emerald-500/25 text-emerald-200' : 'bg-sky-500/25 text-sky-200'
                ]"
              >
                {{ roleLabel }}
              </span>
            </div>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5" aria-label="Menuja kryesore">
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
        <div class="px-4 py-4 border-t border-white/10">
          <button
            @click="logout"
            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-white/80 hover:bg-red-500/20 hover:text-red-200 rounded-xl transition-all duration-200"
          >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Dilni</span>
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
    <div 
      class="flex-1 flex flex-col transition-[margin] duration-300"
      :class="sidebarPinnedOpen ? 'lg:ml-64' : 'lg:ml-0'"
    >
      <!-- Top Header -->
      <header class="sticky top-0 z-30 bg-white border-b border-gray-200/80 shadow-sm">
        <div class="flex items-center justify-between h-14 lg:h-16 px-4 sm:px-6 lg:px-8">
          <div class="flex items-center gap-2">
            <button
              v-if="!sidebarPinnedOpen"
              @click="showSidebar"
              class="hidden lg:flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
              aria-label="Shfaq menunë"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <span class="text-sm font-medium">Menuja</span>
            </button>
            <button
              @click="toggleSidebar"
              class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors"
              aria-label="Hap menunë"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
          <div class="flex-1 flex items-center justify-between gap-4">
            <h2 class="text-lg lg:text-xl font-semibold text-gray-900 truncate">{{ pageTitle }}</h2>
            <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500">
              <span class="truncate max-w-[180px]">{{ adminStore.user?.email }}</span>
              <span 
                :class="['px-2 py-0.5 rounded-md text-xs font-medium', adminStore.isAdmin ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700']"
              >
                {{ roleLabel }}
              </span>
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

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { adminStore } from '../../stores/adminStore'
import axios from 'axios'

const SIDEBAR_STORAGE_KEY = 'admin_sidebar_visible'
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(false)
const sidebarPinnedOpen = ref(true)

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const hideSidebar = () => {
  sidebarPinnedOpen.value = false
  try { localStorage.setItem(SIDEBAR_STORAGE_KEY, '0') } catch (_) {}
}

const showSidebar = () => {
  sidebarPinnedOpen.value = true
  try { localStorage.setItem(SIDEBAR_STORAGE_KEY, '1') } catch (_) {}
}

const loadSidebarPreference = () => {
  try {
    const v = localStorage.getItem(SIDEBAR_STORAGE_KEY)
    if (v === '0') sidebarPinnedOpen.value = false
    else if (v === '1') sidebarPinnedOpen.value = true
  } catch (_) {}
}

const userInitials = computed(() => {
  const name = adminStore.user?.name || ''
  const email = adminStore.user?.email || ''
  if (name && name.trim()) {
    const parts = name.trim().split(/\s+/)
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase()
    if (parts[0].length >= 2) return parts[0].slice(0, 2).toUpperCase()
    return parts[0][0].toUpperCase()
  }
  if (email) {
    const local = email.split('@')[0] || ''
    if (local.length >= 2) return local.slice(0, 2).toUpperCase()
    return local[0].toUpperCase()
  }
  return 'A'
})

const displayName = computed(() => {
  const name = adminStore.user?.name || ''
  const email = adminStore.user?.email || ''
  if (name && name.trim()) return name.trim()
  return email || 'Administrator'
})

const roleLabel = computed(() => {
  return adminStore.isAdmin ? 'Administrator' : 'Menaxher porosish'
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

watch(() => route.path, () => {
  setTimeout(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
    const mainContent = document.querySelector('main.flex-1')
    if (mainContent) mainContent.scrollTo({ top: 0, behavior: 'smooth' })
  }, 100)
})

onMounted(() => {
  loadSidebarPreference()
  window.scrollTo({ top: 0, behavior: 'smooth' })
  adminStore.loadUser()
  if (!adminStore.user) {
    const token = localStorage.getItem('admin_token')
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      axios.get('/api/admin/check')
        .then(res => {
          if (res.data.success) adminStore.setUser(res.data.data.user)
        })
        .catch(() => logout())
    }
  }
})
</script>

<style scoped>
.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.75);
  border-radius: 0.5rem;
  transition: background-color 0.2s, color 0.2s;
}
.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.08);
  color: white;
}
.nav-item-active {
  background-color: var(--color-primary-600, #0d9488);
  color: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}
</style>
