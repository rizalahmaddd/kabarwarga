@props([
    'name',
    'id' => null,
    'value' => null,
    'options' => [],
    'placeholder' => '-- Pilih --',
    'searchable' => null,
    'autosubmit' => false,
    'required' => false,
    'class' => '',
])

@php
    $id = $id ?? $name;

    // Normalisasi format options ke array asosiatif: ['value' => ..., 'label' => ...]
    $normalizedOptions = [];
    foreach ($options as $k => $v) {
        if (is_array($v) && isset($v['value'], $v['label'])) {
            $normalizedOptions[] = ['value' => (string) $v['value'], 'label' => (string) $v['label']];
        } elseif (is_object($v)) {
            // Jika Eloquent model atau generic object
            $optVal = (string) ($v->id ?? $k);
            $optLabel = (string) ($v->label ?? $v->name ?? $v->head_name ?? $v->number ?? $optVal);
            $normalizedOptions[] = ['value' => $optVal, 'label' => $optLabel];
        } else {
            $normalizedOptions[] = ['value' => (string) $k, 'label' => (string) $v];
        }
    }

    $optionCount = count($normalizedOptions);
    // Default: jika jumlah item di atas 6, fitur search otomatis aktif
    $isSearchable = $searchable !== null ? (bool) $searchable : ($optionCount > 6);

    // Cari label untuk value saat ini
    $selectedLabel = $placeholder;
    foreach ($normalizedOptions as $opt) {
        if ((string) $opt['value'] === (string) $value) {
            $selectedLabel = $opt['label'];
            break;
        }
    }
@endphp

<div class="relative custom-dropdown {{ $class }}"
     data-dropdown
     data-name="{{ $name }}"
     data-autosubmit="{{ $autosubmit ? 'true' : 'false' }}">

    {{-- Hidden native input untuk form submission --}}
    <input type="hidden"
           id="{{ $id }}"
           name="{{ $name }}"
           value="{{ $value ?? '' }}"
           @if($required) required @endif
           data-dropdown-input>

    {{-- Trigger Button --}}
    <button type="button"
            class="w-full h-11 px-3.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 shadow-xs flex items-center justify-between gap-2 text-left cursor-pointer transition hover:border-slate-300 focus:outline-none focus:border-daun focus:ring-2 focus:ring-daun/20"
            aria-haspopup="listbox"
            aria-expanded="false"
            data-dropdown-trigger>
        <span class="truncate block {{ empty($value) && $value !== '0' && $value !== 0 ? 'text-slate-400 font-normal' : 'text-slate-800 font-semibold' }}" data-dropdown-label>
            {{ $selectedLabel }}
        </span>
        <svg class="size-4 text-slate-400 shrink-0 transition-transform duration-200 pointer-events-none" data-dropdown-arrow viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </button>

    {{-- Dropdown Popover Menu --}}
    <div class="hidden absolute left-0 right-0 z-50 mt-1.5 min-w-[12rem] bg-white border border-slate-200 rounded-2xl shadow-xl p-1.5 transition-all"
         data-dropdown-menu
         role="listbox">

        {{-- Search Input (otomatis tampil jika item > 6 atau searchable=true) --}}
        @if ($isSearchable)
            <div class="p-1 mb-1 border-b border-slate-100">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" x2="16.65" y1="21" y2="16.65"/>
                        </svg>
                    </div>
                    <input type="text"
                           class="w-full h-8 pl-8 pr-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:border-daun focus:ring-1 focus:ring-daun"
                           placeholder="Cari pilihan ({{ $optionCount }} item)..."
                           autocomplete="off"
                           data-dropdown-search>
                </div>
            </div>
        @endif

        {{-- Options List --}}
        <div class="max-h-60 overflow-y-auto no-scrollbar space-y-0.5" data-dropdown-list>
            {{-- Default placeholder option jika ada dan tidak required --}}
            @if (!empty($placeholder) && ! $required)
                <button type="button"
                        class="w-full px-3 py-2 text-left text-xs rounded-xl flex items-center justify-between text-slate-400 hover:bg-slate-50 transition cursor-pointer"
                        data-dropdown-option
                        data-value=""
                        data-label="{{ $placeholder }}">
                    <span class="truncate">{{ $placeholder }}</span>
                    @if(empty($value) && $value !== '0' && $value !== 0)
                        <svg class="size-4 text-daun shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    @endif
                </button>
            @endif

            @foreach ($normalizedOptions as $opt)
                @php $isSelected = (string) $opt['value'] === (string) $value; @endphp
                <button type="button"
                        class="w-full px-3 py-2 text-left text-xs font-semibold rounded-xl flex items-center justify-between transition cursor-pointer {{ $isSelected ? 'bg-emerald-50 text-daun-dark' : 'text-slate-700 hover:bg-slate-50' }}"
                        data-dropdown-option
                        data-value="{{ $opt['value'] }}"
                        data-label="{{ $opt['label'] }}"
                        role="option"
                        aria-selected="{{ $isSelected ? 'true' : 'false' }}">
                    <span class="truncate">{{ $opt['label'] }}</span>
                    @if ($isSelected)
                        <svg class="size-4 text-daun shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    @endif
                </button>
            @endforeach

            {{-- Empty search result state --}}
            <div class="hidden py-4 text-center text-xs text-slate-400" data-dropdown-empty>
                Tidak ada pilihan yang cocok
            </div>
        </div>
    </div>
</div>
