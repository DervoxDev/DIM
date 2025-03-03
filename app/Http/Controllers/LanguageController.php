<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use App\Models\Team;

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
    public function changeInvoiceLanguage(Request $request, $teamId)
    {
        // Validate request
        $request->validate([
            'lang' => 'required|string|in:en,fr,ar'
        ]);

        $team = Team::findOrFail($teamId);
        
        // Update team's locale in database
        $team->update([
            'locale' => $request->lang
        ]);
        
        // Store in session
        Session::put('team_locale_' . $teamId, $request->lang);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Team language updated successfully',
            'language' => $request->lang
        ]);
    }

    public function getInvoiceLanguage($teamId)
    {
        $team = Team::findOrFail($teamId);
        
        $lang = $team->locale ?? Session::get('team_locale_' . $teamId, config('app.locale'));
        
        return response()->json([
            'status' => 'success',
            'language' => $lang
        ]);
    }
}
