<x-mail::message>
# 📦 Porosi e Re - AronTrade

**Numri i Porosisë:** #{{ $orderData['order_number'] ?? 'N/A' }}

## 👤 Të Dhënat e Porositësit

<x-mail::panel>
**Emri:** {{ $customerData['name'] ?? 'N/A' }}  
**Biznesi:** {{ $customerData['storeName'] ?? 'N/A' }}  
**Numri Fiskal:** {{ $customerData['fiscalNumber'] ?? 'N/A' }}  
**Qyteti:** {{ $customerData['city'] ?? 'N/A' }}  
@if(!empty($customerData['phone']))
**Telefon/Viber:** {{ $customerData['phone'] }}  
@endif

@if(!empty($customerData['locationUnitName']))
**📍 Të Dhënat e Pikës/Njësisë:**  
Pika/Njësia: {{ $customerData['locationUnitName'] }}  
@if(!empty($customerData['locationStreetNumber']))
Adresa: {{ $customerData['locationStreetNumber'] }}  
@endif
@if(!empty($customerData['locationCity']))
Vendi/Qyteti: {{ $customerData['locationCity'] }}  
@endif
@if(!empty($customerData['locationPhone']))
Telefon: {{ $customerData['locationPhone'] }}  
@endif
@if(!empty($customerData['locationViber']))
Viber: {{ $customerData['locationViber'] }}  
@endif
@endif
</x-mail::panel>

## 🛒 Produktet e Porosisë

@foreach($cartItems as $index => $item)
**{{ $index + 1 }}. {{ $item['name'] ?? 'Produkt i panjohur' }}**

- **Sasia:** 
  @if(!empty($item['sold_by_package']) && !empty($item['pieces_per_package']))
    {{ $item['quantity'] ?? 0 }} kompleti ({{ ($item['quantity'] ?? 0) * ($item['pieces_per_package'] ?? 1) }} copa)
  @else
    {{ $item['quantity'] ?? 0 }} copë
  @endif

- **Çmimi:** 
  @if(!empty($item['price']) && $item['price'] > 0)
    {{ number_format($item['price'], 2) }} €/copë
  @else
    Sipas kërkesës
  @endif

- **Total:** 
  @if(!empty($item['price']) && $item['price'] > 0)
    @php
      $itemTotal = 0;
      $quantity = $item['quantity'] ?? 0;
      $price = $item['price'] ?? 0;
      if (!empty($item['sold_by_package']) && !empty($item['pieces_per_package'])) {
        $itemTotal = $price * $quantity * ($item['pieces_per_package'] ?? 1);
      } else {
        $itemTotal = $price * $quantity;
      }
    @endphp
    {{ number_format($itemTotal, 2) }} €
  @else
    Sipas kërkesës
  @endif

---
@endforeach

## 📊 Përmbledhje

**Total Produkte:** {{ $totalItems ?? 0 }} produkt(e)

**Vlera Totale:** 
@if(!empty($totalPrice) && $totalPrice > 0)
**{{ number_format($totalPrice, 2) }} €**
@else
**Sipas kërkesës**
@endif

---

**Data e Porosisë:** {{ now()->format('d.m.Y H:i') }}

<x-mail::button :url="config('app.url') . '/admin/sales?order=' . urlencode($orderData['order_number'] ?? '')">
Shiko Porosinë në Admin Panel
</x-mail::button>

Faleminderit,<br>
{{ config('app.name') }}
</x-mail::message>
