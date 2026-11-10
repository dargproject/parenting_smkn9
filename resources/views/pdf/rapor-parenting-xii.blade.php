<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rapor Parenting - {{ $siswa->nama }}</title>
@include('pdf.partials.rapor-style')
</head>
<body>
    @include('pdf.partials.rapor-header')

    <h3>II. Perkembangan Non-Akademik &amp; Karakter (Sekolah &amp; Ekstrakurikuler)</h3>
    <table class="isi">
        <tr>
            <th class="no">No</th>
            <th class="aspek">Aspek Karakter &amp; Kegiatan</th>
            <th>Catatan Perkembangan</th>
        </tr>
        {{-- <tr>
            <td class="no">1</td>
            <td class="aspek">Kegiatan Ekstrakurikuler / Organisasi</td>
            <td>
                @forelse($nilaiEkskul as $ne)
                    {{ $ne->ekstrakurikuler->nama ?? '-' }} &mdash; Nilai {{ $ne->nilai }}@if($ne->catatan) ({{ $ne->catatan }})@endif<br>
                @empty
                    <span class="kecil">Tidak mengikuti ekstrakurikuler yang tercatat.</span>
                @endforelse
            </td>
        </tr> --}}
        <tr>
            <td class="no">1</td>
            <td class="aspek">Prestasi Non-Akademik</td>
            <td>
                @forelse($prestasiNonAkademik as $prestasi)
                    {{ $prestasi->nama_prestasi }} ({{ $prestasi->tingkat }}) &mdash; {{ $prestasi->tanggal->translatedFormat('d M Y') }}
                    @if($prestasi->peringkat) &middot; {{ $prestasi->peringkat }} @endif
                    @if($prestasi->penyelenggara) &middot; {{ $prestasi->penyelenggara }} @endif
                    <br>
                @empty
                    <span class="kecil">Belum ada prestasi non-akademik yang tercatat.</span>
                @endforelse
            </td>
        </tr>
    </table>

    <div class="blok-utuh">
        <h3>III. Praktik Kerja Lapangan (PKL)</h3>
        @if($penempatanPkl)
            <table class="identitas">
                <tr><td class="label">Nama IDUKA</td><td>: {{ $penempatanPkl->nama_iduka }}</td></tr>
                @if($penempatanPkl->alamat)
                    <tr><td class="label">Alamat</td><td>: {{ $penempatanPkl->alamat }}</td></tr>
                @endif
                @if($penempatanPkl->nama_cp_industri || $penempatanPkl->no_cp_industri)
                    <tr><td class="label">CP Industri</td><td>: {{ $penempatanPkl->nama_cp_industri }}{{ $penempatanPkl->no_cp_industri ? ' ('.$penempatanPkl->no_cp_industri.')' : '' }}</td></tr>
                @endif
                <tr><td class="label">Pembimbing Sekolah</td><td>: {{ $penempatanPkl->guruPembimbing->nama ?? '-' }}</td></tr>
                <tr>
                    <td class="label">Periode PKL</td>
                    <td>:
                        @if($penempatanPkl->tanggal_mulai || $penempatanPkl->tanggal_selesai)
                            {{ $penempatanPkl->tanggal_mulai?->translatedFormat('d M Y') ?? '?' }} &ndash; {{ $penempatanPkl->tanggal_selesai?->translatedFormat('d M Y') ?? '?' }}
                        @else
                            Belum ditentukan oleh pembimbing
                        @endif
                    </td>
                </tr>
            </table>

            @if($penempatanPkl->predikat_etika || $penempatanPkl->catatan_etika || $penempatanPkl->predikat_kemandirian || $penempatanPkl->catatan_kemandirian || $penempatanPkl->predikat_administrasi || $penempatanPkl->catatan_administrasi || $penempatanPkl->predikat_progres || $penempatanPkl->catatan_progres)
                <table class="isi" style="margin-top: 6px;">
                        <th class="no">No</th>
                        <th style="width: 200px;">Aspek Perkembangan</th>
                        <th style="width: 80px;">Predikat</th>
                        <th>Catatan</th>
                    </tr>
                    <tr>
                        <td class="no">1</td>
                        <td>Etika, Kedisiplinan &amp; Sikap</td>
                        <td>{{ $penempatanPkl->predikat_etika ?? '-' }}</td>
                        <td>{{ $penempatanPkl->catatan_etika ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="no">2</td>
                        <td>Kemandirian &amp; Problem Solving</td>
                        <td>{{ $penempatanPkl->predikat_kemandirian ?? '-' }}</td>
                        <td>{{ $penempatanPkl->catatan_kemandirian ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="no">3</td>
                        <td>Administrasi &amp; Tanggung Jawab (Jurnal PKL)</td>
                        <td>{{ $penempatanPkl->predikat_administrasi ?? '-' }}</td>
                        <td>{{ $penempatanPkl->catatan_administrasi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="no">4</td>
                        <td>Progres Laporan PKL</td>
                        <td>{{ $penempatanPkl->predikat_progres ?? '-' }}</td>
                        <td>{{ $penempatanPkl->catatan_progres ?? '-' }}</td>
                    </tr>
                </table>
            @endif

            @if($penempatanPkl->catatan)
                <p class="kecil" style="margin-top: 4px;"><strong>Catatan tambahan:</strong> {{ $penempatanPkl->catatan }}</p>
            @endif
        @else
            <p class="kecil">Belum ada data penempatan PKL untuk siswa ini.</p>
        @endif
    </div>

    {{-- <div class="blok-utuh">
        <h3>IV. Lembar Pantauan Parenting (diisi oleh Wali Murid)</h3>
        <p class="kecil">Bapak/Ibu Wali Murid dimohon memberikan checklist (&radic;) dan catatan ringkas kondisi anak di rumah:</p>
        <table class="isi">
            <tr>
                <th>No</th>
                <th style="width: 180px;">Kebiasaan / Perilaku di Rumah</th>
                <th style="width: 50px;">Selalu</th>
                <th style="width: 50px;">Kadang</th>
                <th style="width: 50px;">Belum</th>
                <th>Catatan Orang Tua</th>
            </tr>
            @foreach(['Kedisiplinan belajar & latihan mandiri', 'Pembatasan & pemanfaatan gadget / media sosial', 'Komunikasi terbuka dengan keluarga', 'Keterlibatan dalam tugas/pekerjaan rumah'] as $i => $label)
                <tr>
                    <td class="no">{{ $i + 1 }}</td>
                    <td>{{ $label }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="height: 26px;">&nbsp;</td>
                </tr>
            @endforeach
        </table>
    </div> --}}

    {{-- <div class="blok-utuh">
        <h3>V. Catatan Kolaborasi &amp; Rencana Tindak Lanjut</h3>
        <p><strong>Tanggapan / Harapan Orang Tua:</strong></p>
        <div class="catatan-blok">&nbsp;</div>
    </div> --}}

    @include('pdf.partials.rapor-ttd')
</body>
</html>
