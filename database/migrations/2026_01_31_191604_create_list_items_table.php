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
        Schema::create('list_items', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignUuid('custom_list_uuid')->constrained('custom_lists', 'uuid')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('completed')->default(false);
            $table->integer('version')->unsigned()->default(1);
            $table->foreignUuid('locked_by')->nullable()->constrained('users', 'uuid');
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_items');
    }
};
