@extends('layouts.app')

@section('content')
<!-- ===== Main Content Grid Start ===== -->
<div class="grid grid-cols-12 gap-4 md:gap-6">

  <!-- Kolom Kiri (Lebar 7/12 di layar besar) -->
  <div class="col-span-12 space-y-6 xl:col-span-7">

    <!-- Metric Group -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6">
      <!-- Metric Item 1 -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-slate-800/50">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
          <i class="fa-solid fa-users text-gray-800 dark:text-white/90 text-xl"></i>
        </div>
        <div class="mt-5 flex items-end justify-between">
          <div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Total Siswa Aktif</span>
            <h4 class="text-2xl mt-2 font-bold text-gray-800 dark:text-white/90">1,245</h4>
          </div>
          <span class="bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400 flex items-center gap-1 rounded-full py-0.5 px-2.5 text-sm font-medium">
            <i class="fa-solid fa-arrow-up text-xs"></i> 11.01%
          </span>
        </div>
      </div>

      <!-- Metric Item 2 -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-slate-800/50">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
          <i class="fa-solid fa-user-check text-gray-800 dark:text-white/90 text-xl"></i>
        </div>
        <div class="mt-5 flex items-end justify-between">
          <div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Kehadiran Hari Ini</span>
            <h4 class="text-2xl mt-2 font-bold text-gray-800 dark:text-white/90">98.2%</h4>
          </div>
          <span class="bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400 flex items-center gap-1 rounded-full py-0.5 px-2.5 text-sm font-medium">
            <i class="fa-solid fa-arrow-up text-xs"></i> 2.05%
          </span>
        </div>
      </div>
    </div>

    <!-- Placeholder Chart Area -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-slate-800/50 min-h-[300px] flex flex-col items-center justify-center">
        <i class="fa-solid fa-chart-line text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
        <p class="text-gray-500 dark:text-gray-400">Area Grafik / Chart 1</p>
    </div>
  </div>

  <!-- Kolom Kanan (Lebar 5/12 di layar besar) -->
  <div class="col-span-12 xl:col-span-5 space-y-6">
    <!-- Placeholder Chart/List Area -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-slate-800/50 min-h-[450px] flex flex-col items-center justify-center">
        <i class="fa-solid fa-chart-pie text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
        <p class="text-gray-500 dark:text-gray-400">Area Grafik / Chart 2</p>
    </div>
  </div>

  <!-- Kolom Penuh (Lebar 12/12) -->
  <div class="col-span-12">
    <!-- Table Area -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-slate-800/50">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white/90 mb-4">Data Presensi Terbaru</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-800 dark:text-white/90">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">Nama Siswa</th>
                        <th class="px-4 py-3">Kelas</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 rounded-r-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">Budi Santoso</td>
                        <td class="px-4 py-3">10-A</td>
                        <td class="px-4 py-3"><span class="bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400 px-2.5 py-1 rounded-full text-xs font-medium">Hadir</span></td>
                        <td class="px-4 py-3"><button class="text-blue-500 hover:text-blue-600 font-medium">Detail</button></td>
                    </tr>
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">Siti Aminah</td>
                        <td class="px-4 py-3">11-B</td>
                        <td class="px-4 py-3"><span class="bg-yellow-full text-yellow-600 dark:bg-yellow-500/15 dark:text-yellow-400 px-2.5 py-1 rounded-full text-xs font-medium">Izin</span></td>
                        <td class="px-4 py-3"><button class="text-blue-500 hover:text-blue-600 font-medium">Detail</button></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">Doni Pratama</td>
                        <td class="px-4 py-3">12-C</td>
                        <td class="px-4 py-3"><span class="bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400 px-2.5 py-1 rounded-full text-xs font-medium">Alpa</span></td>
                        <td class="px-4 py-3"><button class="text-blue-500 hover:text-blue-600 font-medium">Detail</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
  </div>
</div>
<!-- ===== Main Content Grid End ===== -->
@endsection
