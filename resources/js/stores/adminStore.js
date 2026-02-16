import { reactive } from 'vue'

export const adminStore = reactive({
  user: null,
  role: null,
  isAdmin: false,
  isOrderManager: false,

  setUser(userData) {
    this.user = userData
    this.role = userData?.role || 'order_manager'
    this.isAdmin = this.role === 'admin'
    this.isOrderManager = this.role === 'order_manager'
    
    // Store in localStorage for persistence
    if (userData) {
      localStorage.setItem('admin_user', JSON.stringify(userData))
    }
  },

  loadUser() {
    const stored = localStorage.getItem('admin_user')
    if (stored) {
      try {
        const userData = JSON.parse(stored)
        this.setUser(userData)
      } catch (e) {
        console.error('Error loading admin user:', e)
      }
    }
  },

  clearUser() {
    this.user = null
    this.role = null
    this.isAdmin = false
    this.isOrderManager = false
    localStorage.removeItem('admin_user')
  },

  canManage() {
    return this.isAdmin
  },

  canViewSales() {
    return this.isAdmin || this.isOrderManager
  },

  canManageClients() {
    return this.isAdmin
  },

  canManageProducts() {
    return this.isAdmin
  },

  canManageStock() {
    return this.isAdmin
  },

  canManageSuppliers() {
    return this.isAdmin
  },

  canViewTrash() {
    return this.isAdmin
  }
})

