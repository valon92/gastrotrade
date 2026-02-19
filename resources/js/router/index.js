import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'
import Home from '../views/Home.vue'
import Products from '../views/Products.vue'
import ProductDetail from '../views/ProductDetail.vue'
import About from '../views/About.vue'
import Contact from '../views/Contact.vue'
import Cart from '../views/Cart.vue'
import OrderConfirmation from '../views/OrderConfirmation.vue'
import AdminClients from '../views/admin/AdminClients.vue'
import AdminClientPrices from '../views/admin/AdminClientPrices.vue'
import AdminLogin from '../views/admin/AdminLogin.vue'
import AdminProducts from '../views/admin/AdminProducts.vue'
import AdminStock from '../views/admin/AdminStock.vue'
import AdminSupplierInvoices from '../views/admin/AdminSupplierInvoices.vue'
import AdminTrash from '../views/admin/AdminTrash.vue'
import AdminSales from '../views/admin/AdminSales.vue'
import AdminUsers from '../views/admin/AdminUsers.vue'
import AdminDashboard from '../views/admin/AdminDashboard.vue'
import { adminStore } from '../stores/adminStore'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/produktet',
    name: 'Products',
    component: Products
  },
  {
    path: '/produktet/:slug',
    name: 'ProductDetail',
    component: ProductDetail,
    props: true
  },
  {
    path: '/rreth-nesh',
    name: 'About',
    component: About
  },
  {
    path: '/kontakt',
    name: 'Contact',
    component: Contact
  },
  {
    path: '/shporta',
    name: 'Cart',
    component: Cart
  },
  {
    path: '/konfirmo-porosine',
    name: 'OrderConfirmation',
    component: OrderConfirmation
  },
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: AdminLogin
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/clients',
    name: 'AdminClients',
    component: AdminClients,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/clients/:clientId/prices',
    name: 'AdminClientPrices',
    component: AdminClientPrices,
    props: true,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/products',
    name: 'AdminProducts',
    component: AdminProducts,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/stock',
    name: 'AdminStock',
    component: AdminStock,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/supplier-invoices',
    name: 'AdminSupplierInvoices',
    component: AdminSupplierInvoices,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/trash',
    name: 'AdminTrash',
    component: AdminTrash,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/sales',
    name: 'AdminSales',
    component: AdminSales,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/users',
    name: 'AdminUsers',
    component: AdminUsers,
    meta: { requiresAuth: true, requiresAdmin: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Nëse ka pozicion të ruajtur (p.sh. kur përdor back button), kthehu atje
    if (savedPosition) {
      return savedPosition
    }
    // Përndryshe, shko në top të faqes
    return { top: 0, behavior: 'smooth' }
  }
})

// Global navigation guard for admin auth/roles
router.beforeEach(async (to, from, next) => {
  const requiresAuth = to.meta?.requiresAuth
  const requiresAdmin = to.meta?.requiresAdmin
  const token = localStorage.getItem('admin_token')

  // If navigating to login and already authenticated, redirect to dashboard
  if (to.path === '/admin/login' && token) {
    try {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      const res = await axios.get('/api/admin/check')
      if (res.data.success) {
        adminStore.setUser(res.data.data.user)
        return next('/admin/dashboard')
      }
    } catch (e) {
      // fall through to login
    }
  }

  if (!requiresAuth) {
    return next()
  }

  if (!token) {
    adminStore.clearUser()
    return next('/admin/login')
  }

  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

  // Always reload user data from API to ensure we have latest role
  try {
    const res = await axios.get('/api/admin/check')
    if (res.data.success && res.data.data.user) {
      const userData = res.data.data.user
      adminStore.setUser(userData)
      
      console.log('[Router] User loaded:', {
        email: userData.email,
        role: userData.role,
        isAdmin: userData.role === 'admin',
        adminStoreIsAdmin: adminStore.isAdmin
      })
      
      // If route requires admin role, check immediately after loading user
      if (requiresAdmin) {
        const userRole = userData.role
        const isAdmin = userRole === 'admin'
        
        console.log('[Router] requiresAdmin check for', to.path, {
          hasUser: !!adminStore.user,
          role: userRole,
          isAdmin: isAdmin,
          adminStoreIsAdmin: adminStore.isAdmin,
          userEmail: userData.email
        })
        
        if (!isAdmin) {
          console.warn('[Router] Access denied - user is not admin. Role:', userRole, 'Redirecting to /admin/dashboard')
          // Redirect to dashboard instead of sales for better UX
          return next('/admin/dashboard')
        }
      }
    } else {
      adminStore.clearUser()
      localStorage.removeItem('admin_token')
      delete axios.defaults.headers.common['Authorization']
      return next('/admin/login')
    }
  } catch (error) {
    console.error('[Router] Failed to check admin auth:', error)
    adminStore.clearUser()
    localStorage.removeItem('admin_token')
    delete axios.defaults.headers.common['Authorization']
    return next('/admin/login')
  }

  return next()
})

export default router

