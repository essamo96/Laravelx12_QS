<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ScaffoldUI extends Command
{
    protected $signature = 'make:essam2 {name} {table}';
    protected $description = 'Scaffold a new model, controller, request, and views with custom code';

    public function handle()
    {
        $name = $this->argument('name');
        $modelName = Str::studly($name);
        $modelLowerCase = Str::snake($modelName);
        $tableName = $this->argument('table');

        // Note 1: Get table columns safely.
        $dbName = config('database.connections.mysql.database');
        $columns = DB::table('INFORMATION_SCHEMA.COLUMNS')
            ->where('TABLE_SCHEMA', $dbName)
            ->where('TABLE_NAME', $tableName)
            ->whereNotIn('COLUMN_NAME', ['id', 'created_at', 'updated_at'])
            ->select('COLUMN_NAME', 'DATA_TYPE', 'IS_NULLABLE', 'COLUMN_COMMENT')
            ->get();

        if ($columns->isEmpty()) {
            $this->error("Table '{$tableName}' not found or has no scannable columns.");
            return;
        }

        list($fillable, $dataTable, $frontDataTable, $modalTemplate, $tableHead, $requestRulesArray) = $this->prepareScaffoldData($columns);

        $this->updateTranslations($tableName, $columns);
        $this->generateModel($modelName, $fillable, $columns);
        $this->generateRequest($modelName, $modelLowerCase, $requestRulesArray);
        $this->generateController($modelName, $modelLowerCase, $columns);
        $this->generateViews($modelName, $modelLowerCase, $tableHead, $frontDataTable, $modalTemplate, $columns);
        $this->generateRoutes($modelName, $modelLowerCase, $tableName);

        $this->info("Scaffolded {$name} model, controller, form request, and views for table {$tableName} successfully.");
    }

    // protected function prepareScaffoldData($columns)
    // {
    //     $fillable = [];
    //     $dataTable = '';
    //     $frontDataTable = '';
    //     $modalTemplate = '';
    //     $tableHead = '';
    //     $rowBuffer = '';
    //     $fieldCount = 0;
    //     $requestRulesArray = [];

    //     foreach ($columns as $column) {
    //         $colName = $column->COLUMN_NAME;
    //         $type = strtolower($column->DATA_TYPE);
    //         $comment = $column->COLUMN_COMMENT ?: ucfirst($colName);

    //         $fillable[] = $colName;
    //         $dataTable .= '->addColumn("' . $colName . '", fn($row) => $row->' . $colName . ')';

    //         // ترجمات
    //         $translations[$colName]['ar'] = $comment;
    //         $translations[$colName]['en'] = $comment;
    //         if (strpos($comment, ',') !== false) {
    //             [$enText, $arText] = explode(',', $comment, 2);
    //             $translations[$colName]['en'] = trim($enText);
    //             $translations[$colName]['ar'] = trim($arText);
    //         }

    //         // حقول و Rules
    //         list($fieldTemplate, $rule) = $this->getFieldTemplateAndRule($colName, $type);

    //         if (in_array($type, ['int', 'bigint', 'decimal', 'float', 'double', 'varchar', 'char', 'text', 'date', 'datetime', 'timestamp']) || Str::endsWith($colName, '_id')) {
    //             $rowBuffer .= $fieldTemplate;
    //             $fieldCount++;
    //         } else {
    //             $modalTemplate .= $fieldTemplate;
    //         }

    //         if ($fieldCount == 2) {
    //             $modalTemplate .= '<div class="row mb-5">' . $rowBuffer . '</div>';
    //             $rowBuffer = '';
    //             $fieldCount = 0;
    //         }

    //         // ✅ فقط الأعمدة غير nullable نضيفها في الجدول والداتا تيبل
    //         if ($column->IS_NULLABLE === 'NO') {
    //             $tableHead      .= '<th>@lang(\'app.' . $colName . '\')</th></n>';
    //             $frontDataTable .= '{ data: "' . $colName . '" },' . "\n";
    //         }

    //         $requestRulesArray[$colName] = $rule;
    //     }

    //     if ($rowBuffer != '') {
    //         $modalTemplate .= '<div class="row mb-5">' . $rowBuffer . '</div>';
    //     }

    //     return [
    //         "'" . implode("',\n'", $fillable) . "'",
    //         $dataTable,
    //         $frontDataTable,
    //         $modalTemplate,
    //         $tableHead,
    //         $requestRulesArray
    //     ];
    // }

    protected function prepareScaffoldData($columns)
    {
        $fillable = [];
        $dataTable = '';
        $frontDataTable = '';
        $modalTemplate = '';
        $tableHead = '';
        $requestRulesArray = [];

        $requiredFields = [];
        $optionalFields = [];
        $lastFields = [];

        foreach ($columns as $column) {
            $colName    = $column->COLUMN_NAME;
            $type       = strtolower($column->DATA_TYPE);
            $isNullable = ($column->IS_NULLABLE === 'YES');
            $comment    = $column->COLUMN_COMMENT ?: ucfirst($colName);

            $fillable[] = $colName;
            $dataTable .= '->addColumn("' . $colName . '", fn($row) => $row->' . $colName . ')';

            // الترجمات
            $translations[$colName]['ar'] = $comment;
            $translations[$colName]['en'] = $comment;
            if (strpos($comment, ',') !== false) {
                [$enText, $arText] = explode(',', $comment, 2);
                $translations[$colName]['en'] = trim($enText);
                $translations[$colName]['ar'] = trim($arText);
            }

            // نحصل على القالب + الفاليديشن
            list($fieldTemplate, $rule) = $this->getFieldTemplateAndRule($colName, $type, $isNullable);
            $requestRulesArray[$colName] = $rule;

            // التوزيع حسب نوع الحقل
            if (preg_match('/status|is_user/i', $colName) || preg_match('/(disc|description|notes)/i', $colName)) {
                $lastFields[] = $fieldTemplate; // آخر شيء
            } elseif (!$isNullable) {
                $requiredFields[] = $fieldTemplate; // مطلوب
            } else {
                $optionalFields[] = $fieldTemplate; // غير مطلوب
            }

            // فقط الأعمدة المطلوبة تضاف للـ TableHead و FrontDataTable
            if (!$isNullable) {
                $tableHead      .= '<th>@lang(\'app.' . $colName . '\')</th></n>';
                $frontDataTable .= '{ data: "' . $colName . '" },' . "\n";
            }
        }

        // ✅ معالجة حالة الحقل الفردي
        if (count($requiredFields) % 2 != 0 && !empty($optionalFields)) {
            // ضيف أول optional ليكمل الصف الأخير من required
            $requiredFields[] = array_shift($optionalFields);
        }

        // ترتيب عرض الحقول داخل النموذج
        $modalTemplate .= $this->buildRowTemplate($requiredFields);
        $modalTemplate .= $this->buildRowTemplate($optionalFields);
        $modalTemplate .= $this->buildRowTemplate($lastFields, true); // في الآخر

        return [
            "'" . implode("',\n'", $fillable) . "'",
            $dataTable,
            $frontDataTable,
            $modalTemplate,
            $tableHead,
            $requestRulesArray
        ];
    }


    protected function buildRowTemplate($fields, $fullRow = false)
    {
        $html = '';
        $buffer = [];

        foreach ($fields as $field) {
            $buffer[] = $field;
            if (count($buffer) == 2) {
                $html .= '<div class="row mb-5">' . implode('', $buffer) . '</div>';
                $buffer = [];
            }
        }

        // لو باقي حقل واحد
        if (!empty($buffer)) {
            $html .= '<div class="row mb-5">' . implode('', $buffer) . '</div>';
        }

        return $html;
    }

    protected function getFieldTemplateAndRule($colName, $type, $isNullable = false)
    {
        $fieldTemplate = '';
        $rule = '';

        $requiredClass = $isNullable ? '' : 'required';
        $requiredRule  = $isNullable ? 'nullable' : 'required';

        // status / is_user
        if (preg_match('/status|is_user/i', $colName)) {
            $fieldTemplate = '
            <div class="form-floating mb row">
                <div class="col">
                    <label class="p-2">@lang(\'app.' . $colName . '\')</label>
                    <label class="form-check form-switch">
                        <?php $data = $info ? $info->' . $colName . ' : old("' . $colName . '"); ?>
                        <input class="form-check-input" name="' . $colName . '" type="checkbox" value="1"
                            {{ $data == 1 ? "checked=\"checked\"" : "" }}>
                    </label>
                </div>
            </div>';
            $rule = "'in:0,1'";

            // الوصف / الملاحظات
        } elseif (preg_match('/(disc|description|notes)/i', $colName)) {
            $fieldTemplate = '
            <div class="form-floating mb-9 row">
                <div class="fv-row mb-10 col-12">
                    <label class="fw-semibold fs-6 mb-2" for="' . $colName . '">@lang(\'app.' . $colName . '\')</label>
                    <textarea name="' . $colName . '" id="' . $colName . '" class="form-control form-control-solid">{{ $info ? $info->' . $colName . ' : old("' . $colName . '") }}</textarea>
                </div>
            </div>';
            $rule = "'nullable|string'";

            // أرقام
        } elseif (in_array($type, ['int', 'bigint', 'decimal', 'float', 'double'])) {
            $fieldTemplate = '
            <div class="col-md-6 fv-row fv-plugins-icon-container">
                <label class="' . $requiredClass . ' fs-5 fw-semibold mb-2">@lang(\'app.' . $colName . '\')</label>
                <input type="number" class="form-control form-control-solid" name="' . $colName . '" value="{{ $info ? $info->' . $colName . ' : old("' . $colName . '") }}">
            </div>';
            $rule = "'$requiredRule|numeric'";

            // تواريخ
        } elseif (in_array($type, ['date', 'datetime', 'timestamp'])) {
            $fieldTemplate = '
            <div class="col-md-6 fv-row fv-plugins-icon-container">
                <label class="' . $requiredClass . ' fs-5 fw-semibold mb-2">@lang(\'app.' . $colName . '\')</label>
                <input type="date" class="form-control form-control-solid" name="' . $colName . '" id="' . $colName . '" value="{{ $info ? $info->' . $colName . ' : old("' . $colName . '") }}">
            </div>';
            $rule = "'$requiredRule|date'";

            // العلاقات (_id)
        } elseif (Str::endsWith($colName, '_id')) {
            $related = Str::singular(Str::before($colName, '_id'));
            $fieldTemplate = '
            <div class="col-md-6 fv-row fv-plugins-icon-container">
                <label class="p-2 ' . $requiredClass . '">@lang(\'app.' . $colName . '\')</label>
                <select class="form-select form-select-solid" data-control="select2" aria-label="Select example" name="' . $colName . '">
                    <option value="0">@lang(\'app.choose\')</option>
                    <?php $data = $info ? $info->' . $colName . ' : old("' . $colName . '"); ?>
                    @foreach ($' . $related . 's as $item)
                        <option value="{{ $item->id }}" {{ $data == $item->id ? "selected" : "" }}>
                            {{ $item->{"name_" . app()->getLocale()} ?? $item->name ?? "" }}
                        </option>
                    @endforeach
                </select>
            </div>';
            $rule = "'$requiredRule|exists:" . Str::plural($related) . ",id'";

            // نصوص (افتراضي)
        } else {
            $fieldTemplate = '
            <div class="col-md-6 fv-row fv-plugins-icon-container">
                <label class="' . $requiredClass . ' fs-5 fw-semibold mb-2">@lang(\'app.' . $colName . '\')</label>
                <input type="text" class="form-control form-control-solid" name="' . $colName . '" value="{{ $info ? $info->' . $colName . ' : old("' . $colName . '") }}">
            </div>';
            $rule = "'$requiredRule|string|max:255'";
        }

        return [$fieldTemplate, $rule];
    }


    // Note 4: A clear and specific method for generating field rules


    // Note 5: Dedicated method for generating the Model file
    protected function generateModel($modelName, $fillable, $columns)
    {
        // بناء كود العلاقات
        $relationsCode = '';
        foreach ($columns as $col) {
            if (\Illuminate\Support\Str::endsWith($col->COLUMN_NAME, '_id')) {
                $relatedModel = \Illuminate\Support\Str::studly(\Illuminate\Support\Str::before($col->COLUMN_NAME, '_id'));
                $relationName = \Illuminate\Support\Str::camel(\Illuminate\Support\Str::before($col->COLUMN_NAME, '_id'));
                $relationsCode .= <<<REL
                /**
                 * Relationship with {$relatedModel}
                 */
                public function {$relationName}()
                {
                    return \$this->belongsTo({$relatedModel}::class);
                }

            REL;
            }
        }

        $modelTemplate = <<<EOT
            <?php

            namespace App\Models;

            use Illuminate\Database\Eloquent\Factories\HasFactory;
            use Illuminate\Database\Eloquent\Model;

            class {$modelName} extends Model
            {
                use HasFactory;

                protected \$fillable = [{$fillable}];

                public function getSearch(\$name = null)
                {
                    return \$this->where(function (\$query) use (\$name) {
                        if (\$name != "") {
                            \$query->where('name', 'LIKE', '%' . \$name . '%');
                        }
                    })->get();
                }

            {$relationsCode}
            }
            EOT;

        $modelDir = app_path("Models");
        if (!File::exists($modelDir)) {
            File::makeDirectory($modelDir, 0777, true);
        }
        File::put("{$modelDir}/{$modelName}.php", $modelTemplate);
    }


    // Note 6: Dedicated method for generating the Form Request file
    protected function generateRequest($modelName, $modelLowerCase, $requestRulesArray)
    {
        // تعديل القواعد حسب نوع العمود (_ar, _en, nullable)
        $processedRules = [];
        foreach ($requestRulesArray as $field => $rule) {
            // إذا كان الحقل يحتوي _ar
            if (strpos($field, '_ar') !== false) {
                $processedRules[$field] = "regex:/^(?!\d)[\p{Arabic}0-9 ]+$/u|min:3" . (str_contains($rule, 'nullable') ? '|nullable' : '|required');
            }
            // إذا كان الحقل يحتوي _en
            elseif (strpos($field, '_en') !== false) {
                $processedRules[$field] = "regex:/^(?!\d)[A-Za-z0-9 ]+$/|min:3" . (str_contains($rule, 'nullable') ? '|nullable' : '|required');
            }
            // أي حقل آخر
            else {
                $processedRules[$field] = (str_contains($rule, 'nullable') ? 'nullable|' : 'required|') . 'string|min:3';
            }
        }

        // القواعد للإضافة
        $rulesString = implode(",\n\t\t\t\t", array_map(fn($key, $val) => "'{$key}' => '{$val}'", array_keys($processedRules), $processedRules));

        // القواعد للتعديل (استبدال required بـ nullable تلقائياً ما عدا status و is_user)
        $editRulesString = implode(",\n\t\t\t\t", array_map(function ($key, $val) {
            return preg_match('/status|is_user/i', $key)
                ? "'{$key}' => '{$val}'"
                : "'{$key}' => '" . str_replace('required', 'nullable', $val) . "'";
        }, array_keys($processedRules), $processedRules));

        $requestTemplate = <<<EOT
                <?php

                namespace App\Http\Requests\Admin;

                use Illuminate\Foundation\Http\FormRequest;

                class {$modelName}Request extends FormRequest
                {
                    public function authorize(): bool
                    {
                        return true;
                    }

                    public function rules(): array
                    {
                        \$isUpdate = \$this->route('id') !== null;
                        if (\$isUpdate) {
                            return [
                                {$editRulesString}
                            ];
                        }

                        return [
                            {$rulesString}
                        ];
                    }
                }
                EOT;

        $requestDir = app_path("Http/Requests/Admin");
        if (!File::exists($requestDir)) {
            File::makeDirectory($requestDir, 0777, true);
        }
        File::put("{$requestDir}/{$modelName}Request.php", $requestTemplate);
    }

    // Note 7: Dedicated method for generating the Controller
    protected function generateController($modelName, $modelLowerCase, $columns)
    {
        // تحويل اسم المودل إلى الجمع بشكل ذكي
        $modelPlural = $this->makePlural($modelName);
        $modelLowerPlural = strtolower($modelPlural);

        $controllerName = "{$modelPlural}Controller";
        $requestName = "{$modelName}Request";

        // بناء كود العلاقات + use statements
        $relationsCode = '';
        $useStatements = [];

        foreach ($columns as $col) {
            if (\Illuminate\Support\Str::endsWith($col->COLUMN_NAME, '_id')) {
                $relatedModel = \Illuminate\Support\Str::studly(\Illuminate\Support\Str::before($col->COLUMN_NAME, '_id'));
                $relatedPlural = \Illuminate\Support\Str::plural(\Illuminate\Support\Str::camel(\Illuminate\Support\Str::before($col->COLUMN_NAME, '_id')));

                // توليد كود العلاقة
                $relationsCode .= "        parent::\$data['{$relatedPlural}'] = {$relatedModel}::all();\n";

                // إضافة use statement للموديل المرتبط
                $useStatements[$relatedModel] = "use App\\Models\\{$relatedModel};";
            }
        }

        // دمج use statements
        $useStatementsCode = implode("\n", $useStatements);

        $controllerTemplate = <<<EOT
                <?php

                namespace App\Http\Controllers\Admin;

                use App\Models\\{$modelName};
                {$useStatementsCode}
                use Illuminate\Http\Request;
                use Illuminate\Support\Facades\Crypt;
                use Illuminate\Contracts\Encryption\DecryptException;
                use Yajra\DataTables\Facades\DataTables;
                use Illuminate\Support\Facades\Cache;
                use App\Http\Requests\Admin\\{$requestName};

                class {$modelPlural}Controller extends AdminController
                {
                    protected \$path;

                    public function __construct()
                    {
                        parent::__construct();
                        parent::\$data['active_menu'] = '{$modelLowerPlural}';
                        \$this->path = '{$modelLowerPlural}';
                    }

                    public function getIndex()
                    {
                {$relationsCode}
                        return view('admin.' . \$this->path . '.view', parent::\$data);
                    }

                    public function getList(Request \$request)
                    {
                        \$records = {$modelName}::get();

                        return Datatables::of(\$records)
                            ->editColumn('status', function (\$row) {
                                \$data['id'] = \$row->id;
                                \$data['status'] = \$row->status;
                                \$data['active_menu'] = \$this->path;
                                return view('admin.' . \$this->path . '.parts.status', \$data)->render();
                            })
                            ->addColumn('actions', function (\$row) {
                                \$data['active_menu'] = \$this->path;
                                \$data['id'] = \$row->id;
                                return view('admin.' . \$this->path . '.parts.actions', \$data)->render();
                            })
                            ->rawColumns(['status', 'actions'])
                            ->addIndexColumn()
                            ->make(true);
                    }

                    public function getAdd()
                    {
                        parent::\$data['info'] = NULL;
                {$relationsCode}
                        return view('admin.' . \$this->path . '.add', parent::\$data);
                    }

                    public function postAdd({$requestName} \$request)
                    {
                        \$data = \$request->validated();
                        if(isset(\$data['status'])) {
                            \$data['status'] = \$request->input('status') == '1' ? 1 : 0;
                        }

                        \$record = {$modelName}::create(\$data);

                        if (\$record) {
                            Cache::forget('spatie.permission.cache');
                            \$request->session()->flash('success', __('app.insert_success'));
                            return redirect(route(\$this->path . '.view'));
                        } else {
                            \$request->session()->flash('danger', __('app.execution_error'));
                            return redirect(route(\$this->path . '.add'))->withInput();
                        }
                    }

                    public function getEdit(Request \$request, \$id)
                    {
                        try {
                            \$decryptedId = Crypt::decrypt(\$id);
                        } catch (DecryptException \$e) {
                            \$request->session()->flash('danger', __('app.not_found'));
                            return redirect(route(\$this->path . '.view'));
                        }

                        \$record = {$modelName}::findOrFail(\$decryptedId);
                        parent::\$data['info'] = \$record;
                {$relationsCode}
                        return view('admin.' . \$this->path . '.add', parent::\$data);
                    }

                    public function postEdit({$requestName} \$request, \$id)
                    {
                        try {
                            \$decryptedId = Crypt::decrypt(\$id);
                        } catch (DecryptException \$e) {
                            \$request->session()->flash('danger', __('app.not_found'));
                            return redirect(route(\$this->path . '.view'));
                        }

                        \$record = {$modelName}::findOrFail(\$decryptedId);

                        \$validatedData = \$request->validated();
                        if(isset(\$validatedData['status'])) {
                            \$validatedData['status'] = \$request->input('status') == '1' ? 1 : 0;
                        }

                        \$update = \$record->update(\$validatedData);

                        if (\$update) {
                            Cache::forget('spatie.permission.cache');
                            \$request->session()->flash('success', __('app.update_success'));
                            return redirect(route(\$this->path . '.view'));
                        } else {
                            \$request->session()->flash('danger', __('app.execution_error'));
                            return redirect(route(\$this->path . '.edit', ['id' => \$id]))->withInput();
                        }
                    }

                    public function postStatus(Request \$request)
                    {
                        \$id = \$request->get('id');
                        try {
                            \$decryptedId = Crypt::decrypt(\$id);
                        } catch (DecryptException \$e) {
                            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
                        }

                        \$record = {$modelName}::findOrFail(\$decryptedId);

                        \$newStatus = \$record->status == 1 ? 0 : 1;
                        \$update = \$record->update(['status' => \$newStatus]);

                        if (\$update) {
                            Cache::forget('spatie.permission.cache');
                            return response()->json([
                                'status' => 'success',
                                'message' => \$newStatus ? __('app.activation_success') : __('app.disable_success'),
                                'type' => \$newStatus ? 'yes' : 'no'
                            ]);
                        } else {
                            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
                        }
                    }

                    public function postDelete(Request \$request)
                    {
                        try {
                            \$decryptedId = Crypt::decrypt(\$request->input('id'));
                        } catch (DecryptException \$e) {
                            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
                        }

                        try {
                            \$record = {$modelName}::findOrFail(\$decryptedId);
                        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException \$e) {
                            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
                        }
                        if (\$record->delete()) {
                            Cache::forget('spatie.permission.cache');
                            return response()->json(['status' => 'success', 'message' => __('app.delete_success')]);
                        } else {
                            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
                        }
                    }
                }
                EOT;

        $controllerDir = app_path("Http/Controllers/Admin");
        if (!File::exists($controllerDir)) {
            File::makeDirectory($controllerDir, 0777, true);
        }
        File::put("{$controllerDir}/{$controllerName}.php", $controllerTemplate);
    }

    /**
     * دالة مساعدة لتحويل الاسم المفرد إلى جمع بسيط.
     */
    protected function makePlural($word)
    {
        $exceptions = [
            'Person' => 'People',
            'Child' => 'Children',
            'Mouse' => 'Mice',
            'Foot' => 'Feet'
        ];

        if (isset($exceptions[$word])) {
            return $exceptions[$word];
        }

        $lastLetter = substr($word, -1);

        if ($lastLetter === 'y') {
            return substr($word, 0, -1) . 'ies';
        }

        if (in_array($lastLetter, ['s', 'x', 'z']) || in_array(substr($word, -2), ['sh', 'ch'])) {
            return $word . 'es';
        }

        return $word . 's';
    }

    // Note 8: Dedicated method for generating the View files
    protected function generateViews($modelName, $modelLowerCase, $tableHead, $frontDataTable, $modalTemplate, $columns)
    {
        // نحصل على الاسم الجمعي للمجلد
        $modelLowerPlural = strtolower($this->makePlural($modelName));

        $viewsPath = resource_path("views/admin/{$modelLowerPlural}");
        if (!File::exists($viewsPath)) {
            File::makeDirectory($viewsPath . '/parts', 0755, true);
        }

        $this->copyViewParts($viewsPath);
        $this->createMainViews($viewsPath, $modelLowerPlural, $tableHead, $frontDataTable, $modalTemplate, $columns);
    }

    protected function copyViewParts($viewsPath)
    {
        $files_extend = ['actions.blade.php', 'status.blade.php', 'modal.blade.php', 'general.blade.php'];
        $stubsPath = resource_path('stubs/views/parts');

        foreach ($files_extend as $file) {
            $content = File::get("{$stubsPath}/{$file}");
            File::put("{$viewsPath}/parts/{$file}", $content);
        }
    }

    protected function createMainViews($viewsPath, $modelLowerPlural, $tableHead, $frontDataTable, $modalTemplate, $columns)
    {


        // ------------------- صفحة view -------------------
        $viewBladeTemp = <<<EOT
                @extends('admin.layout.main_master')

                @section('title')
                    {{ \$current_route->{'name_' . trans('app.lang')} }}
                @stop

                @section('page-content')
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div id="kt_app_content" class="app-content flex-column-fluid">
                        <div id="kt_app_content_container" class="app-container container-xxl">
                            <div class="card">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title">
                                        <div class="d-flex align-items-center position-relative my-1">
                                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <input type="text" id="generalSearch" value="{{ old('name') }}"
                                                class="form-control form-control-solid w-250px ps-13 generalSearch"
                                                placeholder="@lang('app.search')" />
                                        </div>
                                    </div>
                                    <div class="card-toolbar">
                                        <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                            <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true"></div>
                                            <a href="{{ route(\$active_menu . '.add') }}" class="btn btn-primary">
                                                <i class="bi bi-plus-lg"></i>@lang('app.add')
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body py-4">
                                    @include('admin.layout.masterLayouts.error')
                                    <table id="{$modelLowerPlural}" class="table table-row-bordered gy-5">
                                        <thead>
                                            <tr class="fw-semibold fs-6 text-muted">
                                                <th>#</th>
                                                {$tableHead}
                                                <th>@lang('app.actions')</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('admin.' . \$active_menu . '.parts.modal')
                @stop

                @section('js')
                <script>
                var table;
                var tableId = '{$modelLowerPlural}';
                var columns = [
                    { data: 'DT_RowIndex' },
                    {$frontDataTable}
                    { data: 'actions', responsivePriority: -1 }
                ];

                var filterFields = ['#generalSearch'];
                @include('admin.layout.masterLayouts.datatableMaster')
                </script>
                @stop
                EOT;

        File::put("{$viewsPath}/view.blade.php", $viewBladeTemp);

        // ------------------- إعداد الحقول الديناميكية -------------------
        $editorFields = [];
        $dateFields = [];

        foreach ($columns as $column) {
            $colName = $column->COLUMN_NAME;
            $type = strtolower($column->DATA_TYPE);

            if (preg_match('/(disc|description|notes)/i', $colName)) {
                $editorFields[] = $colName;
            }

            if (in_array($type, ['date', 'datetime', 'timestamp'])) {
                $dateFields[] = $colName;
            }
        }

        $jsScript = "";

        if (!empty($editorFields)) {
            $jsScript .= "<script src=\"{{ asset('admin/ckeditor/ckeditor-classic.bundle.js') }}\"></script>\n";
        }

        $jsScript .= "<script>\n";
        foreach ($editorFields as $field) {
            $jsScript .= "ClassicEditor.create(document.querySelector('#{$field}'))\n";
            $jsScript .= "    .then(editor => { console.log(editor); })\n";
            $jsScript .= "    .catch(error => { console.error(error); });\n";
        }

        if (!empty($dateFields)) {
            $dateSelectors = implode(',', array_map(fn($f) => "#{$f}", $dateFields));
            $jsScript .= "$('{$dateSelectors}').flatpickr();\n";
        }
        $jsScript .= "</script>";

        // ------------------- صفحة add/edit -------------------
        $addBladeTemp = <<<EOT
            @extends('admin.layout.main_master')

            @section('title')
                {{ \$current_route->{'name_' . trans('app.lang')} }}
            @stop

            @section('page-content')
            <div class="card">
                <div class="card-body py-4">
                    @include('admin.layout.masterLayouts.error')
                    <form action="" method="POST">
                        <div class="row justify-content-center">
                            <div class="col-9">
                                {$modalTemplate}
                            </div>
                        </div>
                        <div class="text-center pt-2">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-primary">@lang('app.save')</button>
                            <a type="reset" href="{{ route(\$active_menu . '.view') }}" class="btn btn-light me-3">@lang('app.cancel')</a>
                        </div>
                    </form>
                </div>
            </div>
            @stop

            @section('js')
            {$jsScript}
            @stop
            EOT;

        File::put("{$viewsPath}/add.blade.php", $addBladeTemp);
    }


    protected function updateTranslations($tableName, $columns)
    {
        $langDirAr = base_path("lang/ar");
        $langDirEn = base_path("lang/en");

        if (!File::exists($langDirAr)) {
            File::makeDirectory($langDirAr, 0777, true);
        }
        if (!File::exists($langDirEn)) {
            File::makeDirectory($langDirEn, 0777, true);
        }

        $langFileAr = $langDirAr . "/app.php";
        $langFileEn = $langDirEn . "/app.php";

        // قراءة محتوى الملفات الموجودة
        $arTranslations = File::exists($langFileAr) ? include $langFileAr : [];
        $enTranslations = File::exists($langFileEn) ? include $langFileEn : [];

        foreach ($columns as $column) {
            $colName = $column->COLUMN_NAME;
            $comment = $column->COLUMN_COMMENT;

            $arText = '';
            if (strpos($comment, ',') !== false) {
                $arText = trim(explode(',', $comment)[1]);
            }

            $enText = ucwords(str_replace(['_', '-', '.'], ' ', $colName));

            $arTranslations[$colName] = $arText;
            $enTranslations[$colName] = $enText;
        }

        // بناء محتوى الملفات الجديدة بالكامل
        $arContent = "<?php\n\nreturn [\n";
        foreach ($arTranslations as $key => $value) {
            if (is_array($value)) {
                // Handle nested arrays like 'success' and 'error'
                $arContent .= "    '{$key}' => [\n";
                foreach ($value as $subKey => $subValue) {
                    $arContent .= "        '{$subKey}' => '{$subValue}',\n";
                }
                $arContent .= "    ],\n";
            } else {
                $arContent .= "    '{$key}' => '{$value}',\n";
            }
        }
        $arContent .= "];\n";

        $enContent = "<?php\n\nreturn [\n";
        foreach ($enTranslations as $key => $value) {
            if (is_array($value)) {
                // Handle nested arrays
                $enContent .= "    '{$key}' => [\n";
                foreach ($value as $subKey => $subValue) {
                    $enContent .= "        '{$subKey}' => '{$subValue}',\n";
                }
                $enContent .= "    ],\n";
            } else {
                $enContent .= "    '{$key}' => '{$value}',\n";
            }
        }
        $enContent .= "];\n";


        // حفظ الملفات الجديدة
        File::put($langFileAr, $arContent);
        File::put($langFileEn, $enContent);

        $this->info("Translations for table '{$tableName}' updated successfully.");
    }


    protected function generateRoutes($modelName, $modelLowerCase, $tableName)
    {
        // استخدام اسم الكونترولر جمع عن اسم المودل
        $controllerName = "{$modelName}sController";
        $routeFile = base_path("routes/{$tableName}.php");

        $routesTemplate = <<<EOT
            <?php

            use Illuminate\\Support\\Facades\\Route;

            // {$modelName} Routes
            Route::get('{$tableName}', [
                'as' => '{$tableName}.view',
                'middleware' => ['permission:admin.{$tableName}.view'],
                'uses' => '{$controllerName}@getIndex'
            ]);

            Route::get('{$tableName}/list', [
                'as' => '{$tableName}.list',
                'middleware' => ['permission:admin.{$tableName}.view'],
                'uses' => '{$controllerName}@getList'
            ]);

            Route::get('{$tableName}/add', [
                'as' => '{$tableName}.add',
                'middleware' => ['permission:admin.{$tableName}.add'],
                'uses' => '{$controllerName}@getAdd'
            ]);

            Route::post('{$tableName}/add', [
                'as' => '{$tableName}.add',
                'uses' => '{$controllerName}@postAdd'
            ]);

            Route::get('{$tableName}/edit/{id}', [
                'as' => '{$tableName}.edit',
                'middleware' => ['permission:admin.{$tableName}.edit'],
                'uses' => '{$controllerName}@getEdit'
            ]);

            Route::post('{$tableName}/edit/{id}', [
                'as' => '{$tableName}.edit',
                'middleware' => ['permission:admin.{$tableName}.edit'],
                'uses' => '{$controllerName}@postEdit'
            ]);

            Route::post('{$tableName}/delete', [
                'as' => '{$tableName}.delete',
                'middleware' => ['permission:admin.{$tableName}.delete'],
                'uses' => '{$controllerName}@postDelete'
            ]);

            Route::post('{$tableName}/status', [
                'as' => '{$tableName}.status',
                'middleware' => ['permission:admin.{$tableName}.status'],
                'uses' => '{$controllerName}@postStatus'
            ]);

            EOT;

        // إنشاء ملف routes جديد إذا ما كان موجود
        if (!File::exists($routeFile)) {
            File::put($routeFile, $routesTemplate);
            $this->info("Route file created: routes/{$tableName}.php");
        } else {
            $this->warn("Route file already exists: routes/{$tableName}.php");
        }

        // نضيف require_once داخل web.php (داخل جروب admin)
        $this->injectRouteIncludeIntoWeb($tableName);
    }

    protected function injectRouteIncludeIntoWeb($tableName)
    {
        $webFile = base_path('routes/web.php');
        $requireLine = "    // {$tableName} Route\n    require __DIR__ . '/{$tableName}.php';\n";

        $content = File::get($webFile);

        // لو السطر موجود بالفعل ما نضيفه
        if (strpos($content, $requireLine) !== false) {
            $this->warn("Route include for {$tableName} already exists in web.php");
            return;
        }

        // إيجاد بداية Route::group المطلوب
        $groupStartPos = strpos($content, "'middleware' => ['auth:admin']");
        if ($groupStartPos === false) {
            $this->error("Could not find Route::group with middleware ['auth:admin'] in web.php");
            return;
        }

        // إيجاد موقع فتح القوس { بعد الـ Route::group
        $bracePos = strpos($content, '{', $groupStartPos);
        if ($bracePos === false) {
            $this->error("Could not find opening brace for admin Route::group in web.php");
            return;
        }

        // الآن نحتاج إيجاد القوس الختامي المغلق للفنكشن
        $openBraces = 1;
        $pos = $bracePos + 1;
        $length = strlen($content);

        while ($pos < $length && $openBraces > 0) {
            if ($content[$pos] === '{') $openBraces++;
            elseif ($content[$pos] === '}') $openBraces--;
            $pos++;
        }

        $insertPos = $pos - 1; // مكان القوس الختامي

        // حقن السطر قبل القوس الختامي
        $newContent = substr($content, 0, $insertPos) . "\n" . $requireLine . substr($content, $insertPos);

        File::put($webFile, $newContent);
        $this->info("Route include for {$tableName} added inside admin Route::group successfully.");
    }
}
