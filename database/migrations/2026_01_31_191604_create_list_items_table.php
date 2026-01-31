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
            $table->foreignId('list_id')->constrained();
            $table->string('name');
            $table->text('description');
            $table->boolean('completed')->default(false);
            $table->interger('version')->default(1);
            $table->foreignId('locked_by')->nullable()->constrained('users');
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
