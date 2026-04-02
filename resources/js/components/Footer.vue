<template>
  <footer class="relative overflow-hidden bg-slate-950 text-white">
    <div aria-hidden="true" class="absolute inset-0 bg-[radial-gradient(circle_at_12%_18%,rgba(20,184,166,0.28),transparent_35%),radial-gradient(circle_at_82%_75%,rgba(59,130,246,0.2),transparent_32%)]"></div>
    <div aria-hidden="true" class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-400/60 to-transparent"></div>

    <div class="relative max-w-7xl mx-auto py-14 px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
        <div class="lg:col-span-5">
          <div class="inline-flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 shadow-lg shadow-black/20">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary-600 text-white font-bold">AT</span>
            <div>
              <h3 class="text-lg leading-none font-semibold">AronTrade</h3>
              <p class="mt-1 text-[11px] uppercase tracking-[0.18em] text-slate-400">Furnizime Profesionale B2B</p>
            </div>
          </div>
          <p class="mt-5 max-w-md text-slate-300 leading-relaxed">
            Platformë moderne për furnizime biznesi: produkte të përzgjedhura, porosi të thjeshta dhe shërbim i besueshëm për klientët profesionistë.
          </p>

          <div class="mt-6 grid grid-cols-2 gap-3 max-w-md">
            <div class="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-3">
              <p class="text-[11px] uppercase tracking-wider text-slate-400">Fokus</p>
              <p class="mt-1 text-sm font-semibold text-white">Cilësi & Konsistencë</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-3">
              <p class="text-[11px] uppercase tracking-wider text-slate-400">Mision</p>
              <p class="mt-1 text-sm font-semibold text-white">Partneritet Afatgjatë</p>
            </div>
          </div>
        </div>

        <div class="lg:col-span-3">
          <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-300 mb-4">Navigim i Shpejtë</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <router-link to="/" class="inline-flex items-center gap-2 text-slate-300 hover:text-white transition-colors duration-200">
                <span class="h-1.5 w-1.5 rounded-full bg-primary-400"></span>
                Ballina
              </router-link>
            </li>
            <li>
              <router-link to="/produktet" class="inline-flex items-center gap-2 text-slate-300 hover:text-white transition-colors duration-200">
                <span class="h-1.5 w-1.5 rounded-full bg-primary-400"></span>
                Produktet
              </router-link>
            </li>
            <li>
              <router-link to="/rreth-nesh" class="inline-flex items-center gap-2 text-slate-300 hover:text-white transition-colors duration-200">
                <span class="h-1.5 w-1.5 rounded-full bg-primary-400"></span>
                Rreth Nesh
              </router-link>
            </li>
            <li>
              <router-link to="/kontakt" class="inline-flex items-center gap-2 text-slate-300 hover:text-white transition-colors duration-200">
                <span class="h-1.5 w-1.5 rounded-full bg-primary-400"></span>
                Kontakt
              </router-link>
            </li>
          </ul>
        </div>

        <div class="lg:col-span-4">
          <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-300 mb-4">Kontakt</h3>
          <div class="space-y-3 text-sm text-slate-300">
            <p class="flex items-start gap-2">
              <span class="text-primary-300">✉</span>
              <span class="break-all">{{ officialOrderEmail }}</span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-primary-300">☎</span>
              <span class="flex flex-wrap gap-x-3 gap-y-1">
                <span class="whitespace-nowrap">048 75 66 46</span>
                <span class="whitespace-nowrap">044 82 43 14</span>
              </span>
            </p>
            <p class="flex items-start gap-2">
              <span class="text-primary-300">⌖</span>
              <span class="flex flex-wrap gap-x-3 gap-y-1">
                <span class="whitespace-nowrap">Ferizaj, Kosovë</span>
                <span class="whitespace-nowrap">Rruga Lidhja E Prizerent</span>
              </span>
            </p>
          </div>

          <div class="mt-5">
            <router-link
              to="/admin/login"
              class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-slate-100 hover:text-white bg-slate-800/80 hover:bg-slate-700 rounded-xl transition-colors duration-200 border border-slate-700 shadow-sm"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Admin Panel
            </router-link>
          </div>
        </div>
      </div>

      <div class="mt-10 pt-6 border-t border-slate-800/90 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm text-slate-400">
        <p>&copy; {{ currentYear }} AronTrade. Të gjitha të drejtat e rezervuara.</p>
        <p class="text-slate-500">Ndërtuar për performancë, stabilitet dhe përvojë moderne.</p>
      </div>
    </div>
  </footer>
</template>

<script>
import { OFFICIAL_ORDER_EMAIL } from '../config/site'

export default {
  name: 'Footer',
  data() {
    return {
      officialOrderEmail: OFFICIAL_ORDER_EMAIL
    }
  },
  computed: {
    currentYear() {
      return new Date().getFullYear()
    }
  }
}
</script>

<style scoped>
</style>
