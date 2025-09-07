<?php

use App\Enums\ConferenceDirection;
use App\Models\User;
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
        Schema::create('conferences', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('status')->default('pending');
            $table->foreignIdFor(User::class);
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('direction')->default(ConferenceDirection::Inbound->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conferences');
    }
};
