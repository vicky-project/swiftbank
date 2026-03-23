<?php

return [
  'name' => 'SwiftBank',
  "cache_prefix" => "swift_banks_",
  "hook" => [
    "enabled" => env("SWIFTBANK_HOOK_ENABLED", true),
    "service" => \Modules\CoreUI\Services\UIService::class,
    "name" => "main-apps",
  ],
];