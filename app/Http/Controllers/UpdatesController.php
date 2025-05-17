<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpdatesController extends Controller
{
    public function index()
    {
        return response()->json([
            "application" => "DIM",
            "current_version" => "1.0.0",
            "min_compatible_version" => "0.9.0",
            "updates" => [
                [
                    "version" => "1.0.0",
                    "release_date" => "2025-05-01",
                    "download_url" => [
                        "windows" => "https://dim.dervox.com/downloads/dim-setup-x64.exe",
                        "linux" => "https://dim.dervox.com/downloads/dim-setup-x64.exe",
                        "macos" => "https://dim.dervox.com/downloads/dim-1.0.0-macos.dmg",
                        "android" => "https://dim.dervox.com/downloads/dim-1.0.0-android.apk"
                    ],
                    "changelog" => "### Changelog\n- Performance improvements \n- Bug fixes",
                    "is_mandatory" => false,
                    "file_size" => [
                        "windows" => 9200000,
                        "linux" => 22000000,
                        "macos" => 25000000,
                        "android" => 15000000
                    ]
                ],
            ]
        ]);
    }
    
    public function serveStoredJson()
    {
     
        if (Storage::exists('updates.json')) {
            $content = Storage::get('updates.json');
            return response($content)->header('Content-Type', 'application/json');
        }
        
        return response()->json(['error' => 'Updates information not available'], 404);
    }
}
