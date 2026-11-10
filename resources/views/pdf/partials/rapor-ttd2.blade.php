<div class="ttd-wrapper">
    <!-- Baris Atas: Orang Tua (Kiri) & Wali Kelas (Kanan) -->
    <table class="ttd-top-table">
        <tr>
            <td>
                <p class="ttd-tanggal">&nbsp;</p>
                <p class="ttd-peran">Orang Tua / Wali Murid,</p>
                <div class="ttd-space"></div>
                <p class="ttd-nama">{{ $namaWali ?: '...........................................' }}</p>
            </td>
            <td>
                <p class="ttd-tanggal">Malang, {{ \Illuminate\Support\Carbon::parse(setting('tanggal_cetak_rapor') ?: now())->translatedFormat('d F Y') }}</p>
                <p class="ttd-peran">Wali Kelas,</p>
                <div class="ttd-space"></div>
                <p class="ttd-nama">{{ $waliKelasNama ?: '...........................................' }}</p>
                @if(isset($waliKelasNip) && $waliKelasNip)
                    <p class="ttd-nip">NIP. {{ $waliKelasNip }}</p>
                @endif
            </td>
        </tr>
    </table>

    <!-- Baris Bawah: Kepala Sekolah (Tengah) -->
    <div class="ttd-bottom-box">
        <p class="ttd-peran">Mengetahui,</p>
        <p class="ttd-peran">Kepala Sekolah,</p>
        <div class="ttd-space"></div>
        <p class="ttd-nama">{{ $kepsekNama ?: '...........................................' }}</p>
        @if(isset($kepsekNip) && $kepsekNip)
            <p class="ttd-nip">NIP. {{ $kepsekNip }}</p>
        @endif
    </div>
</div>
