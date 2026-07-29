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
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();

            // Both sides cascade: declaring it on one column only would leave
            // constraint-violating rows behind when the person on the other
            // side is deleted. Both are declared inside the create, because
            // SQLite cannot add a foreign key to an existing table.
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('addressee_id')->constrained('users')->cascadeOnDelete();
            $table->string('status');
            $table->timestamps();

            // Catches the exact duplicate only. The reverse-direction request
            // is not a database concern and is guarded in StoreFriendshipRequest.
            $table->unique(['requester_id', 'addressee_id']);
            $table->index(['addressee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
