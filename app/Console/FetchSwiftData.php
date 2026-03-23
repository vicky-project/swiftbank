<?php
namespace Modules\SwiftBank\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\CoreUI\Traits\FileDownloader;
use Modules\SwiftBank\Models\SwiftBank;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use JsonMachine\Items;

class FetchSwiftData extends Command
{
  use FileDownloader;

  protected $signature = 'app:swift';
  protected $description = 'Fetch global SWIFT bank data from JSON using streaming';

  protected $url = 'https://vickyserver.my.id/data/swift_global/swift_global_data.json';
  protected $type = 'swift';
  protected $config = [];

  protected $chunkSize = 1000; // jumlah bank per batch insert

  public function __construct() {
    parent::__construct();
    ini_set('memory_limit', '512M'); // naikkan untuk parsing JSON besar
    $this->config = [
      'command' => $this,
      'max_retries' => 3,
      'http_timeout' => 600,
      'min_file_size' => 1024,
      'retry_delay' => 1000,
      'connect_timeout' => 30,
      'verify_ssl' => true,
    ];
  }

  public function handle() {
    $this->info('🚀 Fetching SWIFT bank data...');

    $tempFile = null;
    try {
      $tempFile = $this->downloadData($this->url, null, true, $this->config);
      $this->info('✅ File downloaded: ' . $tempFile);

      // Hitung total negara untuk progress bar (opsional, karena kita tidak tahu jumlah negara)
      // Kita akan proses langsung dan gunakan progress bar manual.

      $this->info('📖 Processing JSON stream...');
      $items = Items::fromFile($tempFile, [
        'pointer' => '/swift_global/countries',
        'decoder' => new ExtJsonDecoder(true)
      ]);

      DB::transaction(function () use ($items) {
        SwiftBank::truncate(); // bersihkan data lama

        $totalBanks = 0;
        $buffer = [];
        $progressBar = $this->output->createProgressBar(); // progress tanpa total awal

        foreach ($items as $countryCode => $countryData) {
          if (!isset($countryData['list'])) continue;

          $banks = $countryData['list'];
          $progressBar->setMessage("Processing {$countryData['country']} ({$countryCode})");

          foreach ($banks as $bank) {
            $buffer[] = [
              'country_code' => $countryCode,
              'bank_name' => $bank['bank'],
              'city' => $bank['city'] ?? null,
              'branch' => $bank['branch'] ?? null,
              'swift_code' => $bank['swift_code'],
              'created_at' => now(),
              'updated_at' => now(),
            ];
            $totalBanks++;

            // Insert batch jika buffer mencapai chunk size
            if (count($buffer) >= $this->chunkSize) {
              SwiftBank::insert($buffer);
              $buffer = [];
              $progressBar->advance($this->chunkSize);
            }
          }
        }

        // Insert sisa buffer
        if (!empty($buffer)) {
          SwiftBank::insert($buffer);
          $progressBar->advance(count($buffer));
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Total banks inserted: {$totalBanks}");
      });

      $this->info('🎉 SWIFT bank data imported successfully!');
    } catch (\Exception $e) {
      $this->error('❌ Error: ' . $e->getMessage());
      if ($tempFile) $this->cleanupTempFile($tempFile);
      return 1;
    }

    if ($tempFile) $this->cleanupTempFile($tempFile);
    return 0;
  }
}