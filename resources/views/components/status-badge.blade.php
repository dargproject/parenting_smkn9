{{--
    Badge status/prioritas, satu tempat untuk warnanya.

    - Default (tanpa prop `light`): untuk halaman dark-card khas portal guru/admin/BK
      (sudah ditangani sistem remap CSS tema, jadi cukup kelas gelap tanpa prefix `dark:`).
    - Dengan prop `light`: untuk halaman yang benar-benar toggle terang/gelap (mis. Portal Orang Tua),
      pakai pasangan warna terang + `dark:` supaya kontras tetap terjaga di kedua mode.
--}}
@props(['tone' => 'slate', 'light' => false])
@php
    $toneGelap = [
        'amber' => 'bg-amber-500/10 text-amber-400',
        'emerald' => 'bg-emerald-500/10 text-emerald-400',
        'rose' => 'bg-rose-500/10 text-rose-400',
        'blue' => 'bg-blue-500/10 text-blue-400',
        'purple' => 'bg-purple-500/10 text-purple-400',
        'slate' => 'bg-slate-700/40 text-slate-400',
    ];
    $toneGanda = [
        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        'rose' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400',
        'slate' => 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
    ];
    $palette = $light ? $toneGanda : $toneGelap;
@endphp
<span {{ $attributes->merge(['class' => 'rounded-full px-2.5 py-1 text-xs font-bold whitespace-nowrap '.($palette[$tone] ?? $palette['slate'])]) }}>{{ $slot }}</span>
