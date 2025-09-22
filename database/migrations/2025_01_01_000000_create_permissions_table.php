<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('bitmask');
            $table->string('permissible_type')->nullable();
            $table->unsignedBigInteger('bitmask_next')->nullable();
            $table->unsignedBigInteger('bitmask_disabled')->nullable();
            $table->unsignedBigInteger('bitmask_flush')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
