<?php

return [
  'id' => 'swift-bank',
  'name' => 'Swift Bank',
  'description' => 'Kode SWIFT bank di seluruh dunia',
  'icon_class' => 'bi bi-bank2',
  'render_type' => 'iframe',
  'render_config' => [
    'url' => env('APP_URL') . '/apps/swift'
  ]
];