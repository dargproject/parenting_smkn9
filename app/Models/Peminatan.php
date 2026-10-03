<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminatan extends Model
{
    protected $table = 'peminatans';

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'tanggal',
        'jawaban',
        'pilihan1',
        'pilihan2',
        'pilihan3',
        'hasil',
        'catatan',
        'tampilkan_ke_ortu',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jawaban' => 'array',
        'tampilkan_ke_ortu' => 'boolean',
    ];

    public const SECTIONS = [
        'Linguistik',
        'Logis-Matematik',
        'Visual-Spasial',
        'Musikal',
        'Interpersonal',
        'Intrapersonal',
        'Kinestetik',
        'Naturalis',
    ];

    public const QUESTION_GROUPS = [
        'Linguistik' => [
            'LG01' => 'Saya senang bermain dengan kata-kata, menikmati puisi, dan suka mendengarkan cerita.',
            'LG02' => 'Saya membaca apa saja: buku, majalah, surat kabar, bahkan label produk.',
            'LG03' => 'Saya mudah dan percaya diri mengekspresikan diri secara lisan maupun tulisan.',
            'LG04' => 'Saya suka membumbui percakapan dengan hal-hal menarik yang baru saja saya baca atau dengar.',
            'LG05' => 'Saya suka mengerjakan teka-teki silang, bermain scrabble, atau puzzle dan dapat mengeja dengan sangat baik.',
        ],
        'Logis-Matematik' => [
            'LM01' => 'Saya senang bekerja dengan angka dan dapat melakukan perhitungan mental (mencongak).',
            'LM02' => 'Saya tertarik dengan kemajuan teknologi dan gemar melakukan percobaan untuk melihat cara kerja sesuatu.',
            'LM03' => 'Saya merasa mudah melakukan perencanaan keuangan dan menetapkan target dalam bentuk angka.',
            'LM04' => 'Saya senang menyiapkan jadwal perjalanan secara terperinci dan menetapkan daftar kerja (to-do-list).',
            'LM05' => 'Saya senang dengan permainan yang membutuhkan kemampuan berpikir logis dan statistis seperti catur.',
        ],
        'Visual-Spasial' => [
            'VS01' => 'Saya menyukai seni, menikmati lukisan dan patung, serta memiliki citra rasa yang baik akan warna.',
            'VS02' => 'Saya cenderung menyukai pencatatan secara visual dengan menggunakan kamera atau handycam.',
            'VS03' => 'Saya bisa menulis dengan cepat saat mencatat atau berpikir, dan dapat menggambar dengan cukup baik.',
            'VS04' => 'Saya merasa mudah membaca peta atau melakukan navigasi, serta memiliki kemampuan mengerti arah yang baik.',
            'VS05' => 'Saya menikmati permainan seperti puzzle.',
        ],
        'Musikal' => [
            'MU01' => 'Saya dapat memainkan alat musik.',
            'MU02' => 'Saya dapat menyanyi sesuai dengan tinggi rendahnya kunci nada.',
            'MU03' => 'Saya biasanya dapat mengingat sebuah irama hanya dengan mendengarkan beberapa kali saja.',
            'MU04' => 'Saya sering mendengarkan musik dan bahkan kadang menghadiri konser musik.',
            'MU05' => 'Saya mengikuti irama musik dengan baik dan tanpa sadar mengetuk-ngetukkan jari mengikuti irama lagu.',
        ],
        'Interpersonal' => [
            'IP01' => 'Saya senang bekerja sama dengan orang lain dalam suatu kelompok atau komite.',
            'IP02' => 'Saya lebih suka belajar kelompok daripada belajar sendiri.',
            'IP03' => 'Orang sering kali datang kepada saya untuk meminta nasihat.',
            'IP04' => 'Saya adalah orang yang penuh simpati.',
            'IP05' => 'Saya lebih suka team sport seperti basket, softball, sepak bola daripada olahraga individual.',
        ],
        'Intrapersonal' => [
            'IT01' => 'Saya memiliki buku harian untuk mencatat pikiran saya yang sangat dalam dan pribadi.',
            'IT02' => 'Saya sering menyendiri untuk memikirkan dan memecahkan masalah sendiri.',
            'IT03' => 'Saya menetapkan tujuan saya sendiri dan tidak terpengaruh orang lain.',
            'IT04' => 'Saya adalah seorang pemikir independen (mandiri) dan memutuskan sendiri keputusan saya.',
            'IT05' => 'Saya mempunyai hobi atau kesenangan yang bersifat pribadi yang tidak banyak saya bagikan kepada orang lain.',
        ],
        'Kinestetik' => [
            'KI01' => 'Saya gemar berolahraga atau melakukan kegiatan fisik.',
            'KI02' => 'Saya cakap dalam melakukan sesuatu seorang diri.',
            'KI03' => 'Saya senang memikirkan persoalan sambil aktif dalam kegiatan fisik seperti berjalan atau lari.',
            'KI04' => 'Saya tidak keberatan jika diminta untuk menari.',
            'KI05' => 'Saya senang dengan permainan yang sangat menantang dan mengerikan secara fisik seperti jet coaster.',
        ],
        'Naturalis' => [
            'NA01' => 'Saya senang memelihara atau menyukai hewan.',
            'NA02' => 'Saya dapat mengenali dan membedakan nama berbagai jenis pohon, bunga, dan tanaman.',
            'NA03' => 'Saya tertarik dan memiliki pengetahuan yang cukup mengenai bagaimana tubuh bekerja dan kesehatan.',
            'NA04' => 'Saya tahu jalur atau jalan setapak, sarang burung dan hewan liar, serta dapat membaca cuaca.',
            'NA05' => 'Saya dapat membayangkan diri saya sebagai seorang petani atau saya suka memancing.',
        ],
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function questionGroups(): array
    {
        $jawaban = $this->jawaban ?? [];

        $groups = [];

        foreach (self::SECTIONS as $section) {
            $checked = collect($jawaban[$section] ?? []);

            $items = collect(self::QUESTION_GROUPS[$section] ?? [])
                ->map(fn (string $pertanyaan, string $kode) => [
                    'kode' => $kode,
                    'pertanyaan' => $pertanyaan,
                    'checked' => $checked->contains($kode),
                ])
                ->values()
                ->all();

            $groups[] = [
                'section' => $section,
                'items' => $items,
                'checked_count' => $checked->count(),
                'total' => count($items),
            ];
        }

        return $groups;
    }

    public static function dominantIntelligencesFrom(array $jawaban): array
    {
        $counts = collect(self::SECTIONS)
            ->mapWithKeys(fn (string $section) => [$section => count($jawaban[$section] ?? [])]);

        $top = $counts
            ->sortDesc()
            ->filter(fn ($count) => $count > 0)
            ->keys()
            ->take(3)
            ->all();

        return array_pad($top, 3, '');
    }

    public function dominantIntelligences(): array
    {
        return self::dominantIntelligencesFrom($this->jawaban ?? []);
    }
}
