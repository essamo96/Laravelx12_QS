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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto_increment
            $table->string('name', 191);
            $table->string('username', 191)->nullable();
            $table->string('email', 191); // لا يوجد unique في الجدول القديم
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 191);
            $table->string('created_by', 255)->nullable();
            $table->string('role', 255)->nullable();
            $table->tinyInteger('status')->default(1); // لا يقبل null
            $table->rememberToken(); // varchar(100), nullable
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at
            $table->index('email'); // موجود كـ Index في الجدول القديم
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
