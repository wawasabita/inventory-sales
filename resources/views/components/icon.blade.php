@props(['name', 'size' => 20])
@php
    $paths = [
        'dashboard' => 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z',
        'box' => 'M12 3l8 4.5v9L12 21l-8-4.5v-9zM4 7.5l8 4.5 8-4.5M12 12v9',
        'download' => 'M12 4v11M7 10l5 5 5-5M5 20h14',
        'cart' => 'M3 4h2l2.4 11h10l2-8H6M9 20h.01M17 20h.01',
        'tag' => 'M3 12V4h8l10 10-8 8zM7.5 8h.01',
        'truck' => 'M3 6h11v10H3zM14 9h4l3 3v4h-7M7 19h.01M17 19h.01',
        'plus' => 'M12 5v14M5 12h14',
        'search' => 'M11 4a7 7 0 1 0 0 14 7 7 0 0 0 0-14zM20 20l-4-4',
        'edit' => 'M4 20l1-4L16 5l3 3L8 19zM14 7l3 3',
        'trash' => 'M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13',
        'alert' => 'M12 4l9 16H3zM12 10v4M12 17h.01',
        'money' => 'M12 3v18M16 7.5c0-1.4-1.8-2.5-4-2.5s-4 1.1-4 2.5 1.8 2.5 4 2.5 4 1.1 4 2.5-1.8 2.5-4 2.5-4-1.1-4-2.5',
        'calendar' => 'M4 6h16v14H4zM4 10h16M8 3v4M16 3v4',
        'receipt' => 'M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6',
        'user' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21a8 8 0 0 1 16 0',
        'logout' => 'M9 4H5v16h4M16 8l4 4-4 4M20 12H9',
        'undo' => 'M9 14L4 9l5-5M4 9h10a6 6 0 0 1 0 12h-3',
        'check' => 'M5 12l5 5 9-10',
        'close' => 'M6 6l12 12M18 6L6 18',
        'store' => 'M4 9l1-5h14l1 5M4 9v11h16V9M4 9a3 3 0 0 0 5 0 3 3 0 0 0 6 0 3 3 0 0 0 5 0',
        'lock' => 'M6 11h12v9H6zM8 11V8a4 4 0 0 1 8 0v3',
    ];
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['box'] }}"/></svg>
