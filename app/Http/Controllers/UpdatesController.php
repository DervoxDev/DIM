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
                    "version" => "1.2.0",
                    "release_date" => "2023-10-15",
                    "download_url" => [
                        "windows" => "https://dim.dervox.com/downloads/dim-setup-x64.exe",
                        "linux" => "https://dim.dervox.com/downloads/dim-setup-x64.exe",
                        "macos" => "https://dim.dervox.com/downloads/dim-1.0.0-macos.dmg",
                        "android" => "https://dim.dervox.com/downloads/dim-1.0.0-android.apk"
                    ],
                    "changelog" => "### New Features\n- Feature 1\n- Feature 2\n\n### Bug Fixes\n- Fixed issue 1\n- Fixed issue 2",
                    "is_mandatory" => false,
                    "file_size" => [
                        "windows" => 24500000,
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
