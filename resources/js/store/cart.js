import { reactive } from 'vue'
import { OFFICIAL_ORDER_EMAIL } from '../config/site'

// Cart store using Vue's reactive API
const cartStore = reactive({
  items: [],
  client: null,
  clientPrices: {},
  clientPieceSales: {}, // Store allow_piece_sales for each product
  
  _cartKey: 'arontrade_cart',
  _clientKey: 'arontrade_client',

  // Load cart from localStorage on initialization (AronTrade; fallback për gastrotrade)
  init() {
    let savedCart = typeof localStorage !== 'undefined' && (localStorage.getItem(this._cartKey) || localStorage.getItem('gastrotrade_cart'))
    if (savedCart) {
      try {
        this.items = JSON.parse(savedCart)
      } catch (e) {
        this.items = []
      }
      if (typeof localStorage !== 'undefined') localStorage.setItem(this._cartKey, JSON.stringify(this.items))
    }
    let savedClient = typeof localStorage !== 'undefined' && (localStorage.getItem(this._clientKey) || localStorage.getItem('gastrotrade_client'))
    if (savedClient) {
      try {
        const client = JSON.parse(savedClient)
        this.client = client
        // Rindërto çmimet e klientit që klienti të qëndrojë i kyqur edhe pas rifreskimit
        if (client && client.prices && Array.isArray(client.prices)) {
          this.clientPrices = {}
          this.clientPieceSales = {}
          client.prices.forEach(price => {
            this.clientPrices[price.product_id] = price.price
            this.clientPieceSales[price.product_id] = price.allow_piece_sales || false
          })
          this.items.forEach(item => {
            if (this.clientPrices[item.id]) {
              item.price = this.clientPrices[item.id]
            } else {
              item.price = null
            }
          })
          this.save()
        }
      } catch (e) {
        this.client = null
      }
      if (typeof localStorage !== 'undefined') {
        if (this.client) localStorage.setItem(this._clientKey, JSON.stringify(this.client))
        try { localStorage.removeItem('gastrotrade_client') } catch (_) {}
      }
    }
  },

  // Set client and load prices
  async setClient(client) {
    this.client = client
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(this._clientKey, JSON.stringify(client))
    }
    
    // Load client prices and piece sales settings
    if (client && client.prices && Array.isArray(client.prices)) {
      this.clientPrices = {}
      this.clientPieceSales = {}
      client.prices.forEach(price => {
        this.clientPrices[price.product_id] = price.price
        this.clientPieceSales[price.product_id] = price.allow_piece_sales || false
      })
      
      // Update cart items with client prices (only if client has price for that product)
      this.items.forEach(item => {
        if (this.clientPrices[item.id]) {
          item.price = this.clientPrices[item.id]
        } else {
          // If client doesn't have a price for this product, set to null
          item.price = null
        }
      })
      this.save()
    } else {
      // If no client or no prices, clear all prices
      this.clientPrices = {}
      this.clientPieceSales = {}
      this.items.forEach(item => {
        item.price = null
      })
      this.save()
    }
  },
  
  // Clear client (të dhënat lokale)
  clearClient() {
    this.client = null
    this.clientPrices = {}
    this.clientPieceSales = {}
    if (typeof localStorage !== 'undefined') {
      localStorage.removeItem(this._clientKey)
      try { localStorage.removeItem('gastrotrade_client') } catch (_) {}
    }
    this.items.forEach(item => {
      item.price = null
    })
    this.save()
  },

  /** Dil nga llogaria – i njëjti sistem për të gjithë klientët: pastron klientin dhe tokenin. */
  logoutSession() {
    this.clearClient()
    localStorage.removeItem('client_token')
    if (typeof window !== 'undefined' && window.axios?.defaults?.headers?.common && !localStorage.getItem('admin_token')) {
      delete window.axios.defaults.headers.common['Authorization']
    }
  },
  
  // Add product to cart (quantity = numër kompletesh; actualPieces = optional, kur blerja është me copa)
  addItem(product, quantity = 1, actualPieces = null) {
    const existingItem = this.items.find(item => item.id === product.id)
    
    let price = null
    if (this.client && this.clientPrices[product.id]) {
      price = this.clientPrices[product.id]
    }
    
    if (existingItem) {
      if (actualPieces != null) {
        const prevPieces = existingItem.actual_pieces ?? (existingItem.quantity * (existingItem.pieces_per_package || 1))
        existingItem.actual_pieces = prevPieces + actualPieces
        existingItem.quantity = Math.ceil(existingItem.actual_pieces / (existingItem.pieces_per_package || 1))
      } else {
        existingItem.quantity += quantity
      }
      existingItem.price = price
    } else {
      const newItem = {
        id: product.id,
        name: product.name,
        slug: product.slug,
        image_path: product.image_path,
        image_url: product.image_url,
        barcode: product.barcode || null,
        price: price,
        quantity: quantity,
        sold_by_package: product.sold_by_package || false,
        pieces_per_package: product.pieces_per_package || null,
        discount_amount: 0,
        discount_type: '',
        discount_value: 0
      }
      if (actualPieces != null) {
        newItem.actual_pieces = actualPieces
        newItem.quantity = Math.ceil(actualPieces / (product.pieces_per_package || 1))
      }
      this.items.push(newItem)
    }
    
    this.save()
  },
  
  // Remove item from cart
  removeItem(productId) {
    this.items = this.items.filter(item => item.id !== productId)
    this.save()
  },
  
  // Update item quantity
  updateQuantity(productId, quantity) {
    const item = this.items.find(item => item.id === productId)
    if (item) {
      if (quantity <= 0) {
        this.removeItem(productId)
      } else {
        item.quantity = quantity
        this.save()
      }
    }
  },
  
  // Clear cart
  clear() {
    this.items = []
    this.save()
  },
  
  // Get total items count
  get totalItems() {
    return this.items.reduce((total, item) => total + item.quantity, 0)
  },
  
  // Get subtotal (before discounts)
  get subtotal() {
    return this.items.reduce((total, item) => {
      if (item.price) {
        // If client can buy by pieces and we have actual pieces stored
        // Check if this specific product allows piece sales for this client
        const canBuyByPieces = this.client && this.clientPieceSales[item.id] && item.sold_by_package && item.pieces_per_package
        if (canBuyByPieces && item.actual_pieces) {
          return total + (item.price * item.actual_pieces)
        }
        // For products sold by package: price per piece × quantity (packages) × pieces per package
        if (item.sold_by_package && item.pieces_per_package) {
          return total + (item.price * item.quantity * item.pieces_per_package)
        }
        // For regular products: price per piece × quantity
        return total + (item.price * item.quantity)
      }
      return total
    }, 0)
  },
  
  // Get total item discounts
  get totalItemDiscounts() {
    return this.items.reduce((total, item) => {
      return total + (item.discount_amount || 0)
    }, 0)
  },
  
  // Get general discount amount
  generalDiscountAmount: 0,
  generalDiscountType: '',
  generalDiscountValue: 0,
  
  // Calculate general discount
  calculateGeneralDiscount() {
    if (!this.generalDiscountType || !this.generalDiscountValue || this.generalDiscountValue <= 0) {
      this.generalDiscountAmount = 0
      return 0
    }
    
    const subtotalAfterItemDiscounts = this.subtotal - this.totalItemDiscounts
    
    if (this.generalDiscountType === 'percentage') {
      this.generalDiscountAmount = (subtotalAfterItemDiscounts * this.generalDiscountValue) / 100
    } else if (this.generalDiscountType === 'fixed') {
      this.generalDiscountAmount = Math.min(this.generalDiscountValue, subtotalAfterItemDiscounts)
    } else {
      this.generalDiscountAmount = 0
    }
    
    return this.generalDiscountAmount
  },
  
  // Get total price (after all discounts)
  get totalPrice() {
    const subtotal = this.subtotal
    const itemDiscounts = this.totalItemDiscounts
    const generalDiscount = this.calculateGeneralDiscount()
    
    return Math.max(0, subtotal - itemDiscounts - generalDiscount)
  },
  
  // Save cart to localStorage
  save() {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(this._cartKey, JSON.stringify(this.items))
    }
  },
  
    // Generate order message for Viber
  generateOrderMessage(customerData = null) {
    try {
      // Validate items exist
      if (!this.items || !Array.isArray(this.items) || this.items.length === 0) {
        throw new Error('Shporta është e zbrazët')
      }

      let message = '📦 POROSI E RE\n\n'
      
      // Add customer information if provided
      if (customerData) {
        message += '👤 TË DHËNAT E POROSITËSIT:\n'
        message += `Emri: ${customerData.name || 'N/A'}\n`
        message += `Biznesi: ${customerData.storeName || 'N/A'}\n`
        if (customerData.fiscalNumber) {
          message += `Nr. Fiskal: ${customerData.fiscalNumber}\n`
        }
        message += `Qyteti: ${customerData.city || 'N/A'}\n`
        if (customerData.phone) {
          message += `📞 Telefon/Viber: ${customerData.phone}\n`
        }
        message += '\n─────────────────────────\n\n'
      }
      
      message += '🛒 PRODUKTET E ZGJEDHURA:\n\n'
      
      this.items.forEach((item, index) => {
        // Validate item has required fields
        if (!item || !item.name) {
          console.warn('Invalid item at index', index, item)
          return
        }

        const itemName = item.name || 'Produkt i panjohur'
        const quantity = item.quantity || 1
        const price = item.price || null
        const soldByPackage = item.sold_by_package || false
        const piecesPerPackage = item.pieces_per_package || null
        const actualPieces = item.actual_pieces || null
        // Check if this specific product allows piece sales for this client
        const canBuyByPieces = this.client && this.clientPieceSales[item.id] && soldByPackage && piecesPerPackage

        message += `${index + 1}. ${itemName}\n`
        
        if (price !== null && price !== undefined && !isNaN(price) && price > 0) {
          // Product has price
          try {
            if (canBuyByPieces && actualPieces) {
              // Client bought by pieces
              const itemTotal = price * actualPieces
              message += `   €${parseFloat(price).toFixed(2)} × ${actualPieces}copa = €${itemTotal.toFixed(2)}\n`
            } else if (soldByPackage && piecesPerPackage) {
              // Client bought by packages
              const totalPieces = quantity * piecesPerPackage
              const itemTotal = price * quantity * piecesPerPackage
              message += `   €${parseFloat(price).toFixed(2)} × ${quantity}(komplete) × ${piecesPerPackage}cp = €${itemTotal.toFixed(2)}\n`
            } else {
              // Regular product
              const itemTotal = price * quantity
              message += `   €${parseFloat(price).toFixed(2)} × ${quantity} = €${itemTotal.toFixed(2)}\n`
            }
          } catch (priceError) {
            console.error('Error formatting price for item:', itemName, priceError)
            message += `   Sasia: ${canBuyByPieces && actualPieces ? actualPieces + ' copa' : (soldByPackage && piecesPerPackage ? quantity + ' komplete (' + (quantity * piecesPerPackage) + ' copa)' : quantity + ' copë')}\n`
            message += `   Çmimi: Sipas kërkesës\n`
          }
        } else {
          // Product has no price
          if (canBuyByPieces && actualPieces) {
            message += `   Sasia: ${actualPieces} copa\n`
            message += `   Çmimi: Sipas kërkesës\n`
          } else if (soldByPackage && piecesPerPackage) {
            message += `   Sasia: ${quantity} komplete (${quantity * piecesPerPackage} copa)\n`
            message += `   Çmimi: Sipas kërkesës\n`
          } else {
            message += `   Sasia: ${quantity} copë\n`
            message += `   Çmimi: Sipas kërkesës\n`
          }
        }
        message += '\n'
      })
      
      message += '─────────────────────────\n\n'
      message += `📊 Total: ${this.totalItems || 0} produkt(e)\n`
      
      const totalPrice = this.totalPrice || 0
      if (totalPrice > 0 && !isNaN(totalPrice)) {
        message += `💰 Vlera totale: ${parseFloat(totalPrice).toFixed(2)} €\n`
      }
      
      message += '\n📞 KONTAKT:\n'
      message += `Email: ${OFFICIAL_ORDER_EMAIL}\n`
      message += 'Telefon: 048 75 66 46 / 044 82 43 14\n'
      message += 'Viber: +383 48 75 66 46 / +383 44 82 43 14\n'
      message += '📍 Adresa: Ferizaj, Kosovë, Rruga Lidhja E Prizerent\n'
      
      return encodeURIComponent(message)
    } catch (error) {
      console.error('Error generating order message:', error)
      throw error
    }
  }
})

// Initialize cart on load
cartStore.init()

export default cartStore
