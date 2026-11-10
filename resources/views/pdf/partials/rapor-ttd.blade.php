<div class="blok-utuh ttd-wrap">
    <table class="ttd-dua">
        <tr>
            <td>
                <p class="ttd-peran">&nbsp;</p>
                <p class="ttd-peran">Orang Tua / Wali Murid,</p>
                <div class="ttd-spasi"></div>
                <p class="ttd-nama">{{ $namaWali ?: '...........................................' }}</p>
            </td>
            <td>
                <p class="ttd-tanggal">Malang, {{ \Illuminate\Support\Carbon::parse(setting('tanggal_cetak_rapor') ?: now())->translatedFormat('d F Y') }}</p>
                <p class="ttd-peran">Wali Kelas,</p>
                <div class="ttd-spasi"></div>
                <p class="ttd-nama">{{ $waliKelasNama ?: '...........................................' }}</p>
                @if($waliKelasNip)
                    <p class="ttd-nip">NIP. {{ $waliKelasNip }}</p>
                @endif
            </td>
        </tr>
    </table>

    <div class="ttd-kepsek">
        <p class="ttd-peran">Mengetahui,</p>
        <p class="ttd-peran">Kepala Sekolah,</p>
        <div class="ttd-spasi"></div>
        <p class="ttd-nama">{{ $kepsekNama ?: '...........................................' }}</p>
        @if($kepsekNip)
            <p class="ttd-nip">NIP. {{ $kepsekNip }}</p>
        @endif
    </div>
</div>
