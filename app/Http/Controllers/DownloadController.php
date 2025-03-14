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

    public function download($os, $arch = null, $type = null)
    {
        $files = [
            'windows-64' => [
                'path' => 'downloads/dim-setup-x64.exe',
                'name' => 'dim-setup-x64.exe',
                'size' => '84.5 MB',
                'mime' => 'application/octet-stream'
            ],
            'windows-32' => [
                'path' => 'downloads/saasi-setup-x86.exe',
                'name' => 'saasi-setup-x86.exe',
                'size' => '65.8 MB',
                'mime' => 'application/octet-stream'
            ],
            'mac-64' => [
                'path' => 'downloads/saasi-x64.dmg',
                'name' => 'saasi-x64.dmg',
                'size' => '78.5 MB',
                'mime' => 'application/x-apple-diskimage'
            ],
            'mac-32' => [
                'path' => 'downloads/saasi-x86.dmg',
                'name' => 'saasi-x86.dmg',
                'size' => '72.3 MB',
                'mime' => 'application/x-apple-diskimage'
            ],
            'linux-64-deb' => [
                'path' => 'downloads/saasi-amd64.deb',
                'name' => 'saasi-amd64.deb',
                'size' => '67.2 MB',
                'mime' => 'application/vnd.debian.binary-package'
            ],
            'linux-32-deb' => [
                'path' => 'downloads/saasi-i386.deb',
                'name' => 'saasi-i386.deb',
                'size' => '63.8 MB',
                'mime' => 'application/vnd.debian.binary-package'
            ],
            'linux-64-rpm' => [
                'path' => 'downloads/saasi-x86_64.rpm',
                'name' => 'saasi-x86_64.rpm',
                'size' => '68.1 MB',
                'mime' => 'application/x-rpm'
            ],
            'linux-32-rpm' => [
                'path' => 'downloads/saasi-i686.rpm',
                'name' => 'saasi-i686.rpm',
                'size' => '64.2 MB',
                'mime' => 'application/x-rpm'
            ],
            'linux-64-appimage' => [
                'path' => 'downloads/saasi-x86_64.AppImage',
                'name' => 'saasi-x86_64.AppImage',
                'size' => '69.3 MB',
                'mime' => 'application/x-executable'
            ],
            'linux-32-appimage' => [
                'path' => 'downloads/saasi-i386.AppImage',
                'name' => 'saasi-i386.AppImage',
                'size' => '65.1 MB',
                'mime' => 'application/x-executable'
            ],
        ];

        // Set default architecture if not specified
        if ($arch === null) {
            // Default to 64-bit
            $arch = '64';
        }

        $key = "{$os}-{$arch}";
        
        if ($os === 'linux' && $type) {
            $key = "linux-{$arch}-{$type}";
        }
        
        // For backward compatibility - try without arch if not found
        if (!isset($files[$key]) && isset($files[$os])) {
            $key = $os;
        }

        if (!isset($files[$key])) {
            abort(404, 'Download not available for this configuration');
        }

        $file = $files[$key];
        
        if (!Storage::exists($file['path'])) {
            abort(404, 'Download file not found');
        }

        // Optional: Log the download
        \Log::info('File downloaded: ' . $file['name'] . ' for ' . $key);

        // Return the file with proper headers
        return Storage::download(
            $file['path'], 
            $file['name'], 
            ['Content-Type' => $file['mime']]
        );
    }
    
    /**
     * Auto-detect the appropriate download link based on user agent
     */
    public function autoDetect()
    {
        $agent = request()->userAgent();
        $os = 'windows';
        $arch = '64'; // Default to 64-bit
        $type = null;
        
        // Detect OS
        if (strpos($agent, 'Windows') !== false) {
            $os = 'windows';
        } elseif (strpos($agent, 'Mac') !== false) {
            $os = 'mac';
        } elseif (strpos($agent, 'Linux') !== false) {
            $os = 'linux';
            $type = 'appimage'; // Default Linux type
        }
        
        // Detect architecture - not 100% reliable
        if (strpos($agent, 'WOW64') !== false || 
            strpos($agent, 'Win64') !== false || 
            strpos($agent, 'x86_64') !== false || 
            strpos($agent, 'amd64') !== false) {
            $arch = '64';
        } else {
            $arch = '32';
        }
        
        // Redirect to appropriate download
        if ($os === 'linux' && $type) {
            return redirect()->route('download.os', ['os' => $os, 'arch' => $arch, 'type' => $type]);
        }
        
        return redirect()->route('download.os', ['os' => $os, 'arch' => $arch]);
    }
}
