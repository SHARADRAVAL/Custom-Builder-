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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // Task details
            $table->string('title');
            $table->text('description')->nullable();

            // Task timing
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();

            // User relation
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Task status
            $table->enum('status', ['pending', 'in_progress', 'completed'])
                  ->default('pending');

            // Reminder control
            $table->boolean('reminder_sent')
                  ->default(false)
                  ->comment('Email reminder sent 15 minutes before start');

            // Task lifecycle timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Reapeat Reminder
              $table->enum('repeat_type', ['none', 'daily', 'weekly', 'monthly'])
                  ->default('none');


            // Laravel default timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
