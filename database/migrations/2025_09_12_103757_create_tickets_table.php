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
        Schema::create('tickets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('nik');
        $table->foreignId('department_id')->constrained()->onDelete('cascade');
        $table->string('ticket_number')->unique();
        $table->dateTime('open_time');
        $table->dateTime('close_time');
        $table->enum('priority_level', ['low', 'medium', 'high'])->default('low');
        $table->enum('category', ['software', 'hardware', 'network', 'other'])->default('hardware');
        $table->longText('description');
        $table->enum('type_device', ['desktop', 'laptop', 'printer', 'other'])->nullable();
        $table->enum('operation_system', ['windows', 'macos', 'linux', 'other'])->nullable();
        $table->string('software_or_application')->nullable();
        $table->longText('error_message')->nullable();
        $table->longText('step_taken')->nullable();
        // $table->json('attachment')->nullable();
        $table->enum('ticket_status', ['on_progress','resolved', 'callback', 'monitored', 'other'])->default('on_progress');
        // $table->timestamps();
        $table->dateTime('created_at')->nullable();
        $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
