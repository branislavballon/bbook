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
        Schema::create('likes', function (Blueprint $table) {
            $table->id();

            // Both keys cascade, and both are declared inside the create
            // because SQLite cannot add a foreign key to an existing table.
            // A deleted post takes its likes with it; so does a deleted person.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // A second like from the same person is impossible at the storage
            // layer rather than by the controller remembering to check.
            $table->unique(['user_id', 'post_id']);

            // The unique index leads with user_id, so it cannot serve the
            // per-post aggregate every feed query asks for.
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
