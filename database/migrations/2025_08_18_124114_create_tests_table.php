<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id();

            // نصوص قصيرة وطويلة
            $table->string('name_ar')->comment("@lang('app.name_ar'), الاسم باللغة العربية");
            $table->string('name_en')->nullable()->comment("@lang('app.name_en'), الاسم باللغة الإنجليزية");
            $table->text('description')->nullable()->comment("@lang('app.description'), وصف تفصيلي ");

            // أرقام صحيحة وعشرية
            $table->integer('age')->default(0)->comment("@lang('app.age'), رقم صحيح");
            $table->bigInteger('big_number')->nullable()->comment("@lang('app.big_number'), رقم كبير");
            $table->decimal('price', 8, 2)->default(0.00)->comment("@lang('app.price'), سعر عشري");
            $table->float('weight', 8, 2)->nullable()->comment("@lang('app.weight'), وزن عشري");

            // القيم المنطقية
            $table->boolean('status')->default(true)->comment("@lang('app.status'), حالة نعم/لا");

            // تواريخ وأوقات
            $table->date('birth_date')->nullable()->comment("@lang('app.birth_date'), تاريخ الميلاد");
            $table->datetime('published_at')->nullable()->comment("@lang('app.published_at'), تاريخ  النشر");
            $table->timestamp('last_login_at')->nullable()->comment("@lang('app.last_login_at'), تاريخ آخر تسجيل دخول");

            // UUID و JSON
            $table->uuid('uuid')->nullable()->comment("@lang('app.uuid'), معرّف  فريد UUID");
            $table->json('options')->nullable()->comment("@lang('app.options'), خيارات بصيغة JSON");

            // علاقة مفتاحية
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment("@lang('app.user_id'), المستخدم المرتبط");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
