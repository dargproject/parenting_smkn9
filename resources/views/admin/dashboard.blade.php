@extends('layouts.app')

@section('content')
<div class="admin-dashboard space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="admin-title text-2xl font-bold">Dashboard Admin</h1>
            <p class="admin-muted text-sm">Ringkasan pengelolaan data SIM Sekolah.</p>
        </div>
    </div>

    @include('admin.partials.flash')

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Total Guru', 'value' => $totalGuru, 'icon' => 'fa-chalkboard-user', 'color' => 'blue'],
            ['label' => 'Total Siswa', 'value' => $totalSiswa, 'icon' => 'fa-user-graduate', 'color' => 'emerald'],
            ['label' => 'Total Kelas', 'value' => $totalKelas, 'icon' => 'fa-school', 'color' => 'amber'],
            ['label' => 'Tahun Ajaran Aktif', 'value' => $tahunAjaranAktif?->nama ?? 'Belum diatur', 'icon' => 'fa-calendar-days', 'color' => 'rose'],
        ] as $stat)
            <div class="admin-surface rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80">
                <div class="flex items-center justify-between">
                        <span class="admin-muted text-sm">{{ $stat['label'] }}</span>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-{{ $stat['color'] }}-500/10 text-{{ $stat['color'] }}-500">
                        <i class="fa-solid {{ $stat['icon'] }}"></i>
                    </span>
                </div>
                    <p class="admin-title mt-4 text-xl font-bold">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="admin-surface rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80">
            <h2 class="admin-title font-bold">Akses Cepat</h2>
            <div class="mt-4 grid gap-3">
                <a href="{{ route('admin.settings.edit') }}" class="rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-500">Pengaturan Sekolah</a>
                <a href="{{ route('admin.tahun-ajaran.index') }}" class="rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">Kelola Tahun Ajaran</a>
            </div>
        </div>

        <div class="admin-surface rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80 xl:col-span-2">
            <h2 class="admin-title font-bold">Pelanggaran Terbaru</h2>
            <div class="admin-divider mt-4 divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($pelanggaranTerbaru as $item)
                    <div class="flex items-center justify-between gap-3 py-3 text-sm">
                        <span class="admin-body">{{ $item->siswa?->nama ?? '-' }} · {{ $item->judul }}</span>
                        <span class="rounded-full bg-rose-500/10 px-2 py-1 text-xs font-semibold text-rose-500">{{ $item->poin }} poin</span>
                    </div>
                @empty
                    <p class="admin-muted py-3 text-sm">Belum ada pelanggaran.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="admin-surface rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80">
        <h2 class="admin-title font-bold">Statistik Bimbingan Konseling</h2>
        <p class="admin-muted text-xs mt-1">Hanya ringkasan jumlah -- isi kasus BK bersifat rahasia dan tidak ditampilkan di sini.</p>
        @php $prioritasLabel = ['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah']; @endphp
        <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
            <div class="admin-inner rounded-lg border border-slate-200 p-3 text-center dark:border-slate-700">
                <p class="admin-muted text-xs m-0">Kasus Aktif</p>
                <p class="admin-title mt-1 text-xl font-bold m-0">{{ $kasusBkAktif }}</p>
            </div>
            @foreach(['tinggi', 'sedang', 'rendah'] as $prioritas)
                <div class="admin-inner rounded-lg border border-slate-200 p-3 text-center dark:border-slate-700">
                    <p class="admin-muted text-xs m-0">Prioritas {{ $prioritasLabel[$prioritas] }}</p>
                    <p class="admin-title mt-1 text-xl font-bold m-0">{{ $kasusBkPerPrioritas[$prioritas] ?? 0 }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
