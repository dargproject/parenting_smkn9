<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    public function guru(): JsonResponse
    {
        $nama = setting('nama_sekolah', 'SIM Sekolah');

        return $this->manifest(
            name: "SIM Akademik - {$nama}",
            shortName: 'SIM Akademik',
            startUrl: '/login',
            scope: '/',
            themeColor: '#2563eb',
            iconPrefix: 'pwa-guru',
        );
    }

    public function ortu(): JsonResponse
    {
        $nama = setting('nama_sekolah', 'SIM Sekolah');

        return $this->manifest(
            name: "Portal Orang Tua - {$nama}",
            shortName: 'Portal Ortu',
            startUrl: '/ortu/login',
            scope: '/ortu/',
            themeColor: '#059669',
            iconPrefix: 'pwa-ortu',
        );
    }

    private function manifest(string $name, string $shortName, string $startUrl, string $scope, string $themeColor, string $iconPrefix): JsonResponse
    {
        return response()->json([
            'name' => $name,
            'short_name' => $shortName,
            'start_url' => $startUrl,
            'scope' => $scope,
            'display' => 'standalone',
            'background_color' => '#f1f5f9',
            'theme_color' => $themeColor,
            'lang' => 'id',
            'icons' => [
                [
                    'src' => asset("assets/img/icons/{$iconPrefix}-192.png"),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset("assets/img/icons/{$iconPrefix}-512.png"),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }
}
