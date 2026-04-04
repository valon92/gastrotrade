/**
 * Gjeneron HTML + tekst të faturës së porosisë (i njëjtë me logjikën e Cart.printOrder).
 * `order` duhet të përmbajë fushat e mishëruara nga konfirmimi i porosisë (customer, items, totals).
 */
import { OFFICIAL_ORDER_EMAIL } from '../config/site'

export function orderItemIsPackageSale(item) {
  if (!item) return false
  const ut = (item.unit_type || '').toString().toLowerCase().trim()
  if (ut === 'cp' || ut === 'piece') return false
  return item.sold_by_package === true || item.sold_by_package === 1
}

export function orderItemInvoiceQty(item) {
  const q = parseFloat(item.quantity) || 0
  const ppk = parseInt(item.pieces_per_package, 10)
  const unitPrice = item.unit_price != null ? parseFloat(item.unit_price) : null
  const lineTotal = item.total_price != null ? parseFloat(item.total_price) : null
  let mode = orderItemIsPackageSale(item) && ppk > 0 ? 'package' : 'piece'
  let packageCount = q
  let pieceCount = mode === 'package' ? q * ppk : q
  const lineDiscount = parseFloat(item.discount_amount) || 0
  if (
    mode === 'package' &&
    lineDiscount < 0.01 &&
    unitPrice != null &&
    lineTotal != null &&
    !isNaN(unitPrice) &&
    !isNaN(lineTotal) &&
    unitPrice > 0
  ) {
    const expected = pieceCount * unitPrice
    if (Math.abs(expected - lineTotal) > 0.05) {
      const implied = Math.round(lineTotal / unitPrice)
      if (implied > 0 && Math.abs(implied * unitPrice - lineTotal) <= 0.05) {
        mode = 'piece'
        pieceCount = implied
      }
    }
  }
  return { mode, packageCount, ppk, pieceCount }
}

export function invoiceLineQtyParts(item, fmtQty) {
  const iq = orderItemInvoiceQty(item)
  if (iq.mode === 'package' && iq.ppk > 0) {
    const nbsp = '\u00A0'
    const q = iq.packageCount
    const total = iq.pieceCount
    const ppk = iq.ppk
    const html =
      '<span class="inv-qty-stack inv-qty-stack--pkg">' +
      '<span class="inv-qty-line inv-qty-line1">' + fmtQty(q) + nbsp + 'komplete</span>' +
      '<span class="inv-qty-line inv-qty-line2">×' + nbsp + ppk + nbsp + 'cp</span>' +
      '<span class="inv-qty-line inv-qty-line3">=' + nbsp + fmtQty(total) + nbsp + 'cp</span>' +
      '</span>'
    const plain = fmtQty(q) + ' komplete × ' + ppk + ' cp = ' + fmtQty(total) + ' cp'
    return { qtyHtml: html, qtyPlain: plain, unit: 'Copë' }
  }
  const pcs = iq.pieceCount
  return { qtyHtml: fmtQty(pcs), qtyPlain: fmtQty(pcs), unit: 'Copë' }
}

function formatDateSq(value) {
  if (!value) return '-'
  return new Date(value).toLocaleString('sq-AL', { dateStyle: 'medium', timeStyle: 'short' })
}

/**
 * @param {object} order
 * @returns {{ html: string, plainText: string }}
 */
export function buildInvoiceHtmlAndText(order) {
  if (!order) return { html: '', plainText: '' }

  const invoiceDateFormatted = formatDateSq(order.created_at)
  const isPaid = order.is_paid === true || order.is_paid === 1
  const hasVat = order.has_vat === true || order.has_vat === 1
  const vatRate = 18
  const fmtNum = n => (n != null && !isNaN(n) ? Number(n).toFixed(2) : '-')
  const fmtQty = n => {
    if (n == null || isNaN(n)) return '-'
    const num = Number(n)
    return Number.isInteger(num) ? String(num) : num.toFixed(2)
  }
  const amountBeforeVat =
    hasVat && order.total_amount
      ? parseFloat(order.total_amount) / 1.18
      : order.amount_before_vat != null
        ? parseFloat(order.amount_before_vat)
        : order.total_amount
          ? parseFloat(order.total_amount)
          : null
  const vatAmount =
    hasVat && order.total_amount
      ? parseFloat(order.total_amount) - parseFloat(order.total_amount) / 1.18
      : order.vat_amount != null
        ? parseFloat(order.vat_amount)
        : 0
  const totalItemDiscounts = (order.items || []).reduce((sum, item) => sum + (parseFloat(item.discount_amount) || 0), 0)
  const orderDiscount = parseFloat(order.discount_amount) || 0
  const hasDiscount = totalItemDiscounts > 0.009 || orderDiscount > 0.009
  const totalDiscountAmount = totalItemDiscounts + orderDiscount
  const valueBeforeDiscount =
    (order.subtotal != null ? parseFloat(order.subtotal) : null) ||
    (order.items || []).reduce((s, i) => s + (parseFloat(i.total_price) || 0), 0)

  const invoiceItemRowCells = (item, idx) => {
    const barcode =
      item.barcode != null && item.barcode !== ''
        ? String(item.barcode)
        : item.product && item.product.barcode != null && item.product.barcode !== ''
          ? String(item.product.barcode)
          : ''
    const qp = invoiceLineQtyParts(item, fmtQty)
    const piecesForLine = orderItemInvoiceQty(item).pieceCount
    const unitPrice = item.unit_price != null ? parseFloat(item.unit_price) : null
    const lineTotal =
      item.total_price != null ? parseFloat(item.total_price) : unitPrice ? unitPrice * piecesForLine : 0
    const unitPriceNoVat = hasVat && unitPrice ? unitPrice / 1.18 : unitPrice
    const discountPct = 0
    const base =
      '<td class="inv-num">' +
      (idx + 1) +
      '</td>' +
      '<td class="inv-code">' +
      (barcode || '-') +
      '</td>' +
      '<td class="inv-desc">' +
      (item.product_name || '') +
      '</td>' +
      '<td class="inv-qty">' +
      qp.qtyHtml +
      '</td>' +
      '<td class="inv-unit">' +
      qp.unit +
      '</td>'
    if (hasVat) {
      let rest = '<td class="inv-num">' + (unitPriceNoVat != null ? fmtNum(unitPriceNoVat) : '-') + '</td>'
      if (hasDiscount) rest += '<td class="inv-num">' + fmtNum(discountPct) + '</td>'
      rest +=
        '<td class="inv-num">' +
        vatRate +
        '</td>' +
        '<td class="inv-num">' +
        (unitPrice != null ? fmtNum(unitPrice) : '-') +
        '</td>' +
        '<td class="inv-num">' +
        (lineTotal > 0 ? fmtNum(lineTotal) : '-') +
        '</td>'
      return base + rest
    }
    let rest = ''
    if (hasDiscount) rest += '<td class="inv-num">' + fmtNum(discountPct) + '</td>'
    rest +=
      '<td class="inv-num">' +
      (unitPrice != null ? fmtNum(unitPrice) : '-') +
      '</td>' +
      '<td class="inv-num">' +
      (lineTotal > 0 ? fmtNum(lineTotal) : '-') +
      '</td>'
    return base + rest
  }

  const invoiceItemRowTabs = (item, idx) => {
    const barcode =
      item.barcode != null && item.barcode !== ''
        ? String(item.barcode)
        : item.product && item.product.barcode
          ? String(item.product.barcode)
          : '-'
    const qp = invoiceLineQtyParts(item, fmtQty)
    const piecesForLine = orderItemInvoiceQty(item).pieceCount
    const unitPrice = item.unit_price != null ? parseFloat(item.unit_price) : null
    const lineTotal =
      item.total_price != null ? parseFloat(item.total_price) : unitPrice ? unitPrice * piecesForLine : 0
    const unitPriceNoVat = hasVat && unitPrice ? unitPrice / 1.18 : unitPrice
    const discountPct = 0
    const parts = [String(idx + 1), barcode || '-', item.product_name || '', qp.qtyPlain, qp.unit]
    if (hasVat) {
      parts.push(unitPriceNoVat != null ? fmtNum(unitPriceNoVat) : '-')
      if (hasDiscount) parts.push(fmtNum(discountPct))
      parts.push(String(vatRate))
      parts.push(unitPrice != null ? fmtNum(unitPrice) : '-')
      parts.push(lineTotal > 0 ? fmtNum(lineTotal) : '-')
    } else {
      if (hasDiscount) parts.push(fmtNum(discountPct))
      parts.push(unitPrice != null ? fmtNum(unitPrice) : '-')
      parts.push(lineTotal > 0 ? fmtNum(lineTotal) : '-')
    }
    return parts.join('\t')
  }

  const itemsHeaderTabs =
    'No\tBarcode\tEmertimi\tSasia\tNjesia' +
    (hasVat ? '\tCmimi pa TVSH' : '') +
    (hasDiscount ? '\tRabati %' : '') +
    (hasVat ? '\tTVSH %\tCmimi me TVSH\tVlera me TVSH' : '\tCmimi\tVlera')

  const itemsHeaderHtml =
    '<th>No</th><th>Barcode</th><th>Emertimi</th><th>Sasia</th><th>Njesia</th>' +
    (hasVat ? '<th>Çmimi pa TVSH</th>' : '') +
    (hasDiscount ? '<th>Rabati %</th>' : '') +
    (hasVat ? '<th>TVSH %</th><th>Çmimi me TVSH</th><th>Vlera me TVSH</th>' : '<th>Çmimi</th><th>Vlera</th>')

  const itemsRows = (order.items || [])
    .map(
      (item, idx) =>
        '<tbody class="inv-tbody-item" style="page-break-inside:avoid;break-inside:avoid;-webkit-region-break:avoid"><tr>' +
        invoiceItemRowCells(item, idx) +
        '</tr></tbody>'
    )
    .join('')

  const buyerName = (order.business_name || order.customer_name || 'N/A').trim()
  const buyerAddress = order.location_street_number || ''
  const buyerCity = (order.location_city || order.city || 'N/A').trim()
  const buyerFiscal = (order.fiscal_number || '').trim() || '-'
  const expDate = invoiceDateFormatted

  const taxBase18 = amountBeforeVat != null ? fmtNum(amountBeforeVat) : '0.00'
  const taxVat18 = hasVat ? fmtNum(vatAmount) : '0.00'
  const taxVal18 = order.total_amount ? fmtNum(parseFloat(order.total_amount)) : '0.00'
  const valBeforeDiscount = valueBeforeDiscount != null ? fmtNum(valueBeforeDiscount) : '0.00'
  const discountVal = hasDiscount ? fmtNum(totalDiscountAmount) : '0.00'
  const valNoVat = amountBeforeVat != null ? fmtNum(amountBeforeVat) : '0.00'
  const paymentDone = isPaid ? (order.total_amount ? fmtNum(parseFloat(order.total_amount)) : '0.00') : '0.00'
  const totalForPay = order.total_amount ? fmtNum(parseFloat(order.total_amount)) : '-'
  const mbetjaVal = order.total_amount ? (isPaid ? '0.00' : fmtNum(parseFloat(order.total_amount))) : '-'
  const paymentBadgeHtml = isPaid
    ? '<span class="inv-paid inv-paid--yes">E Paguar</span>'
    : '<span class="inv-paid inv-paid--no">E PaPaguar</span>'

  const itemsTextLines = (order.items || []).map((item, idx) => invoiceItemRowTabs(item, idx)).join('\n')

  const fullInvoiceText =
    'ARON TRADE\n' +
    'Nrf/NIPT: ' +
    (order.company_nrf || '—') +
    '\n' +
    'tel: +383 48 75 66 46 / +383 44 82 43 14\n' +
    'email: ' +
    OFFICIAL_ORDER_EMAIL +
    '\n' +
    'Adresë: Ferizaj, Kosovë, Rruga Lidhja e Prizrenit\n' +
    'Nrb: ' +
    (order.company_nrb || '—') +
    '\n' +
    'Tvsh: ' +
    (order.company_tvsh || '—') +
    '\n\n' +
    'Nr. Faturës: ' +
    (order.order_number || 'N/A') +
    '\n\n' +
    'BLERESI\n' +
    buyerName +
    '\n' +
    (buyerAddress ? 'Adresa: ' + buyerAddress + '\n' : '') +
    'Qyteti: ' +
    buyerCity +
    '\n' +
    'No fiskal: ' +
    buyerFiscal +
    '\n' +
    'Numri unik: ' +
    buyerFiscal +
    '\n' +
    (order.phone ? 'Telefon: ' + order.phone + '\n' : '') +
    '\n' +
    'Data fatura\tKushtet\tData e skadimit\tUser\tReferenca\tLokacioni\n' +
    invoiceDateFormatted +
    '\t\t' +
    expDate +
    '\tKlient\t\t' +
    (order.location_unit_name || '-') +
    '\n\n' +
    itemsHeaderTabs +
    '\n' +
    itemsTextLines +
    '\n\n' +
    (hasVat
      ? 'Normat Tatimore\tBaza\tTVSH\tVlera\n' + 'TVSH 0%\t0.00\t0.00\t0.00\n' + 'TVSH 18%\t' + taxBase18 + '\t' + taxVat18 + '\t' + taxVal18 + '\n\n'
      : '') +
    (hasDiscount ? 'Vlera para zbritjes\t' + valBeforeDiscount + '\nRabati\t' + discountVal + '\n' : '') +
    (hasVat ? 'Vlera pa TVSH\t' + valNoVat + '\nTVSH\t' + taxVat18 + '\n' : '') +
    'Vlera për pagesë (EUR)\t' +
    totalForPay +
    '\n' +
    'Pagesa\t' +
    paymentDone +
    '\n' +
    'Mbetja\t' +
    mbetjaVal +
    '\n\n' +
    'Dërgoi: Aron Trade\n' +
    'Pranoi: ' +
    buyerName +
    '\n\n' +
    'Llogaria bankare:'

  const htmlContent =
    '<!DOCTYPE html><html lang="sq">' +
    '<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">' +
    '<title>Faturë ' +
    (order.order_number || 'N/A') +
    '</title>' +
    '<style>' +
    '*{box-sizing:border-box}' +
    'html,body{-webkit-print-color-adjust:exact;print-color-adjust:exact;color-adjust:exact}' +
    'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;padding:24px 32px;color:#111827;font-size:13px;line-height:1.4;max-width:900px;margin:0 auto;background:#fff}' +
    '.inv-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;margin-bottom:16px}' +
    '.inv-header{display:flex;flex-direction:column;gap:0;margin-bottom:20px;padding-bottom:16px;border-bottom:2px solid #0d9488}' +
    '.inv-header-row1{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;width:100%;margin-bottom:14px}' +
    '.inv-header-row2{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;width:100%}' +
    '.inv-header-address{text-align:right;flex:1;max-width:52%;display:flex;flex-direction:column;align-items:flex-end}' +
    '.inv-header-address .inv-title{margin-top:8px}.inv-header-address .inv-title:first-child{margin-top:0}' +
    '.inv-header-address>div:not(.inv-title){max-width:100%;word-break:break-word}' +
    '.inv-seller-contact{flex:1;min-width:200px;max-width:48%}' +
    '.inv-seller-contact .inv-title{margin-top:8px}.inv-seller-contact .inv-title:first-child{margin-top:0}' +
    '.inv-invoice-no-block{margin:0;padding:0;border:none;width:auto;max-width:280px;text-align:right;flex-shrink:0}' +
    '.inv-invoice-no-block .inv-nr{text-align:right;margin-top:4px}' +
    '.inv-title{font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px}' +
    '.inv-company{font-weight:700;font-size:18px;color:#0f766e;margin:0;padding:0;line-height:1.2;display:block;flex-shrink:0}' +
    '.inv-nr{text-align:right;font-weight:700;font-size:14px;color:#0d9488}' +
    '.inv-bleresi{background:#f8fafc;padding:12px 16px;border-radius:8px;margin-bottom:16px;border:1px solid #e2e8f0}' +
    '.inv-bleresi h3{font-size:12px;text-transform:uppercase;color:#64748b;margin:0 0 8px 0}' +
    '.inv-paid{display:inline-block;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:0.02em;border:1px solid transparent}' +
    '.inv-paid--yes{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}' +
    '.inv-paid--no{background:#fff7ed;color:#9a3412;border-color:#fed7aa}' +
    '.inv-meta{width:100%;border-collapse:collapse;margin-bottom:16px;font-size:12px}' +
    '.inv-meta th,.inv-meta td{border:1px solid #e2e8f0;padding:6px 10px;text-align:left}' +
    '.inv-meta th{background:#f1f5f9;font-weight:600;color:#475569}' +
    '.inv-table{width:100%;border-collapse:collapse;margin-bottom:16px;font-size:12px}' +
    '.inv-table th,.inv-table td{border:1px solid #e2e8f0;padding:6px 8px}' +
    '.inv-table th{background:#0d9488;color:#fff;font-weight:600;text-align:center}' +
    '.inv-table .inv-num{text-align:right}.inv-table .inv-desc{text-align:left}.inv-table .inv-code{text-align:center}' +
    '.inv-table .inv-qty{text-align:right;vertical-align:top}.inv-qty-stack{display:block;text-align:right}' +
    '.inv-qty-stack--pkg{display:flex;flex-direction:column;align-items:flex-end;gap:3px;text-align:right}' +
    '.inv-qty-stack--pkg .inv-qty-line{font-variant-numeric:tabular-nums;line-height:1.3;display:block}' +
    '.inv-qty-stack--pkg .inv-qty-line1{font-size:12px;font-weight:600;color:#0f172a;letter-spacing:-0.02em}' +
    '.inv-qty-stack--pkg .inv-qty-line2{font-size:11px;font-weight:400;color:#64748b}' +
    '.inv-qty-stack--pkg .inv-qty-line3{font-size:12px;font-weight:800;color:#0f172a;letter-spacing:-0.02em}' +
    '.inv-tax{width:100%;max-width:320px;margin-left:auto;border-collapse:collapse;font-size:12px;margin-bottom:12px}' +
    '.inv-tax th,.inv-tax td{border:1px solid #e2e8f0;padding:6px 10px;text-align:right}' +
    '.inv-tax th{background:#f1f5f9;font-weight:600}' +
    '.inv-totals{max-width:280px;margin-left:auto;font-size:13px}' +
    '.inv-totals .row{display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #e2e8f0}' +
    '.inv-totals .row.status{border-bottom:none;padding-bottom:6px}' +
    '.inv-totals .row.total{font-weight:700;font-size:15px;color:#0d9488;border-bottom:none;padding-top:8px;margin-top:4px;border-top:2px solid #0d9488}' +
    '.inv-footer{display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin-top:40px;padding-top:24px;border-top:2px solid #e2e8f0}' +
    '.inv-sig{min-width:200px;max-width:45%}' +
    '.inv-sig--left{text-align:left}' +
    '.inv-sig--right{text-align:right;margin-left:auto}' +
    '.inv-sig .line{border-top:1px solid #111827;padding-top:4px;margin-top:32px;font-weight:600;font-size:12px}' +
    '.inv-sig .sub{font-size:11px;color:#64748b;margin-top:4px}' +
    '.inv-bank{font-size:12px;color:#64748b;margin-top:16px}' +
    '.inv-pdf-hint{font-size:13px;color:#64748b;text-align:center;padding:20px 16px;margin:24px 0 0 0;border-top:1px solid #e2e8f0;background:#f8fafc;line-height:1.5}' +
    '.inv-pdf-hint strong{color:#0f766e}' +
    '@page{size:A4;margin:12mm}' +
    '@media print{' +
    'body{padding:0!important;max-width:100%!important;margin:0!important;background:#fff!important}' +
    '.inv-table-wrap{overflow:visible!important}' +
    '.inv-table{min-width:0!important;width:100%!important;page-break-inside:auto}' +
    'thead{display:table-header-group}' +
    'tbody.inv-tbody-item{break-inside:avoid!important;page-break-inside:avoid!important}' +
    'tbody.inv-tbody-item tr{break-inside:avoid!important;page-break-inside:avoid!important}' +
    'tbody.inv-tbody-item td{break-inside:avoid!important;page-break-inside:avoid!important}' +
    '.inv-bleresi,.inv-totals,.inv-tax,.inv-footer,.inv-bank,.inv-sig{break-inside:avoid;page-break-inside:avoid}' +
    '.inv-pdf-hint{display:none!important}' +
    '}' +
    '@media (max-width:768px){' +
    'body{padding:12px 16px;font-size:12px;max-width:100%}' +
    '.inv-header-row1,.inv-header-row2{flex-direction:row!important;gap:10px!important}' +
    '.inv-table-wrap{margin-left:-16px;margin-right:-16px;padding:0 16px}' +
    '.inv-table{font-size:11px;min-width:720px}' +
    '}' +
    '</style></head><body>' +
    '<div class="inv-header">' +
    '<div class="inv-header-row1">' +
    '<h2 class="inv-company">Aron Trade</h2>' +
    '<div class="inv-header-address">' +
    '<div class="inv-title">Adresë</div><div>Ferizaj, Kosovë, Rruga Lidhja e Prizrenit</div>' +
    '<div class="inv-title">Nrb</div><div>' +
    (order.company_nrb || '—') +
    '</div>' +
    '<div class="inv-title">Tvsh</div><div>' +
    (order.company_tvsh || '—') +
    '</div>' +
    '</div></div>' +
    '<div class="inv-header-row2">' +
    '<div class="inv-seller-contact">' +
    '<div class="inv-title">Nrf / NIPT</div><div>' +
    (order.company_nrf || '—') +
    '</div>' +
    '<div class="inv-title">tel</div><div>+383 48 75 66 46 / +383 44 82 43 14</div>' +
    '<div class="inv-title">email</div><div>' +
    OFFICIAL_ORDER_EMAIL +
    '</div>' +
    '</div>' +
    '<div class="inv-invoice-no-block">' +
    '<div class="inv-title">Nr. Faturës</div>' +
    '<div class="inv-nr">' +
    (order.order_number || 'N/A') +
    '</div>' +
    '</div></div></div>' +
    '<div class="inv-bleresi">' +
    '<h3>Bleresi</h3>' +
    '<div><strong>' +
    buyerName +
    '</strong></div>' +
    (buyerAddress ? '<div>Adresa: ' + buyerAddress + '</div>' : '') +
    '<div>Qyteti: ' +
    buyerCity +
    '</div>' +
    '<div>No fiskal: ' +
    buyerFiscal +
    '</div>' +
    '<div>Numri unik: ' +
    buyerFiscal +
    '</div>' +
    (order.phone ? '<div>Telefon: ' + order.phone + '</div>' : '') +
    '</div>' +
    '<div class="inv-table-wrap"><table class="inv-meta">' +
    '<tr><th>Data fatura</th><th>Kushtet</th><th>Data e skadimit</th><th>User</th><th>Referenca</th><th>Lokacioni</th></tr>' +
    '<tr><td>' +
    invoiceDateFormatted +
    '</td><td></td><td>' +
    expDate +
    '</td><td>Klient</td><td></td><td>' +
    (order.location_unit_name || '-') +
    '</td></tr>' +
    '</table></div>' +
    '<div class="inv-table-wrap"><table class="inv-table' +
    (!hasVat && !hasDiscount ? ' inv-table--simple' : '') +
    '">' +
    '<thead><tr>' +
    itemsHeaderHtml +
    '</tr></thead>' +
    itemsRows +
    '</table></div>' +
    (hasVat
      ? '<table class="inv-tax">' +
        '<tr><th>Normat Tatimore</th><th>Baza</th><th>TVSH</th><th>Vlera</th></tr>' +
        '<tr><td>TVSH 0%</td><td>0.00</td><td>0.00</td><td>0.00</td></tr>' +
        '<tr><td>TVSH 18%</td><td>' +
        taxBase18 +
        '</td><td>' +
        taxVat18 +
        '</td><td>' +
        taxVal18 +
        '</td></tr>' +
        '</table>'
      : '') +
    '<div class="inv-totals">' +
    '<div class="row status"><span>Statusi</span><span>' +
    paymentBadgeHtml +
    '</span></div>' +
    (hasDiscount
      ? '<div class="row"><span>Vlera para zbritjes</span><span>' +
        valBeforeDiscount +
        '</span></div>' +
        '<div class="row"><span>Rabati</span><span>' +
        discountVal +
        '</span></div>'
      : '') +
    (hasVat
      ? '<div class="row"><span>Vlera pa TVSH</span><span>' +
        valNoVat +
        '</span></div>' +
        '<div class="row"><span>TVSH</span><span>' +
        taxVat18 +
        '</span></div>'
      : '') +
    '<div class="row total"><span>Vlera për pagesë (EUR)</span><span>' +
    (order.total_amount ? fmtNum(parseFloat(order.total_amount)) : '-') +
    '</span></div>' +
    '<div class="row"><span>Pagesa</span><span>' +
    paymentDone +
    '</span></div>' +
    '<div class="row"><span>Mbetja</span><span>' +
    mbetjaVal +
    '</span></div>' +
    '</div>' +
    '<div class="inv-footer">' +
    '<div class="inv-sig inv-sig--left"><div class="line">Dërgoi</div><div class="sub">Aron Trade</div></div>' +
    '<div class="inv-sig inv-sig--right"><div class="line">Pranoi</div><div class="sub">' +
    buyerName +
    '</div></div>' +
    '</div>' +
    '<div class="inv-bank">Llogaria bankare:</div>' +
    '</body></html>'

  return { html: htmlContent, plainText: fullInvoiceText }
}

/** Hap print dialog për «Ruaj si PDF» — njësoj si shporta */
export function openInvoicePrintWindowForPdf(html) {
  if (!html) return false
  const hintHtml =
    '<p class="inv-pdf-hint" role="note"><strong>PDF:</strong> Në listën e printerave zgjidhni <strong>Ruaj si PDF</strong>.</p>'
  const docHtml = html.includes('inv-pdf-hint') ? html : html.replace('</body>', hintHtml + '</body>')
  const w = window.open('', '_blank', 'noopener,noreferrer')
  if (!w) return false
  w.document.open()
  w.document.write(docHtml)
  w.document.close()
  w.focus()
  setTimeout(() => {
    try {
      w.print()
    } catch (e) {
      console.error(e)
    }
  }, 400)
  return true
}
