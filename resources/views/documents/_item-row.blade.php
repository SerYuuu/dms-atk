<div data-item-row class="group flex flex-wrap sm:flex-nowrap items-center gap-2 rounded-xl border border-slate-200/80 bg-slate-50/50 p-2.5 transition-all hover:border-slate-300 hover:bg-slate-50">
    <div class="w-full sm:flex-1">
        <label class="sr-only" for="item-name-{{ $index }}">Nama item</label>
        <input id="item-name-{{ $index }}" type="text" name="items[{{ $index }}][item_name]" value="{{ $row['item_name'] ?? '' }}"
               placeholder="Nama barang (mis. Kertas HVS A4 70gr)" class="field-input !py-2 !text-xs sm:!text-sm">
    </div>
    <div class="w-24 shrink-0">
        <label class="sr-only" for="item-qty-{{ $index }}">Jumlah</label>
        <div class="relative">
            <input id="item-qty-{{ $index }}" type="number" min="1" name="items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? 1 }}"
                   placeholder="Qty" class="field-input !py-2 !text-xs sm:!text-sm text-center">
        </div>
    </div>
    <div class="w-36 shrink-0">
        <label class="sr-only" for="item-price-{{ $index }}">Harga Satuan</label>
        <div class="relative">
            <input id="item-price-{{ $index }}" type="number" min="0" step="any" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? '' }}"
                   placeholder="Harga Satuan" class="field-input !py-2 !text-xs sm:!text-sm text-right">
        </div>
    </div>
    <div class="shrink-0 flex items-center">
        <button type="button" data-remove-item aria-label="Hapus item" title="Hapus baris ini"
                class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600 focus:outline-none">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
            </svg>
        </button>
    </div>
</div>