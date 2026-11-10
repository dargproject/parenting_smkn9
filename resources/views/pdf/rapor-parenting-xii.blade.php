<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rapor Parenting - {{ $siswa->nama }}</title>
@include('pdf.partials.rapor-style2')
</head>
<body>
    @include('pdf.partials.rapor-header2')

    <div class="section-title">II. Perkembangan Non-Akademik &amp; Karakter</div>
    <table class="table-rapor">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 32%;">Aspek Karakter &amp; Kegiatan</th>
                <th style="width: 63%;">Catatan Perkembangan</th>
            </tr>
        </thead>
        <tbody>
            {{-- <tr>
                <td class="text-center font-bold">1</td>
                <td class="font-bold">Kegiatan Ekstrakurikuler / Organisasi</td>
                <td>
                    @forelse($nilaiEkskul as $ne)
                        <div class="card-item">
                            <strong>{{ $ne->ekstrakurikuler->nama ?? '-' }}</strong> &mdash;
                            <span class="badge-mini badge-info">Nilai {{ $ne->nilai }}</span>
                            @if($ne->catatan) <span class="text-muted italic">({{ $ne->catatan }})</span> @endif
                        </div>
                    @empty
                        <span class="text-muted italic">Tidak mengikuti ekstrakurikuler yang tercatat.</span>
                    @endforelse
                </td>
            </tr> --}}
            <tr>
                <td class="text-center font-bold">1</td>
                <td class="font-bold">Prestasi Non-Akademik</td>
                <td>
                    @forelse($prestasiNonAkademik as $prestasi)
                        <div class="card-item bg-emerald-light">
                            <strong class="text-emerald">{{ $prestasi->nama_prestasi }}</strong>
                            <span class="text-muted">({{ $prestasi->tingkat }})</span> &mdash;
                            {{ $prestasi->tanggal->translatedFormat('d M Y') }}
                            @if($prestasi->peringkat) &bull; <strong>{{ $prestasi->peringkat }}</strong> @endif
                            @if($prestasi->penyelenggara) &bull; {{ $prestasi->penyelenggara }} @endif
                        </div>
                    @empty
                        <span class="text-muted italic">Belum ada prestasi non-akademik yang tercatat.</span>
                    @endforelse
                </td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">III. Praktik Kerja Lapangan (PKL)</div>
    <div class="no-break">
        @if($penempatanPkl)
            <table class="table-identitas">
                <tr>
                    <td class="label">Nama IDUKA</td><td class="colon">:</td><td class="value">{{ $penempatanPkl->nama_iduka }}</td>
                </tr>
                @if($penempatanPkl->alamat)
                    <tr>
                        <td class="label">Alamat</td><td class="colon">:</td><td class="value">{{ $penempatanPkl->alamat }}</td>
                    </tr>
                @endif
                @if($penempatanPkl->nama_cp_industri || $penempatanPkl->no_cp_industri)
                    <tr>
                        <td class="label">CP Industri</td><td class="colon">:</td>
                        <td class="value">{{ $penempatanPkl->nama_cp_industri }}{{ $penempatanPkl->no_cp_industri ? ' ('.$penempatanPkl->no_cp_industri.')' : '' }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="label">Pembimbing Sekolah</td><td class="colon">:</td><td class="value">{{ $penempatanPkl->guruPembimbing->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Periode PKL</td><td class="colon">:</td>
                    <td class="value">
                        @if($penempatanPkl->tanggal_mulai || $penempatanPkl->tanggal_selesai)
                            {{ $penempatanPkl->tanggal_mulai?->translatedFormat('d M Y') ?? '?' }} &ndash; {{ $penempatanPkl->tanggal_selesai?->translatedFormat('d M Y') ?? '?' }}
                        @else
                            <span class="text-muted italic">Belum ditentukan oleh pembimbing</span>
                        @endif
                    </td>
                </tr>
            </table>

            @if($penempatanPkl->predikat_etika || $penempatanPkl->catatan_etika || $penempatanPkl->predikat_kemandirian || $penempatanPkl->catatan_kemandirian || $penempatanPkl->predikat_administrasi || $penempatanPkl->catatan_administrasi || $penempatanPkl->predikat_progres || $penempatanPkl->catatan_progres)
                <table class="table-rapor mt-1">
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">No</th>
                            <th style="width: 24%;">Aspek Perkembangan</th>
                            <th style="width: 13%;">Predikat</th>
                            <th style="width: 58%;">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center font-bold">1</td>
                            <td class="font-bold">Etika, Kedisiplinan &amp; Sikap</td>
                            <td class="text-center">{{ $penempatanPkl->predikat_etika ?? '-' }}</td>
                            <td>{{ $penempatanPkl->catatan_etika ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-center font-bold">2</td>
                            <td class="font-bold">Kemandirian &amp; Problem Solving</td>
                            <td class="text-center">{{ $penempatanPkl->predikat_kemandirian ?? '-' }}</td>
                            <td>{{ $penempatanPkl->catatan_kemandirian ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-center font-bold">3</td>
                            <td class="font-bold">Administrasi &amp; Tanggung Jawab (Jurnal PKL)</td>
                            <td class="text-center">{{ $penempatanPkl->predikat_administrasi ?? '-' }}</td>
                            <td>{{ $penempatanPkl->catatan_administrasi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-center font-bold">4</td>
                            <td class="font-bold">Progres Laporan PKL</td>
                            <td class="text-center">{{ $penempatanPkl->predikat_progres ?? '-' }}</td>
                            <td>{{ $penempatanPkl->catatan_progres ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            @if($penempatanPkl->catatan)
                <div class="card-item mt-1"><strong>Catatan tambahan:</strong> {{ $penempatanPkl->catatan }}</div>
            @endif
        @else
            <span class="text-muted italic">Belum ada data penempatan PKL untuk siswa ini.</span>
        @endif
    </div>

    @include('pdf.partials.rapor-ttd2')
</body>
</html>
