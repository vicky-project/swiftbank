<?php

namespace Modules\SwiftBank\Models;

use Illuminate\Database\Eloquent\Model;

class SwiftBank extends Model
{
  protected $fillable = [
    'country_code',
    'bank_name',
    'city',
    'branch',
    'swift_code'
  ];
}