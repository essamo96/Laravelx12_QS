<?php

namespace App\Console\Commands;

use App\Services\AdminGenerator\FieldDetectorService;
use App\Services\AdminGenerator\SchemaReaderService;
use App\Services\AdminGenerator\StubGeneratorService;
use App\Services\AdminGenerator\TranslationService;
use App\Services\PermissionGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ScaffoldUI extends Command
{
    protected $signature = 'make:essam2 {name} {table? : Database table (default: snake plural of name)}';
    protected $description = 'Generate admin CRUD scaffold from database table';

    public function __construct(
        private readonly SchemaReaderService $schemaReader,
        private readonly FieldDetectorService $fieldDetector,
        private readonly StubGeneratorService $stubGenerator,
        private readonly TranslationService $translationService,
        private readonly PermissionGeneratorService $permissionGenerator
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $modelName = Str::studly($name);
        $tableName = (string) ($this->argument('table') ?? Str::snake(Str::pluralStudly($modelName)));
        $pluralStudly = Str::pluralStudly($modelName);
        $resourceName = Str::snake($pluralStudly);
        $controllerName = "{$pluralStudly}Controller";
        $requestName = "{$modelName}Request";

        $migrationName = $this->permissionGenerator->createTableMigrationIfMissing($tableName);
        if ($migrationName !== null) {
            $this->warn("Created migration [{$migrationName}]. Run migrations and define columns, then re-run if needed.");
        }

        $columns = $this->schemaReader->getColumns($tableName);
        if ($columns->isEmpty()) {
            $this->error("Table '{$tableName}' was not found or has no scannable columns. Migrate the table first.");
            return self::FAILURE;
        }

        $this->stubGenerator->ensureDefaultStubs();
        $data = $this->prepareScaffoldData($columns->all(), $resourceName, $tableName, $modelName);

        $this->translationService->update($tableName, $data['translations']);
        $this->generateModel($modelName, $data);
        $this->generateRequest($requestName, $data);
        $this->generateController($controllerName, $modelName, $requestName, $resourceName, $tableName, $data);
        $this->generateViews($resourceName, $data);
        $this->generateDatatableConfig($tableName, $data);
        $this->generateRoutes($tableName, $controllerName);
        $this->generatePolicy($modelName);
        $this->registerMenu($modelName, $resourceName);

        $this->permissionGenerator->syncPermissionsForTable($tableName);
        $this->permissionGenerator->assignAllPermissionsToSuperAdmin();

        $this->info("Admin generator completed for {$modelName} ({$tableName}).");
        return self::SUCCESS;
    }

    protected function prepareScaffoldData(array $columns, string $resourceName, string $tableName, string $modelName): array
    {
        $fillable = [];
        $searchable = [];
        $datatableColumns = [];
        $datatableColumnsJs = [];
        $tableHead = [];
        $formFields = [];
        $validationRules = [];
        $translations = [];
        $relations = [];
        $relationUses = [];
        $relationLoaders = [];
        $relationFilterKeys = [];
        $relationFilters = [];
        $booleanFields = [];
        $datatableConfigColumns = [];
        $datatableConfigFilters = ["        'status'", "        'from_date'", "        'to_date'"];
        $imageFields = [];
        $tagsFields = [];
        $richTextFields = [];
        $castsJson = [];
        $relationFilterBlocks = [];
        $relationColumnNames = [];

        foreach ($columns as $column) {
            $meta = $this->fieldDetector->detect($column);
            $name = $meta['name'];
            $fillable[] = $name;
            $translations[$name] = $meta['label'];
            $validationRules[$name] = implode('|', $meta['rules']);

            if ($meta['field_type'] === 'image') {
                $imageFields[] = $name;
            }
            if ($meta['field_type'] === 'tags') {
                $tagsFields[] = $meta;
                if (($meta['tags_storage'] ?? 'string') === 'json') {
                    $castsJson[] = "        '{$name}' => 'array',";
                }
            }
            if ($meta['field_type'] === 'rich_text') {
                $richTextFields[] = $name;
            }

            if (!$meta['is_nullable']) {
                $datatableColumns[] = $name;
                $datatableColumnsJs[] = "    { data: '{$name}' },";
                $tableHead[] = "                            <th>@lang('app.{$name}')</th>";
                $datatableConfigColumns[] = "        '{$name}'";
            }

            if (in_array($meta['field_type'], ['text', 'textarea', 'rich_text', 'smart_select', 'enum_select', 'tags'], true)) {
                $searchable[] = $name;
            }

            if ($meta['field_type'] === 'boolean') {
                $booleanFields[] = $name;
                $datatableConfigFilters[] = "        '{$name}'";
            }

            if ($meta['is_relation']) {
                $relations[] = $this->buildModelRelationMethod($meta['relation_model'], $name);
                $relationUses[$meta['relation_model']] = "use App\\Models\\{$meta['relation_model']};";
                $relationLoaders[$meta['relation_var']] = "        parent::\$data['{$meta['relation_var']}'] = {$meta['relation_model']}::query()->get();";
                $relationFilterKeys[] = ", '{$name}'";
                $relationFilters[] = "        if (\$request->filled('{$name}')) { \$query->where('{$name}', \$request->integer('{$name}')); }";
                $datatableConfigFilters[] = "        '{$name}'";
                $relationFilterBlocks[] = $this->buildRelationFilterBlock($meta);
                $relationColumnNames[] = $name;
            }

            $formFields[] = $this->buildFieldTemplate($meta);
        }

        $hasMultipart = $imageFields !== [];

        return [
            'fillable' => $this->arrayLines($fillable, 8),
            'searchable' => $this->arrayLines(array_values(array_unique($searchable)), 8),
            'datatableColumns' => $this->arrayLines($datatableColumns, 8),
            'datatableColumnsJs' => implode("\n", $datatableColumnsJs),
            'tableHead' => implode("\n", $tableHead),
            'formFields' => $this->buildRows($formFields),
            'validationRules' => $this->ruleLines($validationRules),
            'relations' => implode("\n", array_unique($relations)),
            'relatedModelUses' => implode("\n", array_values($relationUses)),
            'relationLoaders' => implode("\n", array_values($relationLoaders)),
            'relationFilterKeys' => implode('', array_unique($relationFilterKeys)),
            'relationFilters' => implode("\n", array_unique($relationFilters)),
            'booleanNormalizeStore' => $this->normalizeBooleanCode($booleanFields),
            'booleanNormalizeUpdate' => $this->normalizeBooleanCode($booleanFields),
            'translations' => $translations,
            'tableId' => $resourceName,
            'datatableConfigColumns' => implode(",\n", array_unique($datatableConfigColumns)),
            'datatableConfigFilters' => implode(",\n", array_unique($datatableConfigFilters)),
            'modelCasts' => $castsJson !== [] ? implode("\n", $castsJson) : '',
            'imageFieldNamesArray' => $this->arrayLines($imageFields, 8),
            'imageStoreCode' => $this->buildImageStoreCode($imageFields, $tableName),
            'imageUpdateCode' => $this->buildImageUpdateCode($imageFields, $tableName),
            'tagsNormalizeCode' => $this->buildTagsNormalizeCode($tagsFields),
            'deleteModelFilesCode' => $this->buildDeleteModelFilesCode($imageFields),
            'listImageEditColumns' => $this->buildListImageEditColumns($imageFields),
            'listRawColumns' => $this->buildListRawColumns($imageFields),
            'globalSearchFilter' => $this->buildGlobalSearchFilter($modelName),
            'formMultipartFlag' => $hasMultipart ? 'true' : 'false',
            'formJsSection' => $this->buildFormJsSection($richTextFields, $tagsFields),
            'filterPanelHtml' => $this->buildFilterPanelHtml($relationFilterBlocks),
            'filterFieldsJs' => $this->buildFilterFieldsJs($relationColumnNames),
            'modelCastsMethod' => $this->buildModelCastsMethod($castsJson !== [] ? implode("\n", $castsJson) : ''),
        ];
    }

    protected function buildFieldTemplate(array $meta): string
    {
        $name = $meta['name'];
        $requiredClass = $meta['is_nullable'] ? '' : 'required';

        if ($meta['is_relation']) {
            $var = $meta['relation_var'];
            return <<<HTML
                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                        <label class="{$requiredClass} fs-5 fw-semibold mb-2">@lang('app.{$name}')</label>
                        <?php \$data = \$info ? \$info->{$name} : old('{$name}'); ?>
                        <select class="form-select form-select-solid" data-control="select2" name="{$name}">
                            <option value="">@lang('app.choose')</option>
                            @foreach (\${$var} as \$item)
                                <option value="{{ \$item->id }}" {{ (string) \$data === (string) \$item->id ? 'selected' : '' }}>
                                    {{ \$item->{'name_' . app()->getLocale()} ?? \$item->name ?? \$item->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
            HTML;
        }

        if ($meta['field_type'] === 'boolean') {
            return <<<HTML
                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                        <label class="{$requiredClass} fs-5 fw-semibold mb-2">@lang('app.{$name}')</label>
                        <?php \$data = \$info ? \$info->{$name} : old('{$name}', 0); ?>
                        <input type="hidden" name="{$name}" value="0">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="{$name}" value="1" {{ (int) \$data === 1 ? 'checked' : '' }}>
                        </label>
                    </div>
            HTML;
        }

        if ($meta['field_type'] === 'image') {
            return <<<HTML
                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                        <label class="{$requiredClass} fs-5 fw-semibold mb-2">@lang('app.{$name}')</label>
                        <input type="file" name="{$name}" accept="image/*" class="form-control form-control-solid" />
                        @if(!empty(\$info) && !empty(\$info->{$name}))
                            <div class="mt-3">
                                <img src="{{ asset('storage/' . \$info->{$name}) }}" alt="" class="w-125px h-125px object-fit-cover rounded border" />
                            </div>
                        @endif
                    </div>
            HTML;
        }

        if ($meta['field_type'] === 'tags') {
            return <<<HTML
                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                        <label class="{$requiredClass} fs-5 fw-semibold mb-2">@lang('app.{$name}')</label>
                        <input type="text" class="form-control form-control-solid" name="{$name}" id="tagify_{$name}"
                            data-kt-tagify="1"
                            value="{{ old('{$name}', '') }}"
                            @if(!empty(\$info))
                                data-initial-tags='@json(\$info->{$name} ?? [])'
                            @endif />
                    </div>
            HTML;
        }

        if ($meta['field_type'] === 'rich_text') {
            return <<<HTML
                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                        <label class="{$requiredClass} fs-5 fw-semibold mb-2">@lang('app.{$name}')</label>
                        <textarea class="form-control form-control-solid" name="{$name}" id="ckeditor_{$name}" data-kt-ckeditor="1" rows="6">{{ old('{$name}', \$info->{$name} ?? '') }}</textarea>
                    </div>
            HTML;
        }

        if ($meta['field_type'] === 'textarea') {
            return <<<HTML
                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                        <label class="{$requiredClass} fs-5 fw-semibold mb-2">@lang('app.{$name}')</label>
                        <textarea class="form-control form-control-solid" name="{$name}" id="{$name}">{{ \$info ? \$info->{$name} : old('{$name}') }}</textarea>
                    </div>
            HTML;
        }

        if (in_array($meta['field_type'], ['enum_select', 'smart_select'], true)) {
            $optionsHtml = "<option value=\"\">@lang('app.choose')</option>";
            foreach ($meta['enum_options'] as $option) {
                $safe = e($option);
                $optionsHtml .= "\n                            <option value=\"{$safe}\" {{ (\$info ? \$info->{$name} : old('{$name}')) == '{$safe}' ? 'selected' : '' }}>{$safe}</option>";
            }

            return <<<HTML
                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                        <label class="{$requiredClass} fs-5 fw-semibold mb-2">@lang('app.{$name}')</label>
                        <select class="form-select form-select-solid" name="{$name}">
                            {$optionsHtml}
                        </select>
                    </div>
            HTML;
        }

        if ($meta['field_type'] === 'date') {
            return <<<HTML
                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                        <label class="{$requiredClass} fs-5 fw-semibold mb-2">@lang('app.{$name}')</label>
                        <input type="date" class="form-control form-control-solid" name="{$name}" id="{$name}" value="{{ \$info ? \$info->{$name} : old('{$name}') }}">
                    </div>
            HTML;
        }

        $inputType = $meta['field_type'] === 'number' ? 'number' : 'text';
        return <<<HTML
                <div class="col-md-6 fv-row fv-plugins-icon-container">
                    <label class="{$requiredClass} fs-5 fw-semibold mb-2">@lang('app.{$name}')</label>
                    <input type="{$inputType}" class="form-control form-control-solid" name="{$name}" value="{{ \$info ? \$info->{$name} : old('{$name}') }}">
                </div>
        HTML;
    }

    protected function generateModel(string $modelName, array $data): void
    {
        $content = $this->stubGenerator->render('model.stub', [
            'ModelName' => $modelName,
            'fillable' => $data['fillable'],
            'searchable' => $data['searchable'],
            'datatableColumns' => $data['datatableColumns'],
            'modelCastsMethod' => $data['modelCastsMethod'] ?? '',
            'relations' => $data['relations'],
        ]);

        $this->stubGenerator->write(app_path("Models/{$modelName}.php"), $content);
    }

    protected function generateRequest(string $requestName, array $data): void
    {
        $content = $this->stubGenerator->render('request.stub', [
            'RequestName' => $requestName,
            'validationRules' => $data['validationRules'],
        ]);
        $this->stubGenerator->write(app_path("Http/Requests/Admin/{$requestName}.php"), $content);
    }

    protected function generateController(string $controllerName, string $modelName, string $requestName, string $resourceName, string $tableName, array $data): void
    {
        $routeParam = Str::singular($tableName);
        $content = $this->stubGenerator->render('controller.stub', [
            'ControllerName' => $controllerName,
            'ModelName' => $modelName,
            'RequestName' => $requestName,
            'PathName' => $resourceName,
            'RouteParam' => $routeParam,
            'relatedModelUses' => $data['relatedModelUses'],
            'relationLoaders' => $data['relationLoaders'],
            'relationFilterKeys' => $data['relationFilterKeys'],
            'relationFilters' => $data['relationFilters'],
            'booleanNormalizeStore' => $data['booleanNormalizeStore'],
            'booleanNormalizeUpdate' => $data['booleanNormalizeUpdate'],
            'imageFieldNamesArray' => $data['imageFieldNamesArray'] ?? '',
            'formMultipartFlag' => $data['formMultipartFlag'] ?? 'false',
            'imageStoreCode' => $data['imageStoreCode'] ?? '',
            'imageUpdateCode' => $data['imageUpdateCode'] ?? '',
            'tagsNormalizeCode' => $data['tagsNormalizeCode'] ?? '',
            'deleteModelFilesCode' => $data['deleteModelFilesCode'] ?? '',
            'listImageEditColumns' => $data['listImageEditColumns'] ?? '',
            'listRawColumns' => $data['listRawColumns'] ?? "'status', 'actions'",
            'globalSearchFilter' => $data['globalSearchFilter'] ?? '',
        ]);

        $this->stubGenerator->write(app_path("Http/Controllers/admin/{$controllerName}.php"), $content);
    }

    protected function generateViews(string $resourceName, array $data): void
    {
        $viewsPath = resource_path("views/admin/{$resourceName}");
        File::ensureDirectoryExists($viewsPath . '/parts');
        $this->copyViewParts($viewsPath);

        $view = $this->stubGenerator->render('view_index.stub', [
            'tableId' => $data['tableId'],
            'tableHead' => $data['tableHead'],
            'datatableColumnsJs' => $data['datatableColumnsJs'],
            'filterPanelHtml' => $data['filterPanelHtml'] ?? '',
            'filterFieldsJs' => $data['filterFieldsJs'] ?? "var filterFields = ['#generalSearch'];",
        ]);

        $form = $this->stubGenerator->render('view_form.stub', [
            'formFields' => $data['formFields'],
            'formJsSection' => $data['formJsSection'] ?? '',
        ]);

        $this->stubGenerator->write($viewsPath . '/view.blade.php', $view);
        $this->stubGenerator->write($viewsPath . '/add.blade.php', $form);
    }

    protected function generateDatatableConfig(string $tableName, array $data): void
    {
        $content = $this->stubGenerator->render('datatable.stub', [
            'table' => $tableName,
            'columns' => $data['datatableConfigColumns'],
            'filters' => $data['datatableConfigFilters'],
        ]);
        $this->stubGenerator->write(config_path("datatable/{$tableName}.php"), $content);
    }

    protected function generateRoutes(string $tableName, string $controllerName): void
    {
        $routePath = base_path("routes/{$tableName}.php");
        $resourceName = $tableName;
        $resourceParam = Str::singular($tableName);

        $content = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::get('{$resourceName}/list', ['as' => '{$resourceName}.list', 'uses' => '{$controllerName}@list']);
Route::post('{$resourceName}/status', ['as' => '{$resourceName}.status', 'uses' => '{$controllerName}@status']);
Route::post('{$resourceName}/delete', ['as' => '{$resourceName}.delete', 'middleware' => ['permission:admin.{$resourceName}.delete'], 'uses' => '{$controllerName}@postDelete']);

Route::resource('{$resourceName}', '{$controllerName}')
    ->parameters(['{$resourceName}' => '{$resourceParam}'])
    ->names([
        'index' => '{$resourceName}.view',
        'create' => '{$resourceName}.create',
        'store' => '{$resourceName}.store',
        'edit' => '{$resourceName}.edit',
        'update' => '{$resourceName}.update',
        'destroy' => '{$resourceName}.destroy',
    ])
    ->except(['show']);
PHP;

        $this->stubGenerator->write($routePath, $content);
        $this->injectRouteIncludeIntoWeb($tableName);
    }

    protected function generatePolicy(string $modelName): void
    {
        $policyName = "{$modelName}Policy";
        $content = <<<PHP
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\\{$modelName};
use Illuminate\Support\Str;

class {$policyName}
{
    public function viewAny(User \$user): bool
    {
        return \$user->can('admin.' . Str::snake(Str::pluralStudly('{$modelName}')) . '.view');
    }

    public function view(User \$user, {$modelName} \$model): bool
    {
        return \$this->viewAny(\$user);
    }

    public function create(User \$user): bool
    {
        return \$user->can('admin.' . Str::snake(Str::pluralStudly('{$modelName}')) . '.add');
    }

    public function update(User \$user, {$modelName} \$model): bool
    {
        return \$user->can('admin.' . Str::snake(Str::pluralStudly('{$modelName}')) . '.edit');
    }

    public function delete(User \$user, {$modelName} \$model): bool
    {
        return \$user->can('admin.' . Str::snake(Str::pluralStudly('{$modelName}')) . '.delete');
    }
}
PHP;
        $this->stubGenerator->write(app_path("Policies/{$policyName}.php"), $content);
    }

    protected function registerMenu(string $modelName, string $resourceName): void
    {
        $path = config_path('admin_menu.php');
        $menu = File::exists($path) ? include $path : [];
        $menu[$resourceName] = [
            'name' => $resourceName,
            'name_ar' => $modelName,
            'name_en' => $modelName,
            'route' => "{$resourceName}.view",
        ];
        File::put($path, "<?php\n\nreturn " . var_export($menu, true) . ";\n");
    }

    protected function buildRows(array $fields): string
    {
        $rows = '';
        $chunked = array_chunk($fields, 2);
        foreach ($chunked as $chunk) {
            $rows .= "                    <div class=\"row mb-5\">\n" . implode("\n", $chunk) . "\n                    </div>\n";
        }
        return rtrim($rows);
    }

    protected function copyViewParts(string $viewsPath): void
    {
        $stubParts = resource_path('stubs/views/parts');
        foreach (['actions.blade.php', 'status.blade.php', 'modal.blade.php', 'general.blade.php'] as $file) {
            $source = $stubParts . DIRECTORY_SEPARATOR . $file;
            if (File::exists($source)) {
                File::put($viewsPath . '/parts/' . $file, File::get($source));
            }
        }
    }

    protected function injectRouteIncludeIntoWeb(string $tableName): void
    {
        $webFile = base_path('routes/web.php');
        $line = "    // {$tableName} Route\n    require __DIR__ . '/{$tableName}.php';\n";
        $content = File::get($webFile);

        if (Str::contains($content, $line)) {
            return;
        }

        $anchor = "    // tests Route\n    require __DIR__ . '/tests.php';";
        if (Str::contains($content, $anchor)) {
            $content = str_replace($anchor, $anchor . "\n\n" . $line, $content);
        } else {
            $content .= "\n" . $line;
        }

        File::put($webFile, $content);
    }

    protected function arrayLines(array $items, int $spaces = 8): string
    {
        $pad = str_repeat(' ', $spaces);
        return collect($items)
            ->map(fn (string $item) => $pad . "'" . $item . "',")
            ->implode("\n");
    }

    protected function ruleLines(array $rules): string
    {
        return collect($rules)
            ->map(fn (string $rule, string $field) => "            '{$field}' => '{$rule}',")
            ->implode("\n");
    }

    protected function buildModelRelationMethod(string $relatedModel, string $columnName): string
    {
        $relationName = Str::camel(Str::before($columnName, '_id'));
        return <<<PHP

    public function {$relationName}()
    {
        return \$this->belongsTo({$relatedModel}::class, '{$columnName}');
    }
PHP;
    }

    protected function normalizeBooleanCode(array $booleanFields): string
    {
        if ($booleanFields === []) {
            return '';
        }

        return collect($booleanFields)
            ->map(fn (string $field) => "        \$data['{$field}'] = \$request->boolean('{$field}') ? 1 : 0;")
            ->implode("\n");
    }

    protected function buildModelCastsMethod(string $castsBody): string
    {
        if ($castsBody === '') {
            return '';
        }

        return "    protected function casts(): array\n    {\n        return [\n".$castsBody."\n        ];\n    }\n";
    }

    protected function buildImageStoreCode(array $names, string $tableDir): string
    {
        if ($names === []) {
            return '';
        }

        $dir = 'uploads/'.$tableDir;
        $lines = [
            '        foreach ($this->imageFieldNames as $imageField) {',
            '            unset($data[$imageField]);',
            '        }',
        ];
        foreach ($names as $n) {
            $lines[] = "        if (\$request->hasFile('{$n}')) {";
            $lines[] = "            \$data['{$n}'] = \$request->file('{$n}')->store('{$dir}', 'public');";
            $lines[] = '        }';
        }

        return implode("\n", $lines);
    }

    protected function buildImageUpdateCode(array $names, string $tableDir): string
    {
        if ($names === []) {
            return '';
        }

        $dir = 'uploads/'.$tableDir;
        $lines = [
            '        foreach ($this->imageFieldNames as $imageField) {',
            '            unset($data[$imageField]);',
            '        }',
        ];
        foreach ($names as $n) {
            $lines[] = "        if (\$request->hasFile('{$n}')) {";
            $lines[] = "            if (\$record->{$n}) {";
            $lines[] = "                Storage::disk('public')->delete(\$record->{$n});";
            $lines[] = '            }';
            $lines[] = "            \$data['{$n}'] = \$request->file('{$n}')->store('{$dir}', 'public');";
            $lines[] = '        }';
        }

        return implode("\n", $lines);
    }

    protected function buildTagsNormalizeCode(array $tagsFields): string
    {
        if ($tagsFields === []) {
            return '';
        }

        $blocks = [];
        foreach ($tagsFields as $meta) {
            $name = $meta['name'];
            $storage = $meta['tags_storage'] ?? 'string';
            $assign = $storage === 'json'
                ? "\$data['{$name}'] = \$values;"
                : "\$data['{$name}'] = implode(',', \$values);";

            $blocks[] = <<<PHP
        if (\$request->has('{$name}')) {
            \$raw = \$request->input('{$name}');
            \$decoded = json_decode(\$raw, true);
            if (is_array(\$decoded)) {
                \$values = array_values(array_filter(array_map(static function (\$r) {
                    return is_array(\$r) ? (string) (\$r['value'] ?? '') : (string) \$r;
                }, \$decoded)));
                {$assign}
            }
        }
PHP;
        }

        return implode("\n", $blocks);
    }

    protected function buildDeleteModelFilesCode(array $imageFields): string
    {
        if ($imageFields === []) {
            return '';
        }

        return <<<'PHP'
        foreach ($this->imageFieldNames as $field) {
            if (! empty($record->{$field})) {
                Storage::disk('public')->delete($record->{$field});
            }
        }
PHP;
    }

    protected function buildListImageEditColumns(array $imageFields): string
    {
        if ($imageFields === []) {
            return '';
        }

        $blocks = [];
        foreach ($imageFields as $f) {
            $blocks[] = "            ->editColumn('{$f}', function (\$row) {";
            $blocks[] = "                if (empty(\$row->{$f})) {";
            $blocks[] = "                    return '';";
            $blocks[] = '                }';
            $blocks[] = "                return '<img src=\"' . e(asset('storage/' . \$row->{$f})) . '\" class=\"w-50px h-50px rounded object-fit-cover border\" alt=\"\" />';";
            $blocks[] = '            })';
        }

        return implode("\n", $blocks);
    }

    protected function buildListRawColumns(array $imageFields): string
    {
        $cols = ['status', 'actions'];
        foreach ($imageFields as $f) {
            $cols[] = $f;
        }

        return "'".implode("', '", $cols)."'";
    }

    protected function buildGlobalSearchFilter(string $modelName): string
    {
        return <<<PHP
        \$searchable = (new \\App\\Models\\{$modelName})->searchable;
        if (\$request->filled('generalSearch') && \$searchable !== []) {
            \$term = '%' . addcslashes(\$request->string('generalSearch'), '%_\\\\') . '%';
            \$query->where(function (\$q) use (\$searchable, \$term) {
                foreach (\$searchable as \$col) {
                    \$q->orWhere(\$col, 'like', \$term);
                }
            });
        }
PHP;
    }

    protected function buildRelationFilterBlock(array $meta): string
    {
        $name = $meta['name'];
        $var = $meta['relation_var'];

        return <<<HTML
                        <div class="d-flex flex-column flex-wrap gap-2">
                            <label class="form-label fw-semibold mb-0">@lang('app.{$name}')</label>
                            <select id="filter_{$name}" name="{$name}" class="form-select form-select-solid w-200px">
                                <option value="">@lang('app.all')</option>
                                @foreach (\${$var} as \$item)
                                    <option value="{{ \$item->id }}" {{ (string) request('{$name}') === (string) \$item->id ? 'selected' : '' }}>
                                        {{ \$item->{'name_' . app()->getLocale()} ?? \$item->name ?? \$item->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
HTML;
    }

    protected function buildFilterPanelHtml(array $relationBlocks): string
    {
        $rel = implode("\n", $relationBlocks);

        return <<<BLADE
                    <div class="d-flex flex-wrap flex-stack gap-3 gap-lg-5 mb-6">
                        <div class="d-flex flex-column flex-wrap gap-2">
                            <label class="form-label fw-semibold mb-0">@lang('app.search')</label>
                            <input type="text" id="generalSearch" name="generalSearch" class="form-control form-control-solid w-250px" placeholder="@lang('app.search')" value="{{ request('generalSearch') }}" />
                        </div>
                        <div class="d-flex flex-column flex-wrap gap-2">
                            <label class="form-label fw-semibold mb-0">@lang('app.status')</label>
                            <select id="filter_status" name="status" class="form-select form-select-solid w-150px">
                                <option value="">@lang('app.all')</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>@lang('app.active')</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>@lang('app.inactive')</option>
                            </select>
                        </div>
                        <div class="d-flex flex-column flex-wrap gap-2">
                            <label class="form-label fw-semibold mb-0">@lang('app.from_date')</label>
                            <input type="text" id="filter_from_date" name="from_date" class="form-control form-control-solid w-150px" value="{{ request('from_date') }}" autocomplete="off" />
                        </div>
                        <div class="d-flex flex-column flex-wrap gap-2">
                            <label class="form-label fw-semibold mb-0">@lang('app.to_date')</label>
                            <input type="text" id="filter_to_date" name="to_date" class="form-control form-control-solid w-150px" value="{{ request('to_date') }}" autocomplete="off" />
                        </div>
{$rel}
                    </div>
BLADE;
    }

    protected function buildFilterFieldsJs(array $relationColumnNames): string
    {
        $fields = ["'#generalSearch'", "'#filter_status'", "'#filter_from_date'", "'#filter_to_date'"];
        foreach ($relationColumnNames as $n) {
            $fields[] = "'#filter_{$n}'";
        }

        return 'var filterFields = ['."\n        ".implode(",\n        ", $fields)."\n    ];";
    }

    protected function buildFormJsSection(array $richTextFields, array $tagsMeta): string
    {
        if ($richTextFields === [] && $tagsMeta === []) {
            return '';
        }

        $lines = ["@section('js')"];
        if ($tagsMeta !== []) {
            $lines[] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" />';
            $lines[] = '<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>';
        }
        if ($richTextFields !== []) {
            $lines[] = "<script src=\"{{ asset('admin/assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}\"></script>";
        }
        $lines[] = '<script>';
        $lines[] = "document.addEventListener('DOMContentLoaded', function () {";
        foreach ($richTextFields as $name) {
            $lines[] = "    (function () {";
            $lines[] = "        var el = document.querySelector('#ckeditor_{$name}');";
            $lines[] = "        if (el && typeof ClassicEditor !== 'undefined') { ClassicEditor.create(el).catch(function () {}); }";
            $lines[] = '    })();';
        }
        if ($tagsMeta !== []) {
            $lines[] = "    document.querySelectorAll('[data-kt-tagify]').forEach(function (input) {";
            $lines[] = "        if (typeof Tagify === 'undefined') { return; }";
            $lines[] = "        var initial = input.getAttribute('data-initial-tags');";
            $lines[] = '        var tagify = new Tagify(input, { dropdown: { enabled: 0 }, delimiters: ",", maxTags: 40 });';
            $lines[] = '        if (initial) {';
            $lines[] = '            try {';
            $lines[] = '                var arr = JSON.parse(initial);';
            $lines[] = '                if (Array.isArray(arr)) { tagify.addTags(arr.map(function (t) { return typeof t === "string" ? t : (t && t.value) ? t.value : String(t); })); }';
            $lines[] = '            } catch (e) {}';
            $lines[] = '        }';
            $lines[] = '    });';
        }
        $lines[] = '});';
        $lines[] = '</script>';
        $lines[] = '@stop';

        return implode("\n", $lines);
    }
}

