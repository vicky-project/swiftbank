<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up() {
    Schema::create('swift_banks', function (Blueprint $table) {
      $table->id();
      $table->string('country_code', 2);
      $table->string('bank_name');
      $table->string('city')->nullable();
      $table->string('branch')->nullable();
      $table->string('swift_code', 11)->unique();
      $table->timestamps();

      $table->index('country_code');
      $table->index('swift_code');
    });
  }

  public function down() {
    Schema::dropIfExists('swift_banks');
  }
};