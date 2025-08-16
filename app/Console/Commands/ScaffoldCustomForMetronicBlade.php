<?php

namespace App\Console\Commands; // تحديد النيم سبيس للمكان الذي يوجد فيه هذا الكلاس

use Illuminate\Console\Command; // استيراد كلاس الأوامر من لارافيل
use Illuminate\Support\Facades\DB; // استيراد الفاساد الخاص بالتعامل مع قاعدة البيانات
use Illuminate\Support\Facades\File; // استيراد الفاساد الخاص بالتعامل مع الملفات والمجلدات
use Illuminate\Support\Str; // استيراد الكلاس الخاص بالتعامل مع النصوص (تحويل صيغة، إلخ)

class ScaffoldCustomForMetronicBlade extends Command
{
    // تحديد الأمر الذي يمكن استدعاؤه من خلال Artisan مع المعاملات المطلوبة
    protected $signature = 'make:essam {name} {table}';

    // وصف الأمر ليظهر في قائمة أوامر Artisan
    protected $description = 'Scaffold a new model, migration, controller, and views with custom code';

    // الدالة الرئيسية التي تُنفذ عند تشغيل الأمر
    public function handle()
    {
        $name = $this->argument('name'); // جلب اسم الموديل من الأمر
        $modelName = Str::studly($name); // تحويل الاسم إلى صيغة StudlyCase (مثل UserProfile)
        $modelLowerCase = Str::snake($modelName); // تحويل الاسم إلى صيغة snake_case (مثل user_profile)

        $table = $this->argument('table'); // جلب اسم الجدول من الأمر
        $tableName = $table; // تخزين اسم الجدول في متغير (يمكن استخدامه مباشرة)

        $dbName = env('DB_DATABASE'); // جلب اسم قاعدة البيانات من ملف .env

        // تنفيذ استعلام SQL لجلب أسماء الأعمدة وأنواع البيانات من جدول معين مع استثناء بعض الأعمدة
        $columns = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE, COLUMN_COMMENT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = ?
            AND TABLE_SCHEMA = ?
            AND COLUMN_NAME NOT IN ('id', 'created_at', 'updated_at')
        ", [$tableName, $dbName]);


        $fillable = '['; // بداية نص يحتوي على أسماء الأعمدة لوضعها في fillable
        $dataTable = ""; // نص مبدئي لتخزين أعمدة الجدول الخاصة بـ DataTables
        $frontDataTable = ""; // نص مبدئي لتخزين أعمدة الجدول للواجهة الأمامية
        $modalTemplate = ''; // نص مبدئي لتخزين عناصر النموذج في المودال
        $tableHead = ""; // نص مبدئي لتخزين رؤوس الأعمدة للجدول في HTML
        $RequestRoles = ""; // نص مبدئي لتخزين قواعد التحقق (Validation rules)

        // حلقة على كل عمود في الجدول
        foreach ($columns as $column) {
            $fillable .= '"' . $column->COLUMN_NAME . '",'; // إضافة اسم العمود لقائمة fillable

            // إعداد كود إضافة العمود في DataTables
            $dataTable .= '->addColumn(' . "/'" . $column->COLUMN_NAME . "/'" . ', function ($row) {
                                        return $row->' . $column->COLUMN_NAME . ';
                                })';

            // إعداد كود العمود للواجهة الأمامية
            $frontDataTable .= '
                                {data: "' . $column->COLUMN_NAME . '"},

                                ';

            // إعداد حقل إدخال للمودال الخاص بالإضافة/التعديل
            $modalTemplate .= '`
                <div class="form-floating mb-9 row ">
                    <div class="col">
                        <label class="p-2 required">' . $column->COLUMN_NAME . '</label>
                        <input type="text" value="{{  $info->' . $column->COLUMN_NAME . ' ?? old("' . $column->COLUMN_NAME . '",$info->' . $column->COLUMN_NAME . ') }}" name="' . $column->COLUMN_NAME . '"
                            class="form-control" />
                    </div>
                </div>
            `';

            $tableHead .= '<th >' . $column->COLUMN_NAME . '</th>'; // إضافة اسم العمود كرأس جدول
            $RequestRoles .= '"' . $column->COLUMN_NAME . '" => "required",'; // قاعدة تحقق بأن الحقل مطلوب
        }

        $fillable = rtrim($fillable, ',') . '];'; // إزالة الفاصلة الأخيرة وإغلاق المصفوفة

        /////////////////////////////////////   إنشاء الموديل //////////////////
        $modelTemplate = <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$modelName} extends Model
{
    use HasFactory;

    protected \$fillable = {$fillable}

    function getSearch(\$name = null) {
        return \$this->where(function (\$query) use (\$name) {
            if (\$name != "") {
                \$query->where('name', 'LIKE', '%' . \$name . '%');
            }
        })->get();
    }
}
EOT;

        $modelDir = app_path("Models"); // مسار مجلد الموديلات

        if (!File::exists($modelDir)) { // إذا لم يوجد المجلد
            File::makeDirectory($modelDir, 0777, true); // أنشئه مع التصاريح
        }

        File::put("{$modelDir}/{$modelName}.php", $modelTemplate); // إنشاء ملف الموديل
        $this->info("Model for {$name} created successfully."); // رسالة نجاح

        // إنشاء الكنترولر
        $this->createController($modelName, $RequestRoles);

        // مسارات قوالب الواجهات (stubs)
        $templatePath = resource_path('stubs/views');
        $viewsPath = resource_path("views/admin/{$modelLowerCase}");
        $templatePath1 = resource_path('stubs/views/parts');
        $viewsPath1 = resource_path("views\admin\\" . $modelLowerCase . "\parts");

        // إنشاء مجلد الواجهات إذا لم يكن موجودًا
        if (!File::exists($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
            File::makeDirectory($viewsPath . '\parts', 0755, true);
        }

        // نسخ قوالب الواجهات إلى المجلد الجديد
        $this->copyViewFiles($templatePath, $templatePath1, $viewsPath, $viewsPath1, $name, $tableHead, $frontDataTable, $modalTemplate);

        $this->info("Scaffolded {$name} model, migration, controller, and views with custom code"); // رسالة نجاح نهائية
    }
}
