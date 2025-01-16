<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function change(Request $request)
    {
        $lang = $request->lang;
        
        if (!in_array($lang, ['en', 'fr','ar'])) {
            abort(400);
        }
        
        Session::put('locale', $lang);
        App::setLocale($lang);
        
        return redirect()->back();
    }
}
