<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
class ScaffoldCustomForMetronicBlade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:scaffold-custom-for-metronic-blade';
    protected $signature = 'make:essam {name} {table}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold a new model, migration, controller, and views with custom code';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $modelName = Str::studly($name);
        $modelLowerCase = Str::snake($modelName);

        $table = $this->argument('table');

        $tableName = $table; // replace with your table name

        $dbName = env('DB_DATABASE'); // get the database name from the environment configuration

        $columns = DB::select("SELECT COLUMN_NAME, DATA_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = ?
        AND TABLE_SCHEMA = ?
        AND COLUMN_NAME NOT IN ('id', 'created_at', 'updated_at')
        ", [$tableName, $dbName]);

        $fillable = '[';
        $dataTable = "";
        $frontDataTable = "";
        $modalTemplate = '';

        $tableHead = "";
        $RequestRoles = "";


        foreach ($columns as $column) {
            $fillable .= '"' . $column->COLUMN_NAME . '",';

            $dataTable .= '->addColumn(' . "/'" . $column->COLUMN_NAME . "/'" . ', function ($row) {
                                        return $row->' . $column->COLUMN_NAME . ';
                                })';
            $frontDataTable .= '
                                {data: "' . $column->COLUMN_NAME . '"},

                                ';
            $modalTemplate .= '`
                <div class="form-floating mb-9 row ">
                    <div class="col">
                        <label class="p-2 required">' . $column->COLUMN_NAME . '</label>
                        <input type="text" value="{{  $info->' . $column->COLUMN_NAME . ' ?? old("' . $column->COLUMN_NAME . '",$info->' . $column->COLUMN_NAME . ') }}" name="' . $column->COLUMN_NAME . '"
                            class="form-control" />
                    </div>
                </div>
            `';

            $tableHead .= '<th >' . $column->COLUMN_NAME . '</th>';
            $RequestRoles .= '"' . $column->COLUMN_NAME . '" => "required",';
        }
        $fillable = rtrim($fillable, ',') . '];';
        /////////////////////////////////////   create model //////////////////
        $modelTemplate = <<<EOT
                                <?php

                                namespace App\Models;

                                use Illuminate\Database\Eloquent\Factories\HasFactory;
                                use Illuminate\Database\Eloquent\Model;

                                class {$modelName} extends Model
                                {
                                    use HasFactory;
                                        //////////////////////////////////////////////
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

        $modelDir = app_path("Models");

        if (!File::exists($modelDir)) {
            File::makeDirectory($modelDir, 0777, true);
        }

        File::put("{$modelDir}/{$modelName}.php", $modelTemplate);
        $this->info("Model for {$name} created successfully.");

        //////////// end modal creation ////////////////////


        // Create resource controller
        $this->createController($modelName, $RequestRoles);

        // Define the path to the custom view templates
        $templatePath = resource_path('stubs/views');
        $viewsPath = resource_path("views/admin/{$modelLowerCase}");
        $templatePath1 = resource_path('stubs/views/parts');
        $viewsPath1 = resource_path("views\admin\\" . $modelLowerCase . "\parts");

        // Create views directory if it doesn't exist
        if (!File::exists($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
            File::makeDirectory($viewsPath . '\parts', 0755, true);
        }

        // Copy custom view templates to the new views directory
        $this->copyViewFiles($templatePath, $templatePath1, $viewsPath, $viewsPath1, $name, $tableHead, $frontDataTable, $modalTemplate);

        $this->info("Scaffolded {$name} model, migration, controller, and views with custom code");
    }

    protected function createController($modelName, $RequestRoles)
    {
        $controllerName = "{$modelName}Controller";
        $controllerPath = app_path("Http/Controllers/Admin/{$controllerName}.php");

        $controlerTemp = <<<EOT
                        <?php

                        namespace App\Http\Controllers\Admin;

                        use Illuminate\Http\Request;
                        use Illuminate\Support\Facades\Crypt;
                        use Illuminate\Support\Facades\Validator;
                        use Illuminate\Contracts\Encryption\DecryptException;
                        use DataTables;
                        use App\Models\\{$modelName};

                        class {$modelName}Controller extends AdminController {

                            const INSERT_SUCCESS_MESSAGE = "نجاح، تم الإضافة بتجاح";
                            const UPDATE_SUCCESS = "نجاح، تم التعديل بنجاح";
                            const DELETE_SUCCESS = "نجاح، تم الحذف بنجاح";
                            const PASSWORD_SUCCESS = "نجاح، تم تغيير كلمة المرور بنجاح";
                            const EXECUTION_ERROR = "عذراً، حدث خطأ أثناء تنفيذ العملية";
                            const NOT_FOUND = "عذراً،لا يمكن العثور على البيانات";
                            const ACTIVATION_SUCCESS = "نجاح، تم التفعيل بنجاح";
                            const DISABLE_SUCCESS = "نجاح، تم التعطيل بنجاح";

                            //////////////////////////////////////////////
                            public function __construct() {
                                parent::__construct();
                                parent::\$data['active_menu'] = '{$modelName}';
                                \$this->path = '{$modelName}';
                            }

                            public function getIndex() {
                                return view('admin.' . \$this->path . '.view', parent::\$data);
                            }

                            //////////////////////////////////////////////
                            public function getList(Request \$request) {
                                \$countries = new {$modelName};
                                \$name = \$request->get('name');
                                \$info = \$countries->getSearch(\$name);
                                \$datatable = Datatables::of(\$info);
                                \$datatable->editColumn('status', function (\$row) {
                                    parent::\$data['id'] = \$row->id;
                                    parent::\$data['status'] = \$row->status;
                                    return view('admin.' . \$this->path . '.parts.status', parent::\$data)->render();
                                });
                                \$datatable->addColumn('actions', function (\$row) {
                                    \$data['active_menu'] = \$this->path;
                                    \$data['id'] = \$row->id;
                                    return view('admin.' . \$this->path . '.parts.actions', \$data)->render();
                                });
                                \$datatable->escapeColumns(['*']);
                                return \$datatable->addIndexColumn()->make(true);
                            }

                            public function getAdd() {
                                parent::\$data['info'] = new {$modelName}();
                                return view('admin.' . \$this->path . '.add', parent::\$data);
                            }

                            public function postAdd(Request \$request) {
                                \$save_data = \$request->all();
                                \$save_data['status'] = (int) \$request->get('status');
                                \$validator = Validator::make(\$save_data, [
                                        {$RequestRoles}
                                ]);
                                ////////////////////////////////////////
                                if (\$validator->fails()) {
                                    \$request->session()->flash('danger', \$validator->messages());
                                    \$firstError = \$validator->errors()->first();
                                    return redirect(route(\$this->path . '.add'))->withInput()->with('error', \$firstError);
                                } else {
                                    \$add = {$modelName}::create(\$save_data);
                                    if (\$add) {
                                        \$request->session()->flash('success', self::INSERT_SUCCESS_MESSAGE);
                                        return redirect(route(\$this->path . '.view'));
                                    } else {
                                        \$request->session()->flash('danger', self::EXECUTION_ERROR);
                                        return redirect(route(\$this->path . '.add'))->withInput();
                                    }
                                }
                            }

                            public function getEdit(Request \$request, \$id) {
                                try {
                                    \$id = Crypt::decrypt(\$id);
                                } catch (DecryptException \$e) {
                                    \$request->session()->flash('danger', self::NOT_FOUND);
                                    return redirect(route(\$this->path . '.view'));
                                }

                                \$info = {$modelName}::findOrFail(\$id);
                                if (\$info) {
                                    parent::\$data['info'] = \$info;
                                    return view('admin.' . \$this->path . '.add', parent::\$data);
                                } else {
                                    \$request->session()->flash('danger', self::NOT_FOUND);
                                    return redirect(route(\$this->path . '.view'));
                                }
                            }

                            public function postEdit(Request \$request, \$id) {
                                try {
                                    \$encrypted_id = \$id;
                                    \$id = Crypt::decrypt(\$id);
                                } catch (DecryptException \$e) {
                                    \$request->session()->flash('danger', self::NOT_FOUND);
                                    return redirect(route(\$this->path . '.view'));
                                }
                                \$info = {$modelName}::findOrFail(\$id);
                                if (\$info) {
                                    \$save_data = \$request->all();
                                    \$save_data['status'] = (int) \$request->get('status');
                                    \$validator = Validator::make(\$save_data, [
                                        {$RequestRoles}
                                    ]);
                                    if (\$validator->fails()) {
                                        \$request->session()->flash('danger', \$validator->messages());
                                        return redirect(route(\$this->path . '.edit', ['id' => \$encrypted_id]))->withInput();
                                    } else {
                                        \$update = \$info->update(\$save_data);
                                        if (\$update) {
                                            \$request->session()->flash('success', self::UPDATE_SUCCESS);
                                            return redirect(route(\$this->path . '.view'));
                                        } else {
                                            \$request->session()->flash('danger', self::EXECUTION_ERROR);
                                            return redirect(route(\$this->path . '.edit', ['id' => \$encrypted_id]))->withInput();
                                        }
                                    }
                                } else {
                                    \$request->session()->flash('danger', self::NOT_FOUND);
                                    return redirect(route(\$this->path . '.view'));
                                }
                            }

                            public function postStatus(Request \$request) {
                                \$id = \$request->get('id');
                                try {
                                    \$id = Crypt::decrypt(\$id);
                                } catch (DecryptException \$e) {
                                    return response()->json(['status' => 'error', 'message' => 'Error Decode']);
                                }
                                \$info = {$modelName}::findOrFail(\$id);

                                if (\$info) {
                                    \$newStatus = \$info->status == 1 ? 0 : 1;
                                    \$update = \$info->update(['status' => \$newStatus]);
                                    if (\$update) {
                                        return response()->json([
                                                    'status' => 'success',
                                                    'message' => \$newStatus ? self::ACTIVATION_SUCCESS : self::DISABLE_SUCCESS,
                                                    'type' => \$newStatus ? 'yes' : 'no'
                                        ]);
                                    } else {
                                        return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
                                    }
                                } else {
                                    return response()->json(['status' => 'error', 'message' => self::NOT_FOUND]);
                                }
                            }

                            public function postDelete(Request \$request) {
                                \$id = \$request->get('id');
                                try {
                                    \$id = Crypt::decrypt(\$id);
                                } catch (DecryptException \$e) {
                                    return response()->json(['status' => 'error', 'message' => 'Error Decode']);
                                }
                                \$info = {$modelName}::findOrFail(\$id);
                                if (\$info) {
                                    \$delete = \$info->delete();
                                    if (\$delete) {
                                        return response()->json(['status' => 'success', 'message' => self::DELETE_SUCCESS]);
                                    } else {
                                        return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
                                    }
                                } else {
                                    return response()->json(['status' => 'error', 'message' => self::NOT_FOUND]);
                                }
                            }
                        }

                        EOT;


        // Write the content to the controller file
        File::put($controllerPath, $controlerTemp);

        $this->info("Created Controller: {$controllerName}");
    }

    protected function copyViewFiles($templatePath, $templatePath1, $viewsPath, $viewsPath1, $name, $tableHead, $frontDataTable, $modalTemplate)
    {
        $files = ['add.blade.php', 'view.blade.php'];
        $files_extend = ['actions.blade.php', 'status.blade.php'];
        // foreach ($files as $file) {
        //     $content = File::get("{$templatePath}/{$file}");
        //     // Replace placeholders in the template content with actual values
        //     $content = str_replace('{{modelName}}', $name, $content);
        //     File::put("{$viewsPath}/{$file}", $content);
        // }
        foreach ($files_extend as $file) {
            $content = File::get("{$templatePath1}/{$file}");
            File::put($viewsPath1 . "\\" . $file, $content);
        }

        $viewBladeTemp =  <<<EOT
        @extends('admin.layout.master')
        @section('title')
        {{ \$current_route->name_ar ?? null }}
        @stop
        @section('page-breadcrumb')
        <li class="breadcrumb-item text-muted">
            <a href="{{ url('/') }}" class="text-muted text-hover-primary">الرئيسية</a>
        </li>
        <li class="breadcrumb-item text-muted">- {{ \$current_route->name_ar ?? null }}</li>
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
                                    <input type="text" id="generalSearch" value="{{ old('name') }}" class="form-control form-control-solid w-250px ps-13 generalSearch" placeholder="البحث " />
                                </div>
                            </div>
                            <div class="card-toolbar">
                                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                    <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true"></div>
                                    @can('admin.'.\$active_menu.'.add')
                                    <a href="{{ route(\$active_menu . '.add') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-lg"></i>اضافة
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-4">
                            @include('admin.layout.error')
                            <table id="kt_table" class="table table-row-bordered gy-5">
                                <thead>
                                    <tr class="fw-semibold fs-6 text-muted">
                                        <th> # </th>
                                       {$tableHead}
                                       <th>العمليات</th>

                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('admin.layout.modal')
        @stop
        @section('js')
        <script>
            $(document).ready(function () {
                var table = $('#kt_table').DataTable({
                    responsive: true,
                    ordering: false,
                    processing: true,
                    "bLengthChange": false,
                    "bFilter": false,
                    serverSide: true,
                    ajax: {
                        url: "<?= route(\$active_menu . '.list') ?>",
                        data: function (d) {
                            d.name = $('#generalSearch').val();
                        }
                    },
                    columns: [{data: 'DT_RowIndex'},
                        {$frontDataTable}
                        {data: 'actions', responsivePriority: -1}
                    ],
                    "createdRow": function (row, data, dataIndex) {
                        $(row).find('td:eq(1)').addClass(' align-items-center');
                    }
                });
                $('.generalSearch').on('input', function () {
                    table.ajax.reload();
                });
                @include('admin.layout.delete')
            });
        </script>
        @include('admin.layout.status')
        @stop
        EOT;
        File::put("{$viewsPath}/view.blade.php", $viewBladeTemp);


        $addBladeTemp =  <<<EOT

        @extends('admin.layout.master')
        @section('title')
            {{ \$current_route->name_ar ?? null }}
        @stop
        @section('page-breadcrumb')
            <li class="breadcrumb-item text-muted">
                <a href="{{ url('/') }}" class="text-muted text-hover-primary">الرئيسية</a>
            </li>
            <li class="breadcrumb-item text-muted">- {{ \$current_route->name_ar ?? null }}</li>
        @stop
        @section('page-content')
            <div class="card">
                <div class="card-body py-4">
                    @include('admin.layout.error')
                    <form action="" method="POST">
                        <div class="row justify-content-center">
                            <div class="col-9">
                                {$modalTemplate}



                            </div>
                        </div>
                        <div class="text-center pt-2">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-primary">حفظ </button>
                            <a type="reset" href="{{ route(\$active_menu . '.view') }}" class="btn btn-light me-3">الغاء الامر</a>
                        </div>
                    </form>
                </div>
            </div>
        @stop

        EOT;
        File::put("{$viewsPath}/add.blade.php", $addBladeTemp);
    }
}
