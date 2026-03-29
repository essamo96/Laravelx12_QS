<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // نصوص قصيرة وطويلة
            $table->string('name_ar')->comment("@lang('app.name_ar'), الاسم باللغة العربية");
            $table->string('name_en')->nullable()->comment("@lang('app.name_en'), الاسم باللغة الإنجليزية");
            $table->char('sku', 32)->nullable()->unique()->comment('رمز SKU');
            $table->text('description')->nullable()->comment("@lang('app.description'), وصف تفصيلي");
            $table->mediumText('summary')->nullable()->comment('ملخص');
            $table->longText('content_html')->nullable()->comment('محتوى HTML');

            // مسارات صور (لاختبار رفع الملفات في المولّد)
            $table->string('photo')->nullable()->comment("@lang('app.photo'), صورة رئيسية");
            $table->string('banner_image')->nullable()->comment("@lang('app.banner_image'), صورة بانر");

            // وسوم JSON (لاختبار Tagify)
            $table->json('tags')->nullable()->comment("@lang('app.tags'), وسوم");

            // enum
            $table->enum('kind', ['physical', 'digital', 'service'])->default('physical')->comment('نوع المنتج');

            // أرقام صحيحة وعشرية
            $table->unsignedTinyInteger('priority')->default(0)->comment('أولوية عرض');
            $table->smallInteger('stock_alert')->nullable()->comment('حد تنبيه المخزون');
            $table->integer('stock')->default(0)->comment('المخزون');
            $table->bigInteger('views_count')->default(0)->comment('عدد المشاهدات');
            $table->decimal('price', 10, 2)->default(0.00)->comment("@lang('app.price'), السعر");
            $table->float('weight', 8, 2)->nullable()->comment("@lang('app.weight'), الوزن");
            $table->double('discount_rate', 8, 4)->nullable()->comment('نسبة خصم');

            // قيم منطقية
            $table->boolean('status')->default(true)->comment("@lang('app.status'), حالة نعم/لا");
            $table->boolean('is_featured')->default(false)->comment('منتج مميز');

            // تواريخ وأوقات
            $table->date('available_on')->nullable()->comment('متاح من تاريخ');
            $table->dateTime('sale_ends_at')->nullable()->comment('ينتهي العرض');
            $table->time('daily_offer_at')->nullable()->comment('وقت عرض يومي');
            $table->timestamp('published_at')->nullable()->comment("@lang('app.published_at'), تاريخ النشر");

            // UUID و JSON عام
            $table->uuid('uuid')->nullable()->unique()->comment("@lang('app.uuid'), معرّف UUID");
            $table->json('options')->nullable()->comment("@lang('app.options'), خيارات JSON");

            // علاقة (نفس منطق tests: user_id)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment("@lang('app.user_id'), المستخدم المرتبط");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
