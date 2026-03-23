<?php
namespace Modules\SwiftBank\Telegram;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Modules\SwiftBank\Models\SwiftBank;
use Modules\Telegram\Services\Support\InlineKeyboardBuilder;
use Modules\Telegram\Services\Support\TelegramApi;
use Modules\Telegram\Services\Handlers\Commands\BaseCommandHandler;

class SwiftBankCommand extends BaseCommandHandler
{
  protected InlineKeyboardBuilder $inlineKeyboard;

  public function __construct(
    TelegramApi $telegram,
    InlineKeyboardBuilder $inlineKeyboard,
  ) {
    parent::__construct($telegram);
    $this->inlineKeyboard = $inlineKeyboard;
  }

  public function getName(): string
  {
    return "swiftbank";
  }

  public function getDescription(): string
  {
    return "Show swift bank code list";
  }

  /*
	 * Handle command
	 */
  protected function processCommand(
    int $chatId,
    string $text,
    ?string $username = null,
    array $params = [],
  ): array {
    try {
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

      $messages = "*List Country*\n\nPilih negara:\n";

      $keyboards = $this->prepareKeyboard(collect($countries));

      return [
        "status" => "swiftbank_sent",
        "count" => count($countries),
        "send_message" => [
          "text" => $messages,
          "parse_mode" => "MarkdownV2",
          "reply_markup" => ["inline_keyboard" => $keyboards],
        ],
      ];
    } catch (\Exception $e) {
      Log::error("Failed to get swiftbank country list", [
        "message" => $e->getMessage(),
        "trace" => $e->getTraceAsString(),
      ]);

      return [
        "status" => "swiftbank_failed",
        "message" => $e->getMessage(),
        "send_message" => ["text" => $e->getMessage()],
      ];
    }
  }

  private function prepareKeyboard(Collection $data): array
  {
    $this->inlineKeyboard->setModule("swiftbank");
    $this->inlineKeyboard->setEntity("swiftbank");

    $items = $data
    ->map(function ($item) {
      return [
        "text" => $item->name,
        "callback_data" => [
          "value" => $item['code'],
          "action" => "content",
        ],
      ];
    })
    ->toArray();

    return $this->inlineKeyboard->grid($items, 2);
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