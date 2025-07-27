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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('more_desc');
            $table->date('footer_date')->nullable();
            $table->string('footer_text')->nullable();
            $table->string('logo')->nullable();
            $table->string('version')->nullable();
            $table->string('tags')->nullable();
            $table->string('mobile')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('market_situation')->nullable();
            $table->string('currency', 20);
            $table->tinyInteger('close_status')->nullable()->default(0)->comment('0 مفتوح - 1 مغلق');
            $table->string('close_text', 200)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
