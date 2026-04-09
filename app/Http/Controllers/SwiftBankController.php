<?php

namespace Modules\SwiftBank\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\SwiftBank\Models\SwiftBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SwiftBankController extends Controller
{
  public function index() {
    return view('swiftbank::index');
  }

  /**
  * Tampilkan daftar negara yang tersedia.
  */
  public function countries() {
    // Ambil daftar negara unik (country_code dan nama negara)
    $countries = Cache::remember(config('swiftbank.cache_prefix') . 'country', now()->addDays(), function() {
      return SwiftBank::select('country_code')
      ->distinct()
      ->orderBy('country_code')
      ->get()
      ->map(function ($item) {
        // Nama negara dari country_code (fallback jika tidak ada)
        return [
          'code' => $item->country_code,
          'name' => $this->getCountryName($item->country_code),
        ];
      });
    });

    return response()->json($countries);
  }

  /**
  * Tampilkan daftar bank untuk suatu negara, dikelompokkan berdasarkan kota.
  */
  public function banks(Request $request, $countryCode) {
    $countryCode = strtoupper($countryCode);
    $search = $request->get("search", "");
    $page = $request->get("page", 1);
    $perPage = 20;

    $cacheKey = config('swiftbank.cache_prefix') . "show_{$countryCode}_{$search}_{$page}_{$perPage}";

    $data = Cache::remember($cacheKey, now()->addDays(), function() use ($countryCode, $search, $page, $perPage) {
      $query = SwiftBank::where('country_code', $countryCode);

      if (!empty($search)) {
        $query->where(function($q) use($search) {
          $q->where("bank_name", "LIKE", "%{$search}%")
          ->orWhere("swift_code", "LIKE", "%{$search}%")
          ->orWhere("city", "LIKE", "%{$search}%")
          ->orWhere("branch", "LIKE", "%{$search}%");
        });
      }

      // Kelompokkan berdasarkan kota
      $banks = $query->orderBy('city')
      ->orderBy('bank_name')
      ->paginate($perPage, ["*"], "page", $page)
      ->appends(["search" => $search])
      ->withQueryString();

      $grouped = $banks->groupBy('city');

      return [
        "banks" => $banks,
        "grouped" => $grouped
      ];
    });

    $banks = $data["banks"];
    $grouped = $data["grouped"];

    $countryName = $this->getCountryName($countryCode);

    return response()->json([
      'country_name' => $countryName,
      'banks' => $banks
    ]);
  }

  /**
  * Helper untuk mendapatkan nama negara dari kode.
  * Bisa menggunakan library atau array statis.
  */
  private function getCountryName($code) {
    $countries = [
      'ID' => 'Indonesia',
      'US' => 'United States',
      'JP' => 'Japan',
      'GB' => 'United Kingdom',
      'AU' => 'Australia',
      'DE' => 'Germany',
      'FR' => 'France',
      'CN' => 'China',
      'IN' => 'India',
      'TH' => 'Thailand',
      'KR' => 'South Korea',
      'CA' => 'Canada',
      'BR' => 'Brazil',
      'RU' => 'Russia',
      'AE' => 'United Arab Emirates',
      'SA' => 'Saudi Arabia',
      'NL' => 'Netherlands',
      'ES' => 'Spain',
      'AT' => 'Austria',
      'BE' => 'Belgium',
      'CH' => 'Switzerland',
      'IT' => 'Italy',
      'MX' => 'Mexico',
      'NG' => 'Nigeria',
      'NO' => 'Norway',
      'PL' => 'Poland',
      'SE' => 'Sweden',
      'TR' => 'Turkey',
      'VE' => 'Venezuela',
    ];
    return $countries[$code] ?? $code;
  }
}