<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('event'); // e.g. request, model.created, auth.login
            $table->string('action')->nullable(); // route name or logical action

            $table->string('route')->nullable();
            $table->string('method', 10)->nullable();
            $table->text('url')->nullable();

            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();

            $table->unsignedSmallInteger('status_code')->nullable();

            $table->json('request')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['event', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

