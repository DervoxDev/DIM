<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function index()
    {
        return view('download');
    }

    public function download($os, $type = null)
    {
        $files = [
            'windows' => [
                'path' => 'downloads/saasi-setup.exe',
                'name' => 'saasi-setup.exe',
                'size' => '68.5 MB'
            ],
            'mac' => [
                'path' => 'downloads/saasi.dmg',
                'name' => 'saasi.dmg',
                'size' => '72.3 MB'
            ],
            'linux-deb' => [
                'path' => 'downloads/saasi.deb',
                'name' => 'saasi.deb',
                'size' => '63.8 MB'
            ],
            'linux-rpm' => [
                'path' => 'downloads/saasi.rpm',
                'name' => 'saasi.rpm',
                'size' => '64.2 MB'
            ],
            'linux-appimage' => [
                'path' => 'downloads/saasi.AppImage',
                'name' => 'saasi.AppImage',
                'size' => '65.1 MB'
            ]
        ];

        $key = $os;
        if ($os === 'linux' && $type) {
            $key = "linux-{$type}";
        }

        if (!isset($files[$key])) {
            abort(404);
        }

        $file = $files[$key];
        
        if (!Storage::exists($file['path'])) {
            abort(404);
        }

        return Storage::download($file['path'], $file['name']);
    }
}