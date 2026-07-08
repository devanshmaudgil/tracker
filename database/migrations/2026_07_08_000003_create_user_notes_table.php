<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->constrained('staff_users')->cascadeOnDelete();

            $table->string('kind', 20); // note | task | reminder
            $table->string('title', 140);
            $table->text('body')->nullable();

            $table->date('due_date')->nullable(); // tasks
            $table->date('reminder_date')->nullable(); // reminders

            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['staff_user_id', 'kind']);
            $table->index(['staff_user_id', 'due_date']);
            $table->index(['staff_user_id', 'reminder_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notes');
    }
};

