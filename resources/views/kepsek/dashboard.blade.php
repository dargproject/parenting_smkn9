@extends('layouts.app')

@section('content')
<div class="admin-dashboard space-y-6">

@include('admin.partials.flash')

<!-- ============================================ -->
<!-- PANE: DASHBOARD GLOBAL -->
<!-- ============================================ -->
<div id="pane-kepsek-dashboard" class="pane-content fade-transition space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h4 class="text-xl font-bold m-0 text-slate-100">Dashboard Eksekutif Kepala Sekolah</h4>
            <p class="text-slate-400 text-sm m-0 mt-1">Ringkasan analitis operasional dan kesiswaan berbasis data tercatat di sistem.</p>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="text-sm text-slate-400 font-medium">Siswa Aktif</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10 text-blue-400">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-slate-100">{{ number_format($stats['total_siswa']) }}</h3>
                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400 mt-2">
                    <i class="fa-solid fa-school"></i> {{ $stats['total_rombel'] }} Rombel
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="text-sm text-slate-400 font-medium">Kehadiran Tercatat</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                    <i class="fa-solid fa-user-check text-lg"></i>
                </div>
            </div>
            <div>
                @if($stats['persen_kehadiran'] !== null)
                    <h3 class="text-2xl font-bold text-emerald-400">{{ $stats['persen_kehadiran'] }}%</h3>
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400 mt-2">
                        Data {{ \Carbon\Carbon::parse($stats['tanggal_kehadiran'])->translatedFormat('j F Y') }}
                    </span>
                @else
                    <h3 class="text-2xl font-bold text-slate-400">-</h3>
                    <span class="text-xs font-medium text-slate-400 mt-2">Belum ada data presensi</span>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="text-sm text-slate-400 font-medium">Ketuntasan Legger</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400">
                    <i class="fa-solid fa-square-poll-vertical text-lg"></i>
                </div>
            </div>
            <div>
                @if($stats['persen_tuntas'] !== null)
                    <h3 class="text-2xl font-bold text-slate-100">{{ $stats['persen_tuntas'] }}%</h3>
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400 mt-2">
                        {{ $stats['tuntas_count'] }} / {{ $stats['nilai_count'] }} Tuntas
                    </span>
                @else
                    <h3 class="text-2xl font-bold text-slate-400">-</h3>
                    <span class="text-xs font-medium text-slate-400 mt-2">Belum ada data nilai</span>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="text-sm text-slate-400 font-medium">Kasus BK Aktif</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-500/10 text-rose-400">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-rose-400">{{ $stats['kasus_aktif'] }}</h3>
                <span class="inline-flex items-center gap-1 text-xs font-medium text-rose-400 mt-2">
                    <i class="fa-solid fa-circle-info"></i> {{ $stats['kasus_berat'] }} Kategori Mendesak
                </span>
            </div>
        </div>
    </div>

    <!-- Graphics & Summary Row -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">
        <div class="lg:col-span-7">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 h-full flex flex-col">
                <h5 class="text-lg font-bold mb-4 text-slate-100">Tren Kehadiran (Minggu Ini)</h5>
                @if($trendKehadiran->every(fn ($r) => $r['persen'] === null))
                    <div class="flex-1 flex items-center justify-center min-h-[200px] bg-slate-900/50 rounded-xl border border-slate-700/30">
                        <p class="text-slate-400 text-sm">Belum ada data presensi.</p>
                    </div>
                @else
                    <div id="chartTrenKehadiran"></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            registerChart(new ApexCharts(document.querySelector('#chartTrenKehadiran'), {
                                chart: { type: 'area', height: 250, toolbar: { show: false }, fontFamily: 'inherit' },
                                series: [{ name: 'Kehadiran', data: @json($trendKehadiran->pluck('persen')->map(fn ($v) => $v ?? 0)) }],
                                xaxis: {
                                    type: 'category',
                                    categories: @json($trendKehadiran->pluck('hari')),
                                    axisBorder: { show: false },
                                    axisTicks: { show: false },
                                },
                                yaxis: { min: 0, max: 100, labels: { formatter: (v) => v + '%' } },
                                colors: ['#10b981'],
                                stroke: { curve: 'straight', width: 2 },
                                fill: { type: 'gradient', gradient: { opacityFrom: 0.55, opacityTo: 0 } },
                                markers: { size: 0, hover: { size: 5 } },
                                dataLabels: { enabled: false },
                                grid: { xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } }, strokeDashArray: 4 },
                                tooltip: { y: { formatter: (v, opts) => {
                                    const adaData = @json($trendKehadiran->pluck('persen')->map(fn ($v) => $v !== null));
                                    return adaData[opts.dataPointIndex] ? v + '%' : 'Tidak ada data';
                                } } },
                            }));
                        });
                    </script>
                @endif
            </div>
        </div>
        <div class="lg:col-span-5">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 h-full flex flex-col">
                <h5 class="text-lg font-bold mb-4 text-slate-100">Distribusi Kategori Pelanggaran</h5>
                @if($distribusiPelanggaran->isEmpty())
                    <div class="flex-1 flex items-center justify-center min-h-[200px] bg-slate-900/50 rounded-xl border border-slate-700/30">
                        <p class="text-slate-400 text-sm">Belum ada data pelanggaran.</p>
                    </div>
                @else
                    <div id="chartDistribusiPelanggaran"></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            registerChart(new ApexCharts(document.querySelector('#chartDistribusiPelanggaran'), {
                                chart: { type: 'bar', height: 250, toolbar: { show: false }, fontFamily: 'inherit' },
                                series: [{ name: 'Jumlah', data: @json($distribusiPelanggaran->pluck('total')) }],
                                xaxis: { categories: @json($distribusiPelanggaran->pluck('kategori')) },
                                plotOptions: { bar: { borderRadius: 6, horizontal: true, barHeight: '55%' } },
                                colors: ['#f43f5e'],
                                dataLabels: { enabled: false },
                                grid: { strokeDashArray: 4 },
                            }));
                        });
                    </script>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- PANE: PANTAU AKADEMIK -->
<!-- ============================================ -->
<div id="pane-kepsek-akademik" class="pane-content hidden-pane fade-transition space-y-4">
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h4 class="font-bold m-0 text-slate-100 text-xl">Pantau Ketuntasan Akademik</h4>
            <p class="text-slate-400 text-sm m-0">Rekapitulasi nilai akhir seluruh rombel (KKM {{ \App\Http\Controllers\KepsekController::KKM }}).</p>
        </div>
        <form method="GET" action="{{ route('kepsek.dashboard') }}#akademik" class="flex items-center gap-2">
            <select name="jurusan" onchange="this.form.submit()" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                <option value="all" @selected($jurusanFilter === 'all')>Semua Jurusan</option>
                @foreach($jurusanList as $jurusan)
                    <option value="{{ $jurusan }}" @selected($jurusanFilter === $jurusan)>{{ $jurusan }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 shadow-sm overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-sm text-slate-100">
            <thead class="border-b border-slate-700 bg-slate-900">
                <tr>
                    <th class="px-5 py-3">Kelas</th>
                    <th class="px-5 py-3">Wali Kelas</th>
                    <th class="px-5 py-3 text-center">Jumlah Siswa</th>
                    <th class="px-5 py-3 text-center">Rata-rata Nilai Akhir</th>
                    <th class="px-5 py-3 text-center">Persentase Tuntas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($akademikPerKelas as $row)
                    <tr>
                        <td class="px-5 py-4 font-semibold">{{ $row['kelas']->nama_kelas }}</td>
                        <td class="px-5 py-4 text-slate-400">{{ $row['kelas']->waliKelas->nama ?? '-' }}</td>
                        <td class="px-5 py-4 text-center">{{ $row['kelas']->siswas_count }} Siswa</td>
                        <td class="px-5 py-4 text-center font-bold text-blue-400">{{ $row['rata_rata'] ?? '-' }}</td>
                        <td class="px-5 py-4 text-center">
                            @if($row['persen_tuntas'] !== null)
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $row['persen_tuntas'] >= 75 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">{{ $row['persen_tuntas'] }}% Tuntas</span>
                            @else
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold bg-slate-500/10 text-slate-400">Belum ada nilai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada data kelas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================ -->
<!-- PANE: PANTAU KESISWAAN -->
<!-- ============================================ -->
<div id="pane-kepsek-kesiswaan" class="pane-content hidden-pane fade-transition space-y-4">
    <div>
        <h4 class="font-bold text-slate-100 mb-1 text-xl">Pantauan Kesiswaan & Kedisiplinan</h4>
        <p class="text-slate-400 text-sm">Analisis kasus pelanggaran dan penanganan kesiswaan berdasarkan data tercatat.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 p-4">
            <span class="text-xs text-slate-400 font-medium">Kasus BK Aktif</span>
            <h3 class="text-xl font-bold text-slate-100 mt-1">{{ $kesiswaanSummary['kasus_bk_aktif'] }}</h3>
        </div>
        <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 p-4">
            <span class="text-xs text-slate-400 font-medium">Kasus Kategori Mendesak</span>
            <h3 class="text-xl font-bold text-rose-400 mt-1">{{ $kesiswaanSummary['kasus_bk_mendesak'] }}</h3>
        </div>
        <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 p-4">
            <span class="text-xs text-slate-400 font-medium">Panggilan Ortu Pending</span>
            <h3 class="text-xl font-bold text-amber-400 mt-1">{{ $kesiswaanSummary['panggilan_ortu_pending'] }}</h3>
        </div>
        <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 p-4">
            <span class="text-xs text-slate-400 font-medium">Pelanggaran Bulan Ini</span>
            <h3 class="text-xl font-bold text-slate-100 mt-1">{{ $kesiswaanSummary['pelanggaran_bulan_ini'] }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 p-4 md:p-5">
            <h6 class="font-bold text-slate-100 mb-3">Tren Pelanggaran Mingguan</h6>
            @if($trendPelanggaranMingguan->isEmpty())
                <div class="flex items-center justify-center min-h-[160px] bg-slate-900/50 rounded-xl border border-slate-700/30">
                    <p class="text-slate-400 text-sm">Belum ada data pelanggaran.</p>
                </div>
            @else
                <div id="chartTrenPelanggaranMingguan"></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        registerChart(new ApexCharts(document.querySelector('#chartTrenPelanggaranMingguan'), {
                            chart: { type: 'bar', height: 200, toolbar: { show: false }, fontFamily: 'inherit' },
                            series: [{ name: 'Pelanggaran', data: @json($trendPelanggaranMingguan->pluck('total')) }],
                            xaxis: { categories: @json($trendPelanggaranMingguan->pluck('minggu')->map(fn ($m) => substr($m, 2))) },
                            plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
                            colors: ['#f43f5e'],
                            dataLabels: { enabled: false },
                            grid: { strokeDashArray: 4 },
                        }));
                    });
                </script>
            @endif
        </div>
        <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 p-4 md:p-5">
            <h6 class="font-bold text-slate-100 mb-3">Leaderboard Poin Pelanggaran</h6>
            @if($leaderboardPelanggaran->isEmpty())
                <p class="text-slate-400 text-sm">Belum ada data pelanggaran.</p>
            @else
                <div class="flex flex-col gap-3">
                    @foreach($leaderboardPelanggaran as $row)
                        <div class="flex items-center justify-between rounded-lg bg-slate-900/50 px-3 py-2">
                            <div>
                                <p class="text-sm font-semibold text-slate-100 m-0">{{ $row->siswa->nama ?? 'Siswa Dihapus' }}</p>
                                <p class="text-xs text-slate-400 m-0">{{ $row->siswa->kelas->nama_kelas ?? '-' }} &middot; {{ $row->jumlah_kasus }} kasus</p>
                            </div>
                            <span class="rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-semibold text-rose-400">{{ $row->total_poin }} poin</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- PANE: LAPORAN EKSEKUTIF -->
<!-- ============================================ -->
<div id="pane-kepsek-laporan" class="pane-content hidden-pane fade-transition space-y-4">
    <!-- Announcement publishing board -->
    <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 p-4 md:p-5">
        <h5 class="font-bold text-slate-100 mb-1"><i class="fa-solid fa-bullhorn text-amber-400 mr-1"></i> Siaran Pengumuman Sekolah</h5>
        <p class="text-slate-400 text-sm mb-3">Siarkan pemberitahuan resmi dari Kepala Sekolah ke seluruh portal.</p>
        <form method="POST" action="{{ route('kepsek.pengumuman.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-2">
            @csrf
            <input type="text" name="judul" required placeholder="Judul Pengumuman..." class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 md:col-span-1">
            <select name="kategori" required class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                @forelse($kategoriPengumumanList as $kategori)
                    <option value="{{ $kategori }}">{{ $kategori }}</option>
                @empty
                    <option value="Umum">Umum</option>
                @endforelse
            </select>
            <input type="text" name="deskripsi" required placeholder="Isi detail pengumuman..." class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 md:col-span-1">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"><i class="fa-solid fa-paper-plane mr-1"></i> Siarkan</button>
        </form>
        @if($pengumumanTerbaru->isNotEmpty())
            <div class="mt-4 space-y-2">
                @foreach($pengumumanTerbaru as $p)
                    <div class="flex items-center justify-between rounded-lg bg-slate-900/50 px-3 py-2 text-sm">
                        <div>
                            <span class="font-semibold text-slate-100">{{ $p->judul }}</span>
                            <span class="text-slate-400"> &middot; {{ $p->kategori }} &middot; {{ $p->pembuat->nama ?? '-' }}</span>
                        </div>
                        <span class="text-xs text-slate-400">{{ $p->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div>
        <h4 class="font-bold text-slate-100 mb-1 text-xl">Laporan Eksekutif</h4>
        <p class="text-slate-400 text-sm mb-3">Rekapitulasi data akademik dan kedisiplinan berdasarkan seluruh data yang tercatat di sistem.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 p-4 md:p-5">
            <div class="flex items-center justify-between mb-3">
                <h6 class="font-bold text-slate-100 m-0">Rekap Presensi ({{ $laporanRecap['total_presensi'] }} data)</h6>
                <a href="{{ route('kepsek.laporan.export', 'kehadiran') }}" class="text-xs font-semibold text-blue-400 hover:text-blue-300"><i class="fa-solid fa-download mr-1"></i> Unduh CSV</a>
            </div>
            @php $statusLabel = ['H'=>'Hadir','I'=>'Izin','S'=>'Sakit','A'=>'Alpa']; @endphp
            <div class="flex flex-col gap-2">
                @forelse($laporanRecap['rekap_status_presensi'] as $status => $total)
                    <div class="flex justify-between text-sm text-slate-400">
                        <span>{{ $statusLabel[$status] ?? $status }}</span>
                        <span class="font-semibold">{{ $total }}</span>
                    </div>
                @empty
                    <p class="text-slate-400 text-sm">Belum ada data presensi.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 p-4 md:p-5">
            <div class="flex items-center justify-between mb-3">
                <h6 class="font-bold text-slate-100 m-0">Rekap Legger Nilai</h6>
                <a href="{{ route('kepsek.laporan.export', 'legger') }}" class="text-xs font-semibold text-blue-400 hover:text-blue-300"><i class="fa-solid fa-download mr-1"></i> Unduh CSV</a>
            </div>
            <div class="flex justify-between text-sm text-slate-400">
                <span>Total Nilai Tercatat</span>
                <span class="font-semibold">{{ $stats['nilai_count'] }}</span>
            </div>
            <div class="flex justify-between text-sm text-slate-400 mt-2">
                <span>Tuntas (KKM {{ \App\Http\Controllers\KepsekController::KKM }})</span>
                <span class="font-semibold text-emerald-400">{{ $stats['tuntas_count'] }}</span>
            </div>
        </div>

        <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 p-4 md:p-5 md:col-span-2">
            <div class="flex items-center justify-between mb-3">
                <h6 class="font-bold text-slate-100 m-0">Rekap Kasus Bimbingan Konseling ({{ $laporanRecap['total_kasus_bk'] }} kasus)</h6>
                <a href="{{ route('kepsek.laporan.export', 'bk') }}" class="text-xs font-semibold text-blue-400 hover:text-blue-300"><i class="fa-solid fa-download mr-1"></i> Unduh CSV</a>
            </div>
            @php $bkLabel = ['antrean'=>'Antrean','proses'=>'Proses','selesai'=>'Selesai']; @endphp
            <div class="grid grid-cols-3 gap-3">
                @forelse($laporanRecap['rekap_status_kasus_bk'] as $status => $total)
                    <div class="rounded-lg bg-slate-900/50 px-3 py-2 text-center">
                        <p class="text-xs text-slate-400 m-0">{{ $bkLabel[$status] ?? $status }}</p>
                        <p class="text-lg font-bold text-slate-100 m-0">{{ $total }}</p>
                    </div>
                @empty
                    <p class="text-slate-400 text-sm col-span-3">Belum ada kasus BK tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

</div>

@if($jurusanFilter !== 'all')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var link = document.querySelector('#sidebar-menu-list a[onclick*="pane-kepsek-akademik"]');
        showPane('pane-kepsek-akademik', link);
    });
</script>
@endif
@endsection
