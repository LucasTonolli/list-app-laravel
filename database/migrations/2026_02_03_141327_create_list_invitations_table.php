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
        Schema::create('list_invitations', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignUuid('list_uuid')->constrained('custom_lists', 'uuid')->onDelete('cascade');
            $table->string('token');
            $table->timestamp('expires_at');
            $table->tinyInteger('max_uses')->unsigned()->default(1);
            $table->tinyInteger('uses')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_invitations');
    }
};
