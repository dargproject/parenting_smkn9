<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sosiometri extends Model
{
    protected $table = 'sosiometris';

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'tanggal',
        'judul',
        'instruksi',
        'jumlah_pilihan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah_pilihan' => 'integer',
    ];

    public const PERTANYAAN = [
        'Q1' => 'Siapa teman di kelas ini yang paling kamu inginkan untuk menjadi teman satu kelompok belajar? (sebutkan 1-3 nama)',
        'Q2' => 'Siapa teman yang paling tidak kamu harapkan berada dalam satu kelompok belajar denganmu? (sebutkan 1-3 nama)',
        'Q3' => 'Jika kamu sedang memiliki masalah pribadi dan ingin bercerita, siapa teman di kelompok ini yang paling kamu percayai? (sebutkan 1-3 nama)',
        'Q4' => 'Siapa teman yang paling jarang kamu ajak mengobrol atau berinteraksi saat waktu istirahat? (sebutkan 1-3 nama)',
        'Q5' => 'Jika kelompok ini harus memilih seorang ketua untuk memimpin proyek baru, siapa yang akan kamu pilih? (sebutkan 1-3 nama)',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function respons(): HasMany
    {
        return $this->hasMany(SosiometriRespon::class);
    }

    public function questionGroups(): array
    {
        $groups = [];
        $respons = $this->respons ?? collect();

        foreach (self::PERTANYAAN as $key => $pertanyaan) {
            $dipilih = $respons
                ->where('pertanyaan', $key)
                ->sortBy('urutan')
                ->map(fn ($r) => $r->siswaDipilih?->nama ?? $r->nama_dipilih ?? '')
                ->filter()
                ->values()
                ->all();

            $groups[] = [
                'key' => $key,
                'pertanyaan' => $pertanyaan,
                'dipilih' => $dipilih,
            ];
        }

        return $groups;
    }
}
