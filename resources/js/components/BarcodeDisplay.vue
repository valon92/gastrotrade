<template>
  <div class="barcode-display inline-flex flex-col items-center justify-center">
    <svg v-if="validValue" ref="barcodeSvg" class="barcode-svg max-w-full h-auto" :class="{ 'compact': compact }"></svg>
    <span v-if="validValue" class="barcode-number mt-1 font-mono text-xs tracking-widest text-gray-700">{{ value }}</span>
    <span v-else class="text-gray-400 text-xs">{{ value || '—' }}</span>
  </div>
</template>

<script>
import JsBarcode from 'jsbarcode'

export default {
  name: 'BarcodeDisplay',
  props: {
    value: {
      type: [String, Number],
      default: ''
    },
    compact: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    validValue() {
      const v = String(this.value || '').replace(/\D/g, '')
      return v.length >= 7 ? v : ''
    },
    format() {
      const len = this.validValue.length
      if (len === 12 || len === 13) return 'EAN13'
      return 'CODE128'
    }
  },
  watch: {
    value: { handler() { this.render() }, immediate: false },
    validValue: { handler() { this.render() }, immediate: false }
  },
  mounted() {
    this.render()
  },
  methods: {
    render() {
      if (!this.validValue || !this.$refs.barcodeSvg) return
      const el = this.$refs.barcodeSvg
      el.innerHTML = ''
      const options = {
        width: this.compact ? 1.2 : 2,
        height: this.compact ? 28 : 48,
        displayValue: false,
        margin: 4,
        background: '#ffffff',
        lineColor: '#0f172a'
      }
      try {
        JsBarcode(el, this.validValue, { ...options, format: this.format })
      } catch (e) {
        // EAN13 kërkon checksum të vlefshëm; nëse dështon (p.sh. barkod negativ ose i pavlefshëm), përdor CODE128
        try {
          JsBarcode(el, this.validValue, { ...options, format: 'CODE128' })
        } catch (e2) {
          console.warn('Barcode render:', e2)
        }
      }
    }
  }
}
</script>

<style scoped>
.barcode-svg {
  min-height: 32px;
}
.barcode-svg.compact {
  min-height: 24px;
}
.barcode-number {
  letter-spacing: 0.15em;
}
</style>
