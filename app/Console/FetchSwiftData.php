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

  protected $chunkSize = 1000;

  public function __construct() {
    parent::__construct();
    ini_set('memory_limit', '512M');
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

      // 1. Baca metadata untuk mendapatkan total banks
      $metadata = $this->getMetadata($tempFile);
      $totalBanks = $metadata['total_banks'] ?? 0;
      $this->info("📊 Total banks to import: {$totalBanks}");

      $this->info('📖 Processing JSON stream...');

      // 2. Buat progress bar dengan total banks
      $progressBar = $this->output->createProgressBar($totalBanks);
      $progressBar->start();

      // 3. Proses insert dalam transaksi
      DB::transaction(function () use ($tempFile, $progressBar, $totalBanks) {
        SwiftBank::truncate();

        $buffer = [];
        $processed = 0;

        $items = Items::fromFile($tempFile, [
          'pointer' => '/swift_global/countries',
          'decoder' => new ExtJsonDecoder(true)
        ]);

        foreach ($items as $countryCode => $countryData) {
          if (!isset($countryData['list'])) continue;

          $banks = $countryData['list'];
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
            $processed++;

            if (count($buffer) >= $this->chunkSize) {
              SwiftBank::insert($buffer);
              unset($buffer);
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

        // Pastikan progress bar mencapai 100% (mengoreksi jika ada selisih)
        $remaining = $totalBanks - $progressBar->getProgress();
        if ($remaining > 0) {
          $progressBar->advance($remaining);
        }
      });

      // 4. Selesai, tutup progress bar dan tampilkan pesan sukses
      $progressBar->finish();
      $this->newLine();
      $this->info('🎉 SWIFT bank data imported successfully!');

    } catch (\Exception $e) {
      $this->error('❌ Error: ' . $e->getMessage());
      if ($tempFile) $this->cleanupTempFile($tempFile);
      return 1;
    }

    if ($tempFile) $this->cleanupTempFile($tempFile);
    return 0;
  }

  /**
  * Ambil metadata dari file JSON untuk mendapatkan total banks.
  */
  protected function getMetadata($filePath): array
  {
    $items = Items::fromFile($filePath, [
      'pointer' => '/swift_global/metadata',
      'decoder' => new ExtJsonDecoder(true)
    ]);

    return $items ?? [];
  }
}