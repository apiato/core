<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('likables', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('like_id');
            $table->unsignedBigInteger('likable_id');
            $table->string('likable_type');
            $table->timestamps();
            $table->unique(['like_id', 'likable_id', 'likable_type'], 'likables_ids');
        });
    }
};
