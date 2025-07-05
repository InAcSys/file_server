<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("fileName");
            $table->string("extension");
            $table->string("url");
            $table->decimal("size", 8, 2);
            $table->uuid("ownerId");
            $table->uuid("tenantId");
            $table->timestamp('created')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
