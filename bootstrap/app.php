<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('ortu/*') ? route('ortu.login') : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (QueryException $e, Request $request) {
            if ($request->expectsJson() || $request->isMethod('GET')) {
                return null;
            }

            $kode = (string) ($e->errorInfo[1] ?? $e->getCode());
            $pesan = match (true) {
                str_contains($e->getMessage(), 'NOT NULL') || in_array($kode, ['1048', '1364'], true) => 'Ada isian wajib yang masih kosong. Lengkapi semua kolom bertanda wajib lalu simpan kembali.',
                str_contains($e->getMessage(), 'UNIQUE') || $kode === '1062' => 'Data yang sama sudah ada. Gunakan nilai lain untuk isian yang harus unik (mis. NIP, NIS, atau nama).',
                str_contains($e->getMessage(), 'FOREIGN KEY') || in_array($kode, ['1451', '1452'], true) => 'Data ini tidak dapat diproses karena masih terhubung dengan data lain (atau data yang dipilih tidak ditemukan).',
                default => 'Data tidak dapat disimpan karena terjadi kesalahan pada basis data. Periksa kembali isian Anda atau hubungi admin.',
            };

            report($e);

            return back()->withInput($request->except('password', 'password_confirmation', 'password_lama'))->with('error', $pesan);
        });

        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            return back()->withInput($request->except('password', 'password_confirmation', 'password_lama'))
                ->with('error', 'Sesi formulir sudah kedaluwarsa. Silakan kirim ulang formulir.');
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
