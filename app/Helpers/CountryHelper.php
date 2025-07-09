<?php

namespace App\Helpers;

use PragmaRX\Countries\Package\Countries;
use Illuminate\Support\Facades\Cache;

class CountryHelper
{
    /**
     * Get all countries formatted for dropdown/select options
     * Returns array with country code as key and country name as value
     * 
     * @return array
     */
    public static function getCountriesForDropdown()
    {
        return Cache::remember('countries_dropdown', 60 * 24, function () {
            return Countries::all()
                ->sortBy('name.common')
                ->pluck('name.common', 'cca3')
                ->toArray();
        });
    }

    /**
     * Get popular countries (commonly used ones) for quick selection
     * 
     * @return array
     */
    public static function getPopularCountries()
    {
        $popularCodes = [
            'USA', 'GBR', 'CAN', 'AUS', 'DEU', 'FRA', 'JPN', 'CHN', 
            'IND', 'BRA', 'MEX', 'ITA', 'ESP', 'NLD', 'SWE', 'NOR'
        ];

        $allCountries = self::getCountriesForDropdown();
        
        return array_intersect_key(
            $allCountries, 
            array_flip($popularCodes)
        );
    }

    /**
     * Get all unique currencies from all countries
     * 
     * @return array
     */
    public static function getCurrenciesForDropdown()
    {
        return Cache::remember('currencies_dropdown', 60 * 24, function () {
            return Countries::all()
                ->map(function ($country) {
                    $country = $country->hydrateCurrencies();
                    return $country->currencies ? $country->currencies->keys() : [];
                })
                ->flatten()
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        });
    }

    /**
     * Get country by code
     * 
     * @param string $countryCode
     * @return mixed
     */
    public static function getCountryByCode($countryCode)
    {
        if (!$countryCode) {
            return null;
        }

        return Cache::remember("country_{$countryCode}", 60 * 24, function () use ($countryCode) {
            return Countries::where('cca3', $countryCode)->first();
        });
    }

    /**
     * Get country name by code
     * 
     * @param string $countryCode
     * @return string
     */
    public static function getCountryName($countryCode)
    {
        $country = self::getCountryByCode($countryCode);
        return $country ? $country->name->common : 'Unknown';
    }

    /**
     * Get currency info by country code
     * 
     * @param string $countryCode
     * @return object|null
     */
    public static function getCurrencyByCountryCode($countryCode)
    {
        $country = self::getCountryByCode($countryCode);
        
        if ($country) {
            $country = $country->hydrateCurrencies();
            $currencies = $country->currencies;
            if ($currencies && $currencies->count() > 0) {
                return $currencies->first();
            }
        }
        
        return null;
    }

    /**
     * Get countries grouped by continent/region
     * 
     * @return array
     */
    public static function getCountriesByRegion()
    {
        return Cache::remember('countries_by_region', 60 * 24, function () {
            $countries = Countries::all();
            $grouped = [];

            foreach ($countries as $country) {
                $region = $country->region ?? 'Other';
                $grouped[$region][$country->cca3] = $country->name->common;
            }

            // Sort each region's countries
            foreach ($grouped as $region => &$countryList) {
                asort($countryList);
            }

            return $grouped;
        });
    }

    /**
     * Search countries by name (useful for AJAX searches)
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    public static function searchCountries($query, $limit = 10)
    {
        $allCountries = self::getCountriesForDropdown();
        
        $filtered = array_filter($allCountries, function ($name) use ($query) {
            return stripos($name, $query) !== false;
        });

        return array_slice($filtered, 0, $limit, true);
    }

    /**
     * Validate country code
     * 
     * @param string $countryCode
     * @return bool
     */
    public static function isValidCountryCode($countryCode)
    {
        if (!$countryCode || strlen($countryCode) !== 3) {
            return false;
        }

        $allCountries = self::getCountriesForDropdown();
        return array_key_exists($countryCode, $allCountries);
    }

    /**
     * Get country info with flag for display
     * 
     * @param string $countryCode
     * @return string
     */
    public static function getCountryWithFlag($countryCode)
    {
        $country = self::getCountryByCode($countryCode);
        
        if ($country) {
            return $country->flag->emoji . ' ' . $country->name->common;
        }
        
        return '🌍 Unknown Country';
    }

    /**
     * Clear countries cache (useful for updates)
     * 
     * @return void
     */
    public static function clearCache()
    {
        Cache::forget('countries_dropdown');
        Cache::forget('currencies_dropdown');
        Cache::forget('countries_by_region');
        
        // Clear individual country caches
        $countries = Countries::all();
        foreach ($countries as $country) {
            Cache::forget("country_{$country->cca3}");
        }
    }
}
