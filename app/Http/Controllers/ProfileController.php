<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Helpers\CountryHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        // Get countries for dropdown
        $countries = CountryHelper::getCountriesForDropdown();
        $popularCountries = CountryHelper::getPopularCountries();
        
        // Get user's current country info if set
        $currentCountryInfo = null;
        if ($user->country_id) {
            $currentCountryInfo = [
                'code' => $user->country_id,
                'name' => $user->country_name,
                'flag' => $user->country_flag,
                'currency' => $user->currency_code,
                'currency_symbol' => $user->currency_symbol,
            ];
        }

        return view('profile.edit', [
            'user' => $user,
            'countries' => $countries,
            'popularCountries' => $popularCountries,
            'currentCountryInfo' => $currentCountryInfo,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->getValidatedData();
        
        // Track if country changed for logging/analytics
        $countryChanged = false;
        if ($user->country_id !== $validated['country_id']) {
            $countryChanged = true;
            $oldCountry = $user->country_id;
            $newCountry = $validated['country_id'];
            
            Log::info('User country updated', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'old_country' => $oldCountry,
                'new_country' => $newCountry,
                'old_country_name' => $oldCountry ? CountryHelper::getCountryName($oldCountry) : null,
                'new_country_name' => $newCountry ? CountryHelper::getCountryName($newCountry) : null,
                'timestamp' => now(),
            ]);
        }

        // Fill user with validated data
        $user->fill($validated);

        // Handle email verification reset
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            
            Log::info('User email updated - verification reset', [
                'user_id' => $user->id,
                'old_email' => $user->getOriginal('email'),
                'new_email' => $user->email,
            ]);
        }

        // Save the user
        $user->save();

        // Prepare success message
        $message = __('messages.Profile updated successfully.');
        if ($countryChanged) {
            $countryName = $user->country_name;
            $message = __('messages.Profile updated successfully. Country changed to :country.', [
                'country' => $countryName
            ]);
        }

        // Clear any cached country data for this user if country changed
        if ($countryChanged && $user->country_id) {
            cache()->forget("user_country_info_{$user->id}");
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated')->with('message', $message);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        
        // Log account deletion for audit trail
        Log::warning('User account deleted', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
            'country_id' => $user->country_id,
            'team_id' => $user->team_id,
            'deleted_at' => now(),
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Get country information for AJAX requests
     */
    public function getCountryInfo(Request $request)
    {
        $request->validate([
            'country_code' => 'required|string|size:3'
        ]);

        $countryCode = strtoupper($request->country_code);
        
        if (!CountryHelper::isValidCountryCode($countryCode)) {
            return response()->json([
                'success' => false,
                'message' => __('validation.The selected country is invalid.')
            ], 400);
        }

        $country = CountryHelper::getCountryByCode($countryCode);
        $currency = CountryHelper::getCurrencyByCountryCode($countryCode);

        if (!$country) {
            return response()->json([
                'success' => false,
                'message' => __('validation.Country not found.')
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $countryCode,
                'name' => $country->name->common,
                'flag' => $country->flag->emoji ?? '🌍',
                'currency_code' => $currency->iso->code ?? 'USD',
                'currency_symbol' => $currency->units->major->symbol ?? '$',
                'currency_name' => $currency->name ?? 'US Dollar',
                'calling_code' => $country->idd ? ($country->idd->root . ($country->idd->suffixes[0] ?? '')) : '+1',
                'region' => $country->region ?? 'Unknown',
                'timezone' => is_array($country->timezones) ? $country->timezones[0] : ($country->timezones ?? 'UTC'),
            ]
        ]);
    }

    /**
     * Search countries for AJAX autocomplete
     */
    public function searchCountries(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1|max:50'
        ]);

        $query = $request->query;
        $countries = CountryHelper::searchCountries($query, 15);

        $results = [];
        foreach ($countries as $code => $name) {
            $country = CountryHelper::getCountryByCode($code);
            $results[] = [
                'code' => $code,
                'name' => $name,
                'flag' => $country->flag->emoji ?? '🌍',
                'display' => ($country->flag->emoji ?? '🌍') . ' ' . $name,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }
}
