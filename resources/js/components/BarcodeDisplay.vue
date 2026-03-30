<template>
  <div class="barcode-display inline-flex max-w-full min-w-0 flex-col items-center justify-center">
    <svg v-if="validValue" ref="barcodeSvg" class="barcode-svg max-w-full h-auto" :class="{ 'compact': compact }"></svg>
    <div
      v-if="validValue"
      class="mt-0.5 flex w-full max-w-full min-w-0 justify-center overflow-x-auto overscroll-x-contain [-webkit-overflow-scrolling:touch] sm:mt-1"
    >
      <span
        class="barcode-number whitespace-nowrap px-0.5 text-center font-mono text-slate-700 antialiased"
        :class="compact ? 'text-[9px] tracking-tight sm:text-[10px] sm:tracking-wide' : 'text-[10px] tracking-tight sm:text-xs sm:tracking-widest'"
      >{{ value }}</span>
    </div>
    <span v-else class="text-[10px] text-slate-400 sm:text-xs">{{ value || '—' }}</span>
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
        width: this.compact ? 1 : 2,
        height: this.compact ? 22 : 48,
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
  min-height: 20px;
}
/* Hapësira ekstra vetëm në ekrane më të gjërë; në mobile mbetet një rresht */
@media (min-width: 640px) {
  .barcode-number {
    letter-spacing: 0.12em;
  }
}
</style>
