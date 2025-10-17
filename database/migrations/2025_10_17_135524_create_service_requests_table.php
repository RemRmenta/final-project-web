<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // service_worker id
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['pending','in_progress','completed','cancelled'])->default('pending');
            $table->enum('priority', ['low','medium','high'])->default('medium');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
