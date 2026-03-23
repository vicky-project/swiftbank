<?php
namespace Modules\SwiftBank\Telegram;

use Illuminate\Support\Facades\Log;
use Modules\SwiftBank\Models\SwiftBank;
use Modules\Telegram\Services\Support\TelegramApi;
use Modules\Telegram\Services\Handlers\Callbacks\BaseCallbackHandler;

class CallbackHandler extends BaseCallbackHandler
{
  public function __construct(
    TelegramApi $telegramApi,
  ) {
    parent::__construct($telegramApi);
  }

  public function getModuleName(): string
  {
    return "swiftbank";
  }

  public function getName(): string
  {
    return "Swift bank code callback handler";
  }

  public function handle(array $data, array $context): array
  {
    try {
      return $this->handleCallbackWithAutoAnswer(
        $context,
        $data,
        fn($data, $context) => $this->processCallback($data, $context),
      );
    } catch (\Exception $e) {
      Log::error("Failed to handle callback of swiftbank", [
        "message" => $e->getMessage(),
        "trace" => $e->getTraceAsString(),
      ]);

      return [
        "status" => "callback_failed",
        "answer" => $e->getMessage()
      ];
    }
  }

  private function processCallback(array $data, array $context): array
  {
    try {
      $entity = $data["entity"];
      $action = $data["action"];
      $id = $data["id"] ?? null;
      $params = $data["params"] ?? [];
      Log::debug("Proses callback swift bank.", ["action" => $action, "entity" => $entity, "id" =>$id,"params" => $params]);

      switch ($entity) {
        case "swiftbank":
          return $this->handleObject($action, $id, $params);

        default:
          return [];
      }
    } catch (\Exception $e) {
      throw $e;
    }
  }

  private function handleObject(string $action, int $id, array $params): array
  {
    switch ($action) {
    case "country":
      Log::debug("Get country action of swift bank", ["action" => $action, "id" => $id, "params" => $params]);
      return [];

    case "content":
      $contents = $this->objectcodeService->getContentById($id);
      if (!$contents) {
        return ["success" => false,
          "status" => "swiftbank_content_failed"];
      }

      $message = "*{$contents["name"]}*\n\n";

      foreach ($contents["contents"] as $content) {
        $message .= "● `{$content->code}` - {$content->description}\n";
      }

      $message .= "\n\nnote: _tekan kode untuk menyalin_";

      return [
        "success" => true,
        "status" => "swiftbank_content_sent",
        "edit_message" => ["text" => $message,
          "parse_mode" => "MarkdownV2"],
      ];

    default:
      return ["success" => false,
        "status" => "no_action_found"];
    }
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