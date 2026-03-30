<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 z-[200] flex items-end justify-center sm:items-center p-0 sm:p-4"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
      >
        <div
          class="absolute inset-0 bg-slate-900/55 backdrop-blur-[3px]"
          aria-hidden="true"
          @click="close"
        />
        <div
          class="relative z-10 flex max-h-[min(96dvh,100%)] min-h-[52dvh] w-full max-w-lg flex-col overflow-y-auto rounded-t-3xl border border-slate-200/90 bg-white shadow-2xl ring-1 ring-primary-900/5 sm:max-h-[90vh] sm:min-h-0 sm:rounded-3xl"
          :class="'pb-[max(1rem,env(safe-area-inset-bottom))] pt-6 px-5 sm:px-8 sm:py-8'"
          @click.stop
        >
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
              <h2 :id="titleId" class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">
                Barkod
              </h2>
              <p v-if="subtitle" class="mt-1 line-clamp-2 text-sm text-slate-500">
                {{ subtitle }}
              </p>
            </div>
            <button
              type="button"
              class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
              aria-label="Mbyll"
              @click="close"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="validDigits" class="mt-6 space-y-5">
            <div
              class="rounded-2xl border border-slate-100 bg-gradient-to-b from-slate-50 to-white px-4 py-5 shadow-inner sm:px-6 sm:py-7"
            >
              <svg
                ref="modalSvg"
                class="mx-auto block h-auto w-full max-w-full text-[0]"
                style="max-height: 120px"
                aria-hidden="true"
              />
            </div>
            <div
              class="rounded-2xl border border-primary-200/80 bg-primary-50/40 px-4 py-4 text-center ring-1 ring-primary-100 sm:px-5"
            >
              <p
                class="select-all font-mono text-[1.35rem] font-semibold tabular-nums tracking-[0.08em] text-slate-900 sm:text-2xl sm:tracking-[0.12em]"
                style="font-variant-numeric: tabular-nums lining-nums"
              >
                {{ formattedForReading }}
              </p>
              <p class="mt-2 break-all font-mono text-xs font-medium text-slate-500 sm:text-sm">
                {{ validDigits }}
              </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:justify-center">
              <button
                type="button"
                class="inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 px-4 py-3 text-sm font-semibold text-white shadow-md ring-1 ring-primary-700/30 transition hover:from-primary-700 hover:to-primary-800 active:scale-[0.99] sm:w-auto sm:min-w-[200px]"
                @click="copyDigits"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                {{ copied ? 'U kopjua' : 'Kopjo numrin' }}
              </button>
            </div>
            <p class="text-center text-xs text-slate-400">
              Prek jashtë panelit ose «Mbyll» për t’u kthyer
            </p>
          </div>
          <p v-else class="mt-6 text-center text-slate-500">
            Nuk ka barkod të vlefshëm për këtë produkt.
          </p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import JsBarcode from 'jsbarcode'

let overlayUid = 0

export default {
  name: 'BarcodeExpandOverlay',
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    value: {
      type: [String, Number],
      default: ''
    },
    subtitle: {
      type: String,
      default: ''
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      titleId: `barcode-expand-title-${++overlayUid}`,
      copied: false,
      copyTimer: null
    }
  },
  computed: {
    validDigits() {
      const v = String(this.value || '').replace(/\D/g, '')
      return v.length >= 7 ? v : ''
    },
    barcodeFormat() {
      const len = this.validDigits.length
      if (len === 12 || len === 13) return 'EAN13'
      return 'CODE128'
    },
    formattedForReading() {
      const s = this.validDigits
      if (!s) return ''
      if (s.length === 13) {
        return `${s.slice(0, 3)} ${s.slice(3, 7)} ${s.slice(7, 12)} ${s.slice(12)}`
      }
      if (s.length === 12) {
        return `${s.slice(0, 3)} ${s.slice(3, 7)} ${s.slice(7, 11)} ${s.slice(11)}`
      }
      return s.replace(/(.{4})/g, '$1\u2003').trim()
    }
  },
  watch: {
    modelValue(open) {
      if (open) {
        document.body.style.overflow = 'hidden'
        this.copied = false
        this.$nextTick(() => this.renderBarcode())
      } else {
        document.body.style.overflow = ''
        if (this.copyTimer) {
          clearTimeout(this.copyTimer)
          this.copyTimer = null
        }
      }
    },
    validDigits() {
      if (this.modelValue) this.$nextTick(() => this.renderBarcode())
    }
  },
  beforeUnmount() {
    document.body.style.overflow = ''
    if (this.copyTimer) clearTimeout(this.copyTimer)
  },
  methods: {
    close() {
      this.$emit('update:modelValue', false)
    },
    renderBarcode() {
      const el = this.$refs.modalSvg
      if (!el || !this.validDigits) return
      el.innerHTML = ''
      const options = {
        width: 2,
        height: 64,
        displayValue: false,
        margin: 12,
        background: '#ffffff',
        lineColor: '#0f172a'
      }
      try {
        JsBarcode(el, this.validDigits, { ...options, format: this.barcodeFormat })
      } catch (e) {
        try {
          JsBarcode(el, this.validDigits, { ...options, format: 'CODE128' })
        } catch (e2) {
          console.warn('Barcode overlay:', e2)
        }
      }
    },
    async copyDigits() {
      const t = this.validDigits
      if (!t) return
      try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(t)
        } else {
          throw new Error('no clipboard')
        }
        this.copied = true
        if (this.copyTimer) clearTimeout(this.copyTimer)
        this.copyTimer = setTimeout(() => { this.copied = false }, 2000)
      } catch (e) {
        prompt('Kopjoni barkodin:', t)
      }
    }
  }
}
</script>
