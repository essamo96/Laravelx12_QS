<?php

namespace App\Services\AdminGenerator;

use Illuminate\Support\Facades\File;

class StubGeneratorService
{
    private string $stubPath;

    public function __construct()
    {
        $this->stubPath = base_path('stubs/admin-generator');
    }

    public function ensureDefaultStubs(): void
    {
        if (!File::exists($this->stubPath)) {
            File::makeDirectory($this->stubPath, 0755, true);
        }

        foreach ($this->defaultStubs() as $name => $content) {
            $path = $this->stubPath . '/' . $name;
            if (!File::exists($path)) {
                File::put($path, $content);
            }
        }
    }

    public function render(string $stubName, array $replacements): string
    {
        $path = $this->stubPath . '/' . $stubName;
        $template = File::get($path);

        foreach ($replacements as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string) $value, $template);
        }

        return $template;
    }

    public function write(string $absolutePath, string $content): void
    {
        $directory = dirname($absolutePath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        File::put($absolutePath, $content);
    }

    private function defaultStubs(): array
    {
        return [
            'model.stub' => <<<'STUB'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {{ModelName}} extends Model
{
    use HasFactory;

    protected $fillable = [
{{fillable}}
    ];

    public array $searchable = [
{{searchable}}
    ];

    public array $datatableColumns = [
{{datatableColumns}}
    ];
{{relations}}
}
STUB,
            'controller.stub' => <<<'STUB'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\{{RequestName}};
use App\Models\{{ModelName}};
{{relatedModelUses}}
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class {{ControllerName}} extends AdminController
{
    protected string $path = '{{PathName}}';

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = $this->path;
    }

    public function index(Request $request)
    {
{{relationLoaders}}
        parent::$data['filters'] = $request->only(['status', 'from_date', 'to_date'{{relationFilterKeys}}]);
        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function list(Request $request): JsonResponse
    {
        $query = {{ModelName}}::query();
        $query = $this->applyFilters($query, $request);

        return DataTables::of($query)
            ->editColumn('status', function ($row) {
                return view('admin.' . $this->path . '.parts.status', [
                    'id' => $row->id,
                    'status' => $row->status,
                    'active_menu' => $this->path,
                ])->render();
            })
            ->addColumn('actions', function ($row) {
                return view('admin.' . $this->path . '.parts.actions', [
                    'id' => $row->id,
                    'active_menu' => $this->path,
                ])->render();
            })
            ->rawColumns(['status', 'actions'])
            ->addIndexColumn()
            ->make(true);
    }

    public function create()
    {
        parent::$data['info'] = null;
{{relationLoaders}}
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function store({{RequestName}} $request)
    {
        $data = $request->validated();
{{booleanNormalizeStore}}
        {{ModelName}}::create($data);

        Cache::forget('spatie.permission.cache');
        return redirect()->route($this->path . '.view')->with('success', __('app.insert_success'));
    }

    public function edit(string ${{RouteParam}})
    {
        $recordId = $this->decryptId(${{RouteParam}});
        parent::$data['info'] = {{ModelName}}::findOrFail($recordId);
{{relationLoaders}}
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function update({{RequestName}} $request, string ${{RouteParam}})
    {
        $recordId = $this->decryptId(${{RouteParam}});
        $record = {{ModelName}}::findOrFail($recordId);
        $data = $request->validated();
{{booleanNormalizeUpdate}}
        $record->update($data);

        Cache::forget('spatie.permission.cache');
        return redirect()->route($this->path . '.view')->with('success', __('app.update_success'));
    }

    /** حذف عبر مودال DataTables (POST + id مشفّر) — يطابق المسار {module}.delete */
    public function postDelete(Request $request): JsonResponse
    {
        $encryptedId = (string) $request->input('id');

        try {
            $recordId = (int) Crypt::decrypt($encryptedId);
        } catch (DecryptException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error'),
            ]);
        }

        $record = {{ModelName}}::find($recordId);
        if (! $record) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.not_found'),
            ]);
        }

        $record->delete();

        Cache::forget('spatie.permission.cache');

        return response()->json(['status' => 'success', 'message' => __('app.delete_success')]);
    }

    public function destroy(Request $request, string ${{RouteParam}}): JsonResponse
    {
        $recordId = $this->decryptId(${{RouteParam}});
        $record = {{ModelName}}::findOrFail($recordId);
        $record->delete();

        Cache::forget('spatie.permission.cache');
        return response()->json(['status' => 'success', 'message' => __('app.delete_success')]);
    }

    public function status(Request $request): JsonResponse
    {
        $recordId = $this->decryptId((string) $request->input('id'));
        $record = {{ModelName}}::findOrFail($recordId);
        $newStatus = (int) !$record->status;
        $record->update(['status' => $newStatus]);

        Cache::forget('spatie.permission.cache');
        return response()->json([
            'status' => 'success',
            'message' => $newStatus ? __('app.activation_success') : __('app.disable_success'),
            'type' => $newStatus ? 'yes' : 'no',
        ]);
    }

    protected function decryptId(string $encryptedId): int
    {
        try {
            return (int) Crypt::decrypt($encryptedId);
        } catch (DecryptException $exception) {
            abort(404);
        }
    }

    protected function applyFilters($query, Request $request)
    {
        if ($request->filled('status')) {
            $query->where('status', $request->integer('status'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->date('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->date('to_date'));
        }
{{relationFilters}}
        return $query;
    }
}
STUB,
            'request.stub' => <<<'STUB'
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class {{RequestName}} extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
{{validationRules}}
        ];
    }
}
STUB,
            'view_index.stub' => <<<'STUB'
@extends('admin.layout.main_master')

@section('title')
    {{ $current_route->{'name_' . trans('app.lang')} }}
@stop

@section('page-content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <input type="text" id="generalSearch" class="form-control form-control-solid w-250px" placeholder="@lang('app.search')" />
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route($active_menu . '.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i>@lang('app.add')
                        </a>
                    </div>
                </div>
                <div class="card-body py-4">
                    @include('admin.layout.masterLayouts.error')
                    <table id="{{tableId}}" class="table table-row-bordered gy-5">
                        <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <th>#</th>
{{tableHead}}
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
@include('admin.' . $active_menu . '.parts.modal')
@stop

@section('js')
<script>
var table;
var tableId = '{{tableId}}';
var columns = [
    { data: 'DT_RowIndex' },
{{datatableColumnsJs}}
    { data: 'actions', responsivePriority: -1 }
];
var filterFields = ['#generalSearch'];
@include('admin.layout.masterLayouts.datatableMaster')
</script>
@stop
STUB,
            'view_form.stub' => <<<'STUB'
@extends('admin.layout.main_master')

@section('title')
    {{ $current_route->{'name_' . trans('app.lang')} }}
@stop

@section('page-content')
<div class="card">
    <div class="card-body py-4">
        @include('admin.layout.masterLayouts.error')
        <form action="{{ !empty($info) ? route($active_menu . '.update', [Str::singular($active_menu) => encrypt($info->id)]) : route($active_menu . '.store') }}" method="POST">
            @csrf
            @if(!empty($info))
                @method('PUT')
            @endif
            <div class="row justify-content-center">
                <div class="col-9">
{{formFields}}
                </div>
            </div>
            <div class="text-center pt-2">
                <button type="submit" class="btn btn-primary">@lang('app.save')</button>
                <a href="{{ route($active_menu . '.view') }}" class="btn btn-light me-3">@lang('app.cancel')</a>
            </div>
        </form>
    </div>
</div>
@stop
STUB,
            'datatable.stub' => <<<'STUB'
<?php

return [
    'table' => '{{table}}',
    'columns' => [
{{columns}}
    ],
    'filters' => [
{{filters}}
    ],
];
STUB,
        ];
    }
}

