<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Ship\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ship_test_table', static function ($table) {
            $table->id();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ship_test_table');
    }
};
