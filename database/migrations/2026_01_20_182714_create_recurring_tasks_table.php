<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recurring_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade'); // base task
            $table->enum('repeat_type', ['daily','weekly','monthly']);
            $table->time('daily_time')->nullable();
            $table->time('weekly_time')->nullable();
            $table->json('week_days')->nullable();
            $table->date('monthly_date')->nullable();
            $table->time('monthly_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_tasks');
    }
};
