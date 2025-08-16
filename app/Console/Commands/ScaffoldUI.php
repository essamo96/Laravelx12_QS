<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Scaffold extends Command
{
    protected $signature = 'make:scaffold {name} {table}';
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
                    ->select('COLUMN_NAME', 'DATA_TYPE', 'COLUMN_COMMENT')
                    ->get();

        if ($columns->isEmpty()) {
            $this->error("Table '{$tableName}' not found or has no scannable columns.");
            return;
        }

        list($fillable, $dataTable, $frontDataTable, $modalTemplate, $tableHead, $requestRulesArray) = $this->prepareScaffoldData($columns);

        $this->generateModel($modelName, $fillable);
        $this->generateRequest($modelName, $modelLowerCase, $requestRulesArray);
        $this->generateController($modelName, $modelLowerCase);
        $this->generateViews($modelName, $modelLowerCase, $tableHead, $frontDataTable, $modalTemplate, $columns);
        $this->updateTranslations($tableName, $columns);

        $this->info("Scaffolded {$name} model, controller, form request, and views for table {$tableName} successfully.");
    }

    protected function prepareScaffoldData($columns)
    {
        $fillable = [];
        $dataTable = '';
        $frontDataTable = '';
        $modalTemplate = '';
        $tableHead = '';
        $rowBuffer = '';
        $fieldCount = 0;
        $requestRulesArray = [];

        foreach ($columns as $column) {
            $colName = $column->COLUMN_NAME;
            $type = strtolower($column->DATA_TYPE);
            $comment = $column->COLUMN_COMMENT ?: ucfirst($colName);

            $fillable[] = $colName;
            $dataTable .= '->addColumn("' . $colName . '", fn($row) => $row->' . $colName . ')';
            $frontDataTable .= '{data: "' . $colName . '"},';

            // Note 2: Use an associative array for translations
            $translations[$colName]['ar'] = $comment;
            $translations[$colName]['en'] = $comment;
            if (strpos($comment, ',') !== false) {
                [$enText, $arText] = explode(',', $comment, 2);
                $translations[$colName]['en'] = trim($enText);
                $translations[$colName]['ar'] = trim($arText);
            }

            // Note 3: Use a dedicated method to build field templates for clarity
            list($fieldTemplate, $rule) = $this->getFieldTemplateAndRule($colName, $type);

            if (in_array($type, ['int', 'bigint', 'decimal', 'float', 'double', 'varchar', 'char', 'text', 'date', 'datetime', 'timestamp']) || Str::endsWith($colName, '_id')) {
                $rowBuffer .= $fieldTemplate;
                $fieldCount++;
            } else {
                $modalTemplate .= $fieldTemplate;
            }

            if ($fieldCount == 2) {
                $modalTemplate .= '<div class="row mb-5">' . $rowBuffer . '</div>';
                $rowBuffer = '';
                $fieldCount = 0;
            }

            $tableHead .= '<th>@lang(\'' . $colName . '\')</th>';
            $requestRulesArray[$colName] = $rule;
        }

        if ($rowBuffer != '') {
            $modalTemplate .= '<div class="row mb-5">' . $rowBuffer . '</div>';
        }

        return [
            "'" . implode("', '", $fillable) . "'", // formatted fillable string
            $dataTable,
            $frontDataTable,
            $modalTemplate,
            $tableHead,
            $requestRulesArray
        ];
    }

    // Note 4: A clear and specific method for generating field rules
    protected function getFieldTemplateAndRule($colName, $type)
    {
        $fieldTemplate = '';
        $rule = '';

        if (preg_match('/status|is_user/i', $colName)) {
            $fieldTemplate = '
                    <div class="form-floating mb row">
                        <div class="col">
                            <label class="p-2">@lang(\'' . $colName . '\')</label>
                            <label class="form-check form-switch">
                                <?php $data = $info ? $info->' . $colName . ' : old("' . $colName . '"); ?>
                                <input class="form-check-input" name="' . $colName . '" type="checkbox" value="1"
                                    {{ $data == 1 ? "checked=\"checked\"" : "" }}>
                            </label>
                        </div>
                    </div>';
            $rule = "'in:0,1'";
        } elseif (preg_match('/(disc|description|notes)/i', $colName)) {
            $fieldTemplate = '
                        <div class="form-floating mb-9 row">
                            <div class="fv-row mb-10 col">
                                <label class="fw-semibold fs-6 mb-2" for="' . $colName . '">@lang(\'' . $colName . '\')</label>
                                <textarea name="' . $colName . '" id="' . $colName . '" class="form-control form-control-solid">{{ $info ? $info->' . $colName . ' : old("' . $colName . '") }}</textarea>
                            </div>
                        </div>';
            $rule = "'nullable|string'";
        } elseif (in_array($type, ['int', 'bigint', 'decimal', 'float', 'double'])) {
            $fieldTemplate = '
                        <div class="col-md-6 fv-row fv-plugins-icon-container">
                            <label class="required fs-5 fw-semibold mb-2">@lang(\'' . $colName . '\')</label>
                            <input type="number" class="form-control form-control-solid" name="' . $colName . '" value="{{ $info ? $info->' . $colName . ' : old("' . $colName . '") }}">
                        </div>';
            $rule = "'required|numeric'";
        } elseif (in_array($type, ['varchar', 'char', 'text'])) {
            $fieldTemplate = '
                        <div class="col-md-6 fv-row fv-plugins-icon-container">
                            <label class="required fs-5 fw-semibold mb-2">@lang(\'' . $colName . '\')</label>
                            <input type="text" class="form-control form-control-solid" name="' . $colName . '" value="{{ $info ? $info->' . $colName . ' : old("' . $colName . '") }}">
                        </div>';
            $rule = "'required|string|max:255'";
        } elseif (in_array($type, ['date', 'datetime', 'timestamp'])) {
            $fieldTemplate = '
                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                        <label class="required fs-5 fw-semibold mb-2">@lang(\'' . $colName . '\')</label>
                        <input type="date" class="form-control form-control-solid" name="' . $colName . '" id="' . $colName . '" value="{{ $info ? $info->' . $colName . ' : old("' . $colName . '") }}">
                    </div>';
            $rule = "'required|date'";
        } elseif (Str::endsWith($colName, '_id')) {
            $related = Str::singular(Str::before($colName, '_id'));
            $fieldTemplate = '
                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                        <label class="required fs-5 fw-semibold mb-2">@lang(\'' . $colName . '\')</label>
                        <select name="' . $colName . '" id="' . $colName . '" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="@lang(\'' . $colName . '\')">
                            <option value="">اختر ...</option>
                            <?php $data = $info ? $info->' . $colName . ' : old("' . $colName . '"); ?>
                            @foreach ($' . $related . 's as $item)
                                <option value="{{ $item->id }}" {{ $data == $item->id ? "selected" : "" }}>
                                    {{ $item->{"name_" . trans("app.lang")} }}
                                </option>
                            @endforeach
                        </select>
                    </div>';
            $rule = "'required|exists:" . Str::plural($related) . ",id'";
        }
        return [$fieldTemplate, $rule];
    }

    // Note 5: Dedicated method for generating the Model file
    protected function generateModel($modelName, $fillable)
    {
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
        $rulesString = implode(",\n\t\t\t\t", array_map(fn($key, $val) => "'{$key}' => {$val}", array_keys($requestRulesArray), $requestRulesArray));
        $editRulesString = implode(",\n\t\t\t\t", array_map(function($key, $val) {
            return preg_match('/status|is_user/i', $key) ? "'{$key}' => {$val}" : "'{$key}' => " . 'str_replace(\'required\', \'nullable\', ' . $val . ')';
        }, array_keys($requestRulesArray), $requestRulesArray));

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
    protected function generateController($modelName, $modelLowerCase)
    {
        $controllerName = "{$modelName}Controller";
        $controllerPath = app_path("Http/Controllers/Admin/{$controllerName}.php");
        $requestName = "{$modelName}Request";

        $controllerTemplate = <<<EOT
<?php

namespace App\Http\Controllers\Admin;

use App\Models\\{$modelName};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\\{$requestName};

class {$modelName}Controller extends AdminController
{
    protected \$path;

    public function __construct()
    {
        parent::__construct();
        parent::\$data['active_menu'] = '{$modelLowerCase}';
        \$this->path = '{$modelLowerCase}';
    }

    public function getIndex()
    {
        return view('admin.' . \$this->path . '.view', parent::\$data);
    }

    public function getList(Request \$request)
    {
        \$records = {$modelName}::get();

        return Datatables::of(\$records)
            ->editColumn('status', function (\$row) {
                \$data['id'] = \$row->id;
                \$data['status'] = \$row->status;
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

    // Note 8: Dedicated method for generating the View files
    protected function generateViews($modelName, $modelLowerCase, $tableHead, $frontDataTable, $modalTemplate, $columns)
    {
        $viewsPath = resource_path("views/admin/{$modelLowerCase}");
        if (!File::exists($viewsPath)) {
            File::makeDirectory($viewsPath . '/parts', 0755, true);
        }

        $this->copyViewParts($viewsPath);
        $this->createMainViews($viewsPath, $modelLowerCase, $tableHead, $frontDataTable, $modalTemplate, $columns);
    }

    protected function copyViewParts($viewsPath)
    {
        $files_extend = ['actions.blade.php', 'status.blade.php'];
        $stubsPath = resource_path('stubs/views/parts');

        foreach ($files_extend as $file) {
            $content = File::get("{$stubsPath}/{$file}");
            File::put("{$viewsPath}/parts/{$file}", $content);
        }
    }

    protected function createMainViews($viewsPath, $modelLowerCase, $tableHead, $frontDataTable, $modalTemplate, $columns)
    {
        $viewBladeTemp = <<<EOT
@extends('admin.layout.main_master')
@section('title')
    {{ \$current_route->{'name_' . trans('app.lang')} }}
@stop
@section('page-breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ url('/') }}" class="text-muted text-hover-primary">@lang('app.home')</a>
    </li>
    <li class="breadcrumb-item text-muted">- {{ \$current_route->{'name_' . trans('app.lang')} }}</li>
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
                                <input type="text" id="delete-button"
                                    class="form-control form-control-solid w-250px ps-13 generalSearch"
                                    placeholder="@lang('app.search')" />
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true"></div>
                                <a href="{{ route(\$active_menu . '.add') }}" class="btn btn-primary">
                                    <i class="ki-duotone ki-plus fs-2"></i>@lang('app.add')</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table id="kt_table" class="table table-row-bordered gy-5">
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
    $(document).ready(function() {
        var table = $('#kt_table').DataTable({
            responsive: true,
            processing: true,
            "bLengthChange": false,
            "bFilter": false,
            serverSide: true,
            ajax: {
                url: dataTableAjaxUrl,
                data: function(d) {
                    d.name = $('.generalSearch').val();
                }
            },
            columns: [{data: 'DT_RowIndex'}, {$frontDataTable} {data: 'actions', responsivePriority: -1}],
            language: { url: dataTableLanguageUrl }
        });
        $('.generalSearch').on('input', function() {
            table.ajax.reload();
        });

        @include('admin.layout.masterLayouts.delete')
        @include('admin.layout.masterLayouts.status')
    });
</script>
@stop
EOT;

        File::put("{$viewsPath}/view.blade.php", $viewBladeTemp);

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

        $jsScript = "<script>\n";
        foreach ($editorFields as $field) {
            $jsScript .= "ClassicEditor.create(document.querySelector('#{$field}')).then(editor => { console.log(editor); }).catch(error => { console.error(error); });\n";
        }
        if (!empty($dateFields)) {
            $dateSelectors = implode(',', array_map(fn($f) => "#{$f}", $dateFields));
            $jsScript .= "$('{$dateSelectors}').flatpickr();\n";
        }
        $jsScript .= "</script>";

        $addBladeTemp = <<<EOT
@extends('admin.layout.main_master')
@section('title')
    {{ \$current_route->{'name_' . trans('app.lang')} }}
@stop
@section('page-breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ url('/') }}" class="text-muted text-hover-primary">@lang('app.home')</a>
    </li>
    <li class="breadcrumb-item text-muted">- {{ \$current_route->{'name_' . trans('app.lang')} }}</li>
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

    // Note 9: Dedicated method for updating translation files
    protected function updateTranslations($tableName, $columns)
    {
        $langDirAr = resource_path("lang/ar");
        $langDirEn = resource_path("lang/en");

        if (!File::exists($langDirAr)) {
            File::makeDirectory($langDirAr, 0777, true);
        }
        if (!File::exists($langDirEn)) {
            File::makeDirectory($langDirEn, 0777, true);
        }

        $langFileAr = $langDirAr . "/app.php";
        $langFileEn = $langDirEn . "/app.php";

        $arTranslations = File::exists($langFileAr) ? include $langFileAr : [];
        $enTranslations = File::exists($langFileEn) ? include $langFileEn : [];

        $tableComment = "// Translations for table: {$tableName}";
        $newArTranslations = [];
        $newEnTranslations = [];

        foreach ($columns as $column) {
            $colName = $column->COLUMN_NAME;
            $comment = $column->COLUMN_COMMENT ?: ucfirst($colName);
            $enText = ucfirst($colName);
            $arText = $comment;

            if (strpos($comment, ',') !== false) {
                [$enText, $arText] = explode(',', $comment, 2);
            }

            $newArTranslations[$colName] = trim($arText);
            $newEnTranslations[$colName] = trim($enText);
        }

        $arTranslations = array_merge($arTranslations, [$tableComment => ''], $newArTranslations);
        $enTranslations = array_merge($enTranslations, [$tableComment => ''], $newEnTranslations);

        File::put($langFileAr, "<?php\n\nreturn " . var_export($arTranslations, true) . ";\n");
        File::put($langFileEn, "<?php\n\nreturn " . var_export($enTranslations, true) . ";\n");
    }
}
// للتجربة:

// احفظ الكود في ملف جديد، وليكن app/Console/Commands/Scaffold.php.

// افتح Terminal أو Command Prompt في مشروع Laravel الخاص بك.

// أضف use App\Console\Commands\Scaffold; إلى ملف app/Console/Kernel.php في نهاية مصفوفة $commands.

// قم بإنشاء جدول في قاعدة البيانات لديك، ثم شغل الكوماند من الطرفية:

// Bash

// php artisan make:scaffold Post posts
// (مع تغيير "Post" إلى اسم الموديل و"posts" إلى اسم الجدول).

// ستلاحظ أن جميع الملفات تم إنشاؤها في الأماكن المحددة، مع تحديث ملفات الترجمة بشكل تلقائي.

// هذا الكود هو مثال على كيفية بناء أدوات مساعدة (Helpers) قوية ومرنة في Laravel.
