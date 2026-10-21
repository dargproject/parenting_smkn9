<?php

namespace App\Services;

/**
 * Satu sumber aturan jenjang sanksi tatib (SP1-DO + peringatan harian), sesuai
 * tabel "Penanganan dan Sanksi Pelanggaran" sekolah. Dipakai di mana pun status
 * poin siswa perlu ditampilkan: form Catat Pelanggaran, Rekap Poin, dan dashboard
 * Wali Kelas -- supaya tidak ada ambang batas yang berbeda-beda antar halaman.
 */
class SanksiService
{
    /**
     * Status berdasarkan akumulasi poin pelanggaran dalam satu tahun ajaran
     * (poin pelanggaran dikurangi poin yang sudah dihapus lewat kegiatan positif).
     */
    public function statusSemester(int $poin): array
    {
        return match (true) {
            $poin >= 50 => [
                'kode' => 'DO',
                'label' => 'Dikembalikan ke Orang Tua',
                'tone' => 'rose',
                'tindakan' => [
                    'Rapat dewan guru memutuskan murid dikembalikan ke orang tua',
                    'Diberi surat rekomendasi pindah sekolah',
                ],
            ],
            $poin >= 40 => [
                'kode' => 'SP3',
                'label' => 'Surat Peringatan 3',
                'tone' => 'rose',
                'tindakan' => [
                    'Sidang kesiswaan: Kepsek, Waka Kesiswaan, Wali Kelas, BK, dan orang tua',
                    'Skorsing 1 minggu dan wajib lapor setiap hari',
                    'Surat pernyataan bermaterai dari orang tua bahwa ini peringatan terakhir',
                    'Pendampingan khusus oleh BK 2x seminggu',
                ],
            ],
            $poin >= 24 => [
                'kode' => 'SP2',
                'label' => 'Surat Peringatan 2',
                'tone' => 'amber',
                'tindakan' => [
                    'Pemanggilan orang tua ke sekolah, wajib hadir',
                    'Pembinaan intensif oleh tim BK dan Kesiswaan',
                    'Kontrak perilaku: murid, orang tua, dan pihak sekolah (tanda tangan)',
                    'Skorsing ringan 1-3 hari untuk evaluasi diri',
                    'Tugas sosial/pengabdian di sekolah 1-2 minggu',
                ],
            ],
            $poin >= 10 => [
                'kode' => 'SP1',
                'label' => 'Surat Peringatan 1',
                'tone' => 'amber',
                'tindakan' => [
                    'Pemanggilan dan pembinaan oleh wali kelas + BK',
                    'Penandatanganan surat pernyataan tidak mengulangi',
                    'Pemberitahuan tertulis ke orang tua/wali via surat',
                ],
            ],
            default => [
                'kode' => null,
                'label' => 'Aman',
                'tone' => 'emerald',
                'tindakan' => [],
            ],
        };
    }

    /**
     * Status berdasarkan akumulasi poin pelanggaran pada HARI yang sama (reset
     * tiap hari) -- beda dari status semester, untuk teguran ringan di tempat.
     */
    public function statusHarian(int $poinHarian): ?array
    {
        $tindakanRingan = [
            'Mengerjakan tugas kebersihan sekolah, atau',
            'Membantu tim tatib pukul 06.45, atau',
            'Sanksi tindakan langsung yang bersifat mendidik lainnya',
        ];

        return match (true) {
            $poinHarian >= 6 => ['kode' => 'P2', 'label' => 'Peringatan ke-2 (Harian)', 'tone' => 'amber', 'tindakan' => $tindakanRingan],
            $poinHarian >= 3 => ['kode' => 'P1', 'label' => 'Peringatan ke-1 (Harian)', 'tone' => 'amber', 'tindakan' => $tindakanRingan],
            default => null,
        };
    }
}
