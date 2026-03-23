<?php

namespace Modules\SwiftBank\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\SwiftBank\Models\SwiftBank;
use Illuminate\Http\Request;

class SwiftBankController extends Controller
{
  /**
  * Tampilkan daftar negara yang tersedia.
  */
  public function index() {
    // Ambil daftar negara unik (country_code dan nama negara)
    $countries = SwiftBank::select('country_code')
    ->distinct()
    ->orderBy('country_code')
    ->get()
    ->map(function ($item) {
      // Nama negara dari country_code (fallback jika tidak ada)
      $name = $this->getCountryName($item->country_code);
      return [
        'code' => $item->country_code,
        'name' => $name,
      ];
    });

    return view('swiftbank::index', compact('countries'));
  }

  /**
  * Tampilkan daftar bank untuk suatu negara, dikelompokkan berdasarkan kota.
  */
  public function show($countryCode) {
    $countryCode = strtoupper($countryCode);
    $banks = SwiftBank::where('country_code', $countryCode)
    ->orderBy('city')
    ->orderBy('bank_name')
    ->get();

    // Kelompokkan berdasarkan kota
    $grouped = $banks->groupBy('city');

    $countryName = $this->getCountryName($countryCode);

    return view('swiftbank::show', compact('countryCode', 'countryName', 'grouped'));
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