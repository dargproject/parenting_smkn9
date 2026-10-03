<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dcm extends Model
{
    protected $table = 'dcms';

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'tanggal',
        'jawaban',
        'masalah_teridentifikasi',
        'kesimpulan',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'masalah_teridentifikasi' => 'array',
        'jawaban' => 'array',
    ];

    public const SECTIONS = [
        'A' => 'Masalah Kesehatan',
        'B' => 'Masalah Ekonomi',
        'C' => 'Masalah Keluarga',
        'D' => 'Masalah Agama dan Moral',
        'E' => 'Masalah Pribadi',
        'F' => 'Masalah Hubungan Sosial dan Berorganisasi',
        'G' => 'Masalah Hobi dan Penggunaan Waktu Luang',
        'H' => 'Masalah Penyesuaian terhadap Sekolah',
        'I' => 'Masalah Penyesuaian terhadap Kurikulum',
        'J' => 'Masalah Masa Depan yang Berhubungan dengan Jabatan',
        'K' => 'Masalah Kebiasaan Belajar',
        'L' => 'Masalah Muda-Mudi dan Asmara',
    ];

    public const QUESTION_GROUPS = [
        'A' => [
            'A01' => 'Sering sakit ketika SD',
            'A02' => 'Sering sakit sekarang',
            'A03' => 'Jantung sering berdebar-debar',
            'A04' => 'Sering keluar keringat dingin',
            'A05' => 'Kesehatan saya sering terganggu',
            'A06' => 'Pernah dioperasi',
            'A07' => 'Merasa terlalu gemuk',
            'A08' => 'Merasa terlalu kurus',
            'A09' => 'Selalu kurang nafsu makan',
            'A10' => 'Saya merasa kurang bahagia karena cacat',
            'A11' => 'Sering kurang atau tidak dapat tidur',
            'A12' => 'Merasa lelah dan tidak bersemangat',
            'A13' => 'Makanan saya kurang memenuhi syarat kesehatan',
            'A14' => 'Sering merasa ngantuk',
            'A15' => 'Penglihatan saya kurang (sering kabur)',
            'A16' => 'Saya sering pusing/pening',
            'A17' => 'Pendengaran saya kurang baik',
            'A18' => 'Saya menderita gagap (sulit bicara)',
            'A19' => 'Kurang hawa segar',
            'A20' => 'Sering gemetar dan keluar keringat',
            'A21' => 'Mudah terkejut dan gugup',
            'A22' => 'Sering pingsan',
            'A23' => 'Tekanan darah terlalu rendah',
            'A24' => 'Tekanan darah terlalu tinggi',
            'A25' => 'Mempunyai penyakit menahun (parah)',
            'A26' => 'Mudah merasa lelah',
            'A27' => 'Memiliki gangguan pencernaan',
            'A28' => 'Sulit mengatur pola makan',
            'A29' => 'Paru-paru sering terasa nyeri',
            'A30' => 'Pernah opname atau rawat inap di rumah sakit',
            'A31' => 'Sering periksa ke dokter',
            'A32' => 'Mempunyai penyakit menular',
            'A33' => 'Merasa terlalu kurus/gemuk',
            'A34' => 'Merasa nafsu makan kurang',
            'A35' => 'Mudah gelisah',
            'A36' => 'Terkena penyakit kulit',
        ],
        'B' => [
            'B01' => 'Uang saku saya tidak mencukupi',
            'B02' => 'Tidak mampu membeli buku penunjang belajar',
            'B03' => 'Terpaksa sambil bekerja karena ekonomi tidak cukup',
            'B04' => 'Tidak tahu bagaimana caranya menambah biaya sekolah',
            'B05' => 'Saya sering meminjam uang',
            'B06' => 'Penerangan di rumah kurang memadai',
            'B07' => 'Sering berjalan kaki ke sekolah padahal rumah jauh',
            'B08' => 'Pekerjaan orang tua tidak tetap',
            'B09' => 'Biaya sekolah terlalu tinggi',
            'B10' => 'Terlalu banyak saudara yang harus dibiayai',
            'B11' => 'Saya tidak pernah mendapat uang saku',
            'B12' => 'Ibu/saudara ikut mencari penghasilan tambahan',
            'B13' => 'Terpaksa sering menunggak membayar SPP',
            'B14' => 'Terpaksa tidak melanjutkan ke perguruan tinggi setelah tamat sekolah',
            'B15' => 'Ayah dan ibu tidak hidup bersama',
            'B16' => 'Keluarga saya hidup berantakan',
            'B17' => 'Saya tidak puas dengan keadaan saya',
            'B18' => 'Saya ikut orang lain karena orang tua saya tidak mampu',
            'B19' => 'Orang tua mampu dan saya ingin segala keinginan dicukupi',
            'B20' => 'Kekurangan uang untuk membeli peralatan sekolah yang baik',
            'B21' => 'Tidak mempunyai seragam sekolah yang baik',
            'B22' => 'Sering dibelikan baju oleh orang tua',
            'B23' => 'Penghasilan orang tua tidak mencukupi untuk kebutuhan sehari-hari',
            'B24' => 'Selalu menyisihkan uang saku untuk ditabung',
        ],
        'C' => [
            'C01' => 'Saya adalah anak tunggal',
            'C02' => 'Saya adalah anak sulung',
            'C03' => 'Saya adalah anak bungsu',
            'C04' => 'Saya adalah tidak ber-ayah',
            'C05' => 'Saya adalah tidak ber-ibu',
            'C06' => 'Saya selalu dimanja orang tua/saudara',
            'C07' => 'Tidak hidup bersama orang tua',
            'C08' => 'Selalu bertengkar dengan kakak-adik',
            'C09' => 'Ayah-ibu pulang kerja terlalu petang',
            'C10' => 'Di rumah terlalu sibuk membantu orang tua',
            'C11' => 'Pertentangan ayah dan ibu mengganggu pikiran saya',
            'C12' => 'Mata pencaharian orang tua mengganggu pikiran saya',
            'C13' => 'Orang tua kurang memperhatikan saya',
            'C14' => 'Orang tua mencampuri urusan saya',
            'C15' => 'Sukar menyesuaikan diri dengan ayah',
            'C16' => 'Sukar menyesuaikan diri dengan ibu',
            'C17' => 'Di rumah saya merasa kurang senang',
            'C18' => 'Kehidupan di rumah kurang teratur',
            'C19' => 'Keluarga kami kurang tolong menolong',
            'C20' => 'Saya tidak ingin orang tua terlalu mengekang',
            'C21' => 'Pikiran orang tua terlalu kolot',
            'C22' => 'Orang tua sering bertengkar',
            'C23' => 'Sering beda pendapat dengan saudara ipar',
            'C24' => 'Tidak pernah merasa bahagia bersama keluarga',
            'C25' => 'Memiliki Saudara',
            'C26' => 'Sering ditinggal pergi orang tua',
            'C27' => 'Ada anggota keluarga yang dibenci',
            'C28' => 'Di rumah kurang disenangi',
            'C29' => 'Orang tua kurang perhatian',
            'C30' => 'Tidak mempunyai kamar sendiri',
        ],
        'D' => [
            'D01' => 'Tidak dapat secara bersungguh-sungguh menerima pelajaran agama',
            'D02' => 'Masih meragukan adanya Tuhan',
            'D03' => 'Sering timbul keinginan berganti agama',
            'D04' => 'Malas bersembahyang',
            'D05' => 'Tidak bersungguh-sungguh mengerjakan ibadah',
            'D06' => 'Kurang merasakan manfaat agama',
            'D07' => 'Sering berdusta',
            'D08' => 'Sering mengingkari janji',
            'D09' => 'Sering tidak mengakui kesalahan',
            'D10' => 'Sering iri hati',
            'D11' => 'Ucapan dan perbuatan sering tidak sesuai',
            'D12' => 'Sering mengambil barang orang lain',
            'D13' => 'Sering mempermainkan orang lain',
            'D14' => 'Pernah melanggar kesusilaan',
            'D15' => 'Kurang dapat bertoleransi dengan pemeluk agama lain',
            'D16' => 'Mudah merasa iba terhadap penderitaan orang lain',
            'D17' => 'Kurang ada tenggang rasa dengan orang lain',
            'D18' => 'Sering melupakan milik orang lain yang dipinjam',
            'D19' => 'Merasa hormat dengan orang yang lebih tua',
            'D20' => 'Merasa hormat dengan wanita',
            'D21' => 'Membenci teman yang mempunyai kelebihan',
            'D22' => 'Ada perasaan senang menceritakan hal yang berbau porno',
            'D23' => 'Sangat segan bergaul dengan orang ugal-ugalan',
            'D24' => 'Kurang senang dengan wanita atau pria yang pendiam',
            'D25' => 'Merasa jauh dengan Tuhan',
            'D26' => 'Suka membicarakan orang lain',
            'D27' => 'Suka menilai orang lain',
            'D28' => 'Merasa diabaikan teman/tidak disukai teman',
            'D29' => 'Merasa diancam teman',
            'D30' => 'Merasa ingin menang sendiri bila berteman',
            'D31' => 'Merasa takut pada guru di kelas',
            'D32' => 'Mudah tersinggung',
            'D33' => 'Mudah marah',
            'D34' => 'Rajin sholat',
            'D35' => 'Rajin mengaji',
            'D36' => 'Sering mengambil barang milik orang lain',
            'D37' => 'Sering ditegur orang lain karena tidak sopan',
            'D38' => 'Sering menjauhi orang lain',
            'D39' => 'Sering merasa minder',
        ],
        'E' => [
            'E01' => 'Tidak suka bergaul dengan orang yang kedudukannya lebih rendah',
            'E02' => 'Tidak suka bergaul dengan orang yang kedudukannya lebih tinggi',
            'E03' => 'Sering merasa malu dengan kawan lawan jenis',
            'E04' => 'Sering merasa iri hati',
            'E05' => 'Sukar mendapat teman',
            'E06' => 'Tidak suka bertamu',
            'E07' => 'Enggan menerima tamu',
            'E08' => 'Merasa rendah diri',
            'E09' => 'Sering merasa curiga pada orang lain',
            'E10' => 'Bersikap kaku dan tidak toleran',
            'E11' => 'Bersifat dingin dalam bergaul',
            'E12' => 'Sering merasa ingin bunuh diri',
            'E13' => 'Merasa pesimis atau tidak punya harapan',
            'E14' => 'Saya ingin lebih menarik',
            'E15' => 'Saya selalu menginginkan segalanya berjalan sempurna',
            'E16' => 'Suka merasa iba terhadap orang lain',
            'E17' => 'Suka marah',
            'E18' => 'Merasa tidak memiliki apa-apa yang diinginkan',
        ],
        'F' => [
            'F01' => 'Tidak senang bermain dalam kelompok',
            'F02' => 'Sering gagal dalam usaha mencari teman',
            'F03' => 'Sukar bergaul',
            'F04' => 'Merasa tidak disenangi kawan-kawan di luar sekolah',
            'F05' => 'Senang menjadi pusat perhatian',
            'F06' => 'Tidak berminat dalam berorganisasi',
            'F07' => 'Terlalu aktif dalam organisasi',
            'F08' => 'Sukar menyesuaikan diri',
            'F09' => 'Mudah tersinggung',
            'F10' => 'Takut bergaul dengan atasan',
            'F11' => 'Tidak pernah menjadi pemimpin',
            'F12' => 'Tidak pernah mengemukakan suatu pendapat',
            'F13' => 'Sering bertentangan pendapat dengan orang lain',
            'F14' => 'Sukar menerima kekalahan',
            'F15' => 'Selalu ingin berkuasa dalam pergaulan',
            'F16' => 'Bingung bila berhadapan dengan orang banyak',
            'F17' => 'Mudah merasa malu',
            'F18' => 'Mudah marah',
            'F19' => 'Sering tidak sabar',
            'F20' => 'Sering tidak menepati janji',
            'F21' => 'Tidak dapat menerima kritikan',
            'F22' => 'Bersifat tertutup',
            'F23' => 'Lebih senang menjadi anggota daripada ketua',
            'F24' => 'Jarang diajak bermain bersama oleh teman',
            'F25' => 'Kurang percaya diri dalam bergaul',
        ],
        'G' => [
            'G01' => 'Keinginan untuk rekreasi selalu terhalang',
            'G02' => 'Gemar melukis tetapi tidak mempunyai alat',
            'G03' => 'Waktu libur saya harus belajar',
            'G04' => 'Suka olahraga tetapi tidak ada kesempatan',
            'G05' => 'Lebih suka buku hiburan daripada buku pelajaran',
            'G06' => 'Setiap ada film baru saya nonton',
            'G07' => 'Salah satu keluargaku sering menghalangi hobiku',
            'G08' => 'Kesenangan membaca majalah menghabiskan waktu belajar',
            'G09' => 'Habis waktuku untuk nonton TV',
            'G10' => 'Orang tuaku tidak pernah mengajak rekreasi',
            'G11' => 'Terlalu sering rekreasi ke luar kota',
            'G12' => 'Sebagian besar waktu saya pakai untuk belajar',
            'G13' => 'Waktu banyak terpakai untuk membantu orang tua',
            'G14' => 'Saya tidak bisa menggunakan waktu luang saya',
            'G15' => 'Waktu luang banyak terpakai untuk hobi',
            'G16' => 'Waktu banyak terpakai untuk ngobrol',
            'G17' => 'Waktu banyak terpakai untuk latihan seni',
            'G18' => 'Saya tidak senang rekreasi',
            'G19' => 'Lebih senang di rumah daripada hobi di luar rumah',
            'G20' => 'Hampir tidak ada waktu untuk bermain',
            'G21' => 'Hobi selalu mengganggu waktu belajar',
            'G22' => 'Sering tidur untuk menghabiskan waktu luang',
        ],
        'H' => [
            'H01' => 'Sering malas untuk masuk sekolah',
            'H02' => 'Sering meninggalkan pelajaran',
            'H03' => 'Sering membolos',
            'H04' => 'Ingin pindah kelas lain',
            'H05' => 'Ingin pindah sekolah',
            'H06' => 'Sering merasa cemas bila ada ulangan',
            'H07' => 'Bahan pelajaran sulit dikuasai',
            'H08' => 'Ingin menjadi pengurus OSIS, tetapi tidak terpilih',
            'H09' => 'Ada beberapa pelajaran yang tidak saya sukai',
            'H10' => 'Pelajaran di sekolah terlalu membosankan',
            'H11' => 'Merasa kurang dimengerti oleh guru',
            'H12' => 'Peraturan sekolah terlalu menekan',
            'H13' => 'Pribadi guru menyebabkan pelajaran tidak kuperhatikan',
            'H14' => 'Beberapa mata pelajaran saya anggap tidak perlu',
            'H15' => 'Di sekolah tidak dapat memusatkan perhatian',
            'H16' => 'Di dalam kelas saya sering melamun',
            'H17' => 'Sering datang terlambat',
            'H18' => 'Saya sering dibenci oleh kawan-kawan di sekolahan',
            'H19' => 'Seorang kawan selalu menjengkelkan saya',
            'H20' => 'Tidak ada teman yang saya sukai untuk belajar bersama',
            'H21' => 'Takut jika bertemu guru yang galak',
            'H22' => 'Merasa tidak senang dengan wali kelas',
        ],
        'I' => [
            'I01' => 'Pelajaran di sekolah terlalu berat',
            'I02' => 'Pelajaran di sekolah terlalu mudah',
            'I03' => 'Sukar mendapatkan buku-buku pelajaran',
            'I04' => 'Saya takut terhadap ulangan',
            'I05' => 'Saya tidak suka belajar',
            'I06' => 'Saya mengerti isi buku pelajaran',
            'I07' => 'Saya tidak berminat terhadap buku',
            'I08' => 'Saya sering mendapatkan nilai rendah',
            'I09' => 'Saya tidak belajar bersama',
            'I10' => 'Sukar menangkap dan mengikuti pelajaran',
            'I11' => 'Sering merasa khawatir jika mendapat giliran maju di kelas',
            'I12' => 'Sering merasa sulit dalam mengerjakan PR',
            'I13' => 'Pelajaran yang bersifat hitungan sukar bagi saya',
            'I14' => 'Pelajaran yang bersifat hafalan sukar bagi saya',
            'I15' => 'Merasa segan membaca buku perpustakaan',
            'I16' => 'Sekolah sangat berarti bagi saya',
            'I17' => 'Saya ingin mengetahui bakat dan minat saya',
            'I18' => 'Saya tidak dapat mengetahui bakat dan minat saya',
        ],
        'J' => [
            'J01' => 'Saya tidak tahu berbuat apa setelah SMU',
            'J02' => 'Sukar menetapkan pilihan sekolah lanjutan',
            'J03' => 'Khawatir tidak diterima di Perguruan Tinggi Negeri',
            'J04' => 'Ingin melanjutkan ke jenjang yang lebih tinggi tetapi tidak ada biaya',
            'J05' => 'Merasa pesimis dengan masa depan karena sulit mencari pekerjaan',
            'J06' => 'Khawatir nantinya tidak bisa mandiri',
            'J07' => 'Ingin mengetahui bakat dan kemampuan diri',
            'J08' => 'Cita-cita saya tidak sesuai dengan kemampuan diri',
            'J09' => 'Bingung menentukan sikap setelah lulus nanti',
            'J10' => 'Merasa bingung jika belum bekerja',
            'J11' => 'Sering berdebar jika mengingat masa depan',
            'J12' => 'Ayah dan ibu keras dalam mengarahkan cita-cita',
        ],
        'K' => [
            'K01' => 'Belajar kalau ada ulangan',
            'K02' => 'Belajar tidak teratur waktunya',
            'K03' => 'Belajar hanya waktu malam hari',
            'K04' => 'Belajar hanya waktu siang hari',
            'K05' => 'Sukar memusatkan perhatian pada waktu belajar',
            'K06' => 'Sukar mengingat pelajaran yang telah dihafal',
            'K07' => 'Sulit untuk memulai belajar',
            'K08' => 'Kalau belajar sering mengantuk',
            'K09' => 'Sering merasa malas belajar',
            'K10' => 'Sering merasa terganggu saudara ketika sedang belajar',
            'K11' => 'Belajar dengan cara menghafal',
            'K12' => 'Belajar dengan cara membayangkan',
            'K13' => 'Belajar dengan cara membuat ringkasan',
            'K14' => 'Tidak dapat menerapkan cara belajar yang baik',
            'K15' => 'Sering menyalin PR teman',
        ],
        'L' => [
            'L01' => 'Memikirkan masalah cinta adalah soal yang terlalu awal bagi saya',
            'L02' => 'Bercinta adalah bagian dari hidup saya',
            'L03' => 'Merasa tabu membicarakan soal cinta',
            'L04' => 'Bercinta dalam masa sekolah dapat menjadi dorongan',
            'L05' => 'Bercinta dalam masa sekolah dapat menghancurkan semangat',
            'L06' => 'Saya mulai tertarik dengan lawan jenis',
            'L07' => 'Saya lebih tertarik pada teman sejenis',
            'L08' => 'Saya pernah patah hati ditinggal pacar',
            'L09' => 'Sering membayangkan adegan cinta',
            'L10' => 'Gemar melihat atau membaca film yang bernuansa cinta',
            'L11' => 'Terpaksa bercinta secara sembunyi-sembunyi',
            'L12' => 'Merasa jijik atau muak jika ada orang membicarakan masalah cinta',
            'L13' => 'Saya tidak bisa belajar jika dia tidak berkirim surat',
            'L14' => 'Sering melamun memikirkan si dia',
            'L15' => 'Saya ragu-ragu terhadap pacar saya',
            'L16' => 'Orang tua melarang saya pacaran dulu',
            'L17' => 'Pacarku selalu mengajak keluar rumah',
            'L18' => 'Saya kesepian karena belum punya pacar',
            'L19' => 'Iri melihat kawan-kawan pacaran',
            'L20' => 'Memilih calon pacar sulit bagi saya',
            'L21' => 'Sering bertepuk sebelah tangan',
            'L22' => 'Sukar bergaul dengan teman lawan jenis',
            'L23' => 'Jodohku telah ditentukan oleh orang tua',
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

        foreach (self::SECTIONS as $section => $title) {
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
                'title' => $title,
                'items' => $items,
                'checked_count' => $checked->count(),
                'total' => count($items),
            ];
        }

        return $groups;
    }

    public function masalahSummary(): array
    {
        $jawaban = $this->jawaban ?? [];

        $labels = [];

        foreach ($jawaban as $section => $codes) {
            foreach ($codes as $kode) {
                $pertanyaan = self::QUESTION_GROUPS[$section][$kode] ?? null;

                if ($pertanyaan) {
                    $labels[] = "{$kode} - {$pertanyaan}";
                }
            }
        }

        return $labels;
    }
}
