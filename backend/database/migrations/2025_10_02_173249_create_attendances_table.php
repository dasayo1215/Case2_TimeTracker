<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('work_date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->enum('status', ['normal', 'pending', 'approved'])->default('normal');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            // 同じ日の重複禁止
            $table->unique(['user_id', 'work_date']);
        });

        DB::statement("
            ALTER TABLE attendances
            ADD CONSTRAINT chk_attendances_status
            CHECK (
                (status = 'normal'   AND submitted_at IS NULL AND approved_at IS NULL)
                OR (status = 'pending'  AND submitted_at IS NOT NULL AND remarks IS NOT NULL)
                OR (status = 'approved' AND submitted_at IS NOT NULL AND approved_at IS NOT NULL AND remarks IS NOT NULL)
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
