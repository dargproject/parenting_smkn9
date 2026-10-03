<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiswaRequest;
use App\Http\Requests\UpdateSiswaRequest;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $siswas = Siswa::with(['kelas', 'orangTua'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('nama', 'like', '%'.$request->q.'%')->orWhere('nis', 'like', '%'.$request->q.'%')->orWhere('nisn', 'like', '%'.$request->q.'%')->orWhere('nipd', 'like', '%'.$request->q.'%')))
            ->when($request->filled('kelas_id'), fn ($q) => $q->where('kelas_id', $request->kelas_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status_aktif', $request->status === 'aktif'))
            ->latest()->paginate(15);

        return view('admin.siswa.index', [
            'siswas' => $siswas,
            'kelasOptions' => Kelas::orderBy('tingkat')->orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'totalSiswaKeseluruhan' => Siswa::count(),
        ]);
    }

    public function create()
    {
        return view('admin.siswa.create', ['siswa' => new Siswa, 'kelas' => Kelas::orderBy('nama_kelas')->get()]);
    }

    public function store(StoreSiswaRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make('password');
        Siswa::create($data);

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        return view('admin.siswa.edit', ['siswa' => $siswa, 'kelas' => Kelas::orderBy('nama_kelas')->get()]);
    }

    public function update(UpdateSiswaRequest $request, Siswa $siswa)
    {
        $siswa->update($request->validated());

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    public function destroyAll()
    {
        $jumlah = Siswa::count();

        if ($jumlah === 0) {
            return back()->with('error', 'Tidak ada data siswa untuk dihapus.');
        }

        Siswa::query()->delete();

        return back()->with('success', "Berhasil menghapus seluruh {$jumlah} data siswa, beserta seluruh data nilai, presensi, pelanggaran, BK, dan akun orang tua yang terkait.");
    }

    public function resetPassword(Siswa $siswa)
    {
        $siswa->update(['password' => Hash::make('password')]);

        return back()->with('success', 'Password direset ke password default.');
    }

    public function buatAkunOrtu(Siswa $siswa)
    {
        if (! $siswa->no_hp_ortu) {
            return back()->with('error', 'Isi No. HP Orang Tua pada data siswa ini terlebih dahulu.');
        }

        if ($siswa->orangTua) {
            return back()->with('error', 'Siswa ini sudah punya akun orang tua.');
        }

        $username = preg_replace('/\D/', '', $siswa->no_hp_ortu);

        if (OrangTua::where('username', $username)->exists()) {
            return back()->with('error', "Nomor HP {$siswa->no_hp_ortu} sudah dipakai sebagai username akun orang tua siswa lain. Gunakan nomor HP yang berbeda untuk siswa ini.");
        }

        OrangTua::create([
            'siswa_id' => $siswa->id,
            'nama' => 'Orang Tua/Wali '.$siswa->nama,
            'username' => $username,
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        return back()->with('success', "Akun orang tua berhasil dibuat. Username: {$username}, password default: password");
    }

    public function hapusAkunOrtu(Siswa $siswa)
    {
        if (! $siswa->orangTua) {
            return back()->with('error', 'Siswa ini belum punya akun orang tua.');
        }

        $siswa->orangTua->delete();

        return back()->with('success', 'Akun orang tua berhasil dihapus. Data nilai, presensi, dan pesan siswa ini tidak terpengaruh.');
    }
}
