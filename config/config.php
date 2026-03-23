<?php

return [
  'name' => 'SwiftBank',
  "hook" => [
    "enabled" => env("SWIFTBANK_HOOK_ENABLED", true),
    "service" => \Modules\CoreUI\Services\UIService::class,
    "name" => "main-apps",
  ],
];