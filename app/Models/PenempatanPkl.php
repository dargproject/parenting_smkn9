<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenempatanPkl extends Model
{
    const PREDIKAT_OPTIONS = ['Sangat Baik', 'Baik', 'Cukup', 'Perlu Bimbingan'];

    const PROGRES_OPTIONS = ['25%', '50%', '75%', '100%'];

    const TEMPLATE_CATATAN = [
        'etika' => [
            'Sangat Baik' => 'Siswa menunjukkan etika kerja dan sikap yang sangat baik selama PKL: konsisten hadir tepat waktu, berpakaian dan bertutur sopan sesuai budaya kerja industri, serta selalu mematuhi tata tertib dan arahan dari pihak IDUKA tanpa perlu pengawasan berulang.',
            'Baik' => 'Siswa menunjukkan etika kerja dan sikap yang baik selama PKL, pada umumnya disiplin hadir dan mematuhi aturan di tempat PKL, meski sesekali masih memerlukan pengingat terkait tata tertib tertentu.',
            'Cukup' => 'Siswa cukup disiplin dalam menjalankan PKL, namun konsistensi kehadiran dan kepatuhan terhadap aturan di tempat kerja masih fluktuatif sehingga perlu pendampingan lebih intensif dari pembimbing.',
            'Perlu Bimbingan' => 'Siswa masih sering mengalami kendala dalam kedisiplinan, kehadiran, maupun kepatuhan terhadap aturan di tempat PKL, sehingga memerlukan bimbingan dan pemantauan yang lebih intensif dari pembimbing sekolah maupun pihak IDUKA.',
        ],
        'kemandirian' => [
            'Sangat Baik' => 'Siswa menunjukkan kemandirian yang sangat baik dalam melaksanakan tugas: mampu mengidentifikasi dan menyelesaikan permasalahan di lapangan secara mandiri dengan inisiatif tinggi, serta berani mengambil keputusan yang tepat tanpa harus menunggu arahan.',
            'Baik' => 'Siswa cukup mandiri dalam mengerjakan tugas yang diberikan dan mampu mengatasi sebagian besar permasalahan di lapangan, meski pada kondisi tertentu masih memerlukan arahan dari pembimbing industri.',
            'Cukup' => 'Siswa masih memerlukan arahan yang cukup sering dalam menyelesaikan tugas maupun mengatasi permasalahan di lapangan; kemandirian dan inisiatif perlu terus dilatih.',
            'Perlu Bimbingan' => 'Siswa masih sangat bergantung pada arahan pembimbing dalam menyelesaikan tugas maupun mengatasi permasalahan di lapangan; kemandirian dan kemampuan problem solving perlu mendapat perhatian khusus.',
        ],
        'administrasi' => [
            'Sangat Baik' => 'Siswa sangat tertib, lengkap, dan tepat waktu dalam mengisi jurnal harian maupun laporan PKL, menunjukkan tanggung jawab yang tinggi terhadap seluruh administrasi kegiatan PKL.',
            'Baik' => 'Siswa tertib mengisi jurnal PKL dan menunjukkan tanggung jawab yang baik terhadap administrasi, meski sesekali masih ada keterlambatan kecil dalam pengisian.',
            'Cukup' => 'Siswa cukup tertib dalam mengisi jurnal PKL, namun masih memerlukan pengingat terkait ketepatan waktu dan kelengkapan data administrasi secara berkala.',
            'Perlu Bimbingan' => 'Siswa masih sering terlambat atau tidak lengkap dalam mengisi jurnal dan laporan PKL, sehingga memerlukan bimbingan serta pemantauan lebih ketat terkait tanggung jawab administrasi.',
        ],
        'progres' => [
            '25%' => 'Siswa baru menyelesaikan sebagian kecil (sekitar 25%) dari laporan/jurnal PKL; sebagian besar bagian laporan masih dalam proses pengerjaan dan perlu dipantau agar selesai tepat waktu.',
            '50%' => 'Siswa telah menyelesaikan sekitar separuh (50%) laporan/jurnal PKL; progres berjalan sesuai rencana, namun masih perlu penyelesaian pada bagian-bagian berikutnya.',
            '75%' => 'Siswa telah menyelesaikan sebagian besar (sekitar 75%) laporan/jurnal PKL; tinggal penyempurnaan dan pelengkapan beberapa bagian akhir sebelum laporan final.',
            '100%' => 'Siswa telah menyelesaikan seluruh (100%) laporan/jurnal PKL dengan baik dan siap untuk diperiksa serta dikumpulkan.',
        ],
    ];

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'nama_iduka',
        'alamat',
        'nama_cp_industri',
        'no_cp_industri',
        'guru_pembimbing_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'catatan',
        'predikat_etika',
        'catatan_etika',
        'predikat_kemandirian',
        'catatan_kemandirian',
        'predikat_administrasi',
        'catatan_administrasi',
        'predikat_progres',
        'catatan_progres',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function guruPembimbing()
    {
        return $this->belongsTo(Guru::class, 'guru_pembimbing_id');
    }
}
