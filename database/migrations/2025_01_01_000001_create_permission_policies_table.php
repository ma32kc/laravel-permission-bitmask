<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permission_policies', function (Blueprint $table) {
            $table->id();
            $table->string('permissible_type')->nullable();
            $table->unsignedBigInteger('permissible_id')->nullable();
            $table->string('crud_method');
            $table->unsignedBigInteger('bitmask');
            $table->string('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_policies');
    }
};
