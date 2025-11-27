import { createRouter, createWebHistory } from 'vue-router'
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
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router

