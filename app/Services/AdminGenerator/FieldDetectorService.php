<?php

namespace App\Services\AdminGenerator;

use Illuminate\Support\Str;

class FieldDetectorService
{
    public function detect(object $column): array
    {
        $name = $column->COLUMN_NAME;
        $dataType = strtolower((string) $column->DATA_TYPE);
        $columnType = strtolower((string) $column->COLUMN_TYPE);
        $nullable = strtoupper((string) $column->IS_NULLABLE) === 'YES';
        $label = $this->labelFromCommentOrName($name, (string) $column->COLUMN_COMMENT);

        $meta = [
            'name' => $name,
            'label' => $label,
            'data_type' => $dataType,
            'column_type' => $columnType,
            'is_nullable' => $nullable,
            'required_rule' => $nullable ? 'nullable' : 'required',
            'field_type' => 'text',
            'rules' => [$nullable ? 'nullable' : 'required'],
            'is_relation' => false,
            'relation_model' => null,
            'relation_var' => null,
            'relation_table' => null,
            'enum_options' => [],
            'tags_storage' => 'string',
        ];

        if (Str::endsWith($name, '_id')) {
            $base = Str::before($name, '_id');
            $meta['is_relation'] = true;
            $meta['field_type'] = 'belongs_to';
            $meta['relation_model'] = Str::studly(Str::singular($base));
            $meta['relation_var'] = Str::camel(Str::pluralStudly(Str::studly($base)));
            $meta['relation_table'] = Str::snake(Str::pluralStudly(Str::studly($base)));
            $meta['rules'][] = 'integer';
            $meta['rules'][] = 'exists:'.$meta['relation_table'].',id';

            return $this->finalizeRules($meta, $name);
        }

        if ($this->isEnum($columnType)) {
            $options = $this->parseEnumOptions($columnType);
            $meta['field_type'] = 'enum_select';
            $meta['enum_options'] = $options;
            if ($options !== []) {
                $meta['rules'][] = 'in:'.implode(',', $options);
            }

            return $this->finalizeRules($meta, $name);
        }

        if ($this->isBoolean($dataType, $columnType, $name)) {
            $meta['field_type'] = 'boolean';
            $meta['rules'][] = 'boolean';

            return $this->finalizeRules($meta, $name);
        }

        if ($this->isTagsColumn($name, $dataType)) {
            $meta['field_type'] = 'tags';
            $meta['tags_storage'] = $dataType === 'json' ? 'json' : 'string';
            $meta['rules'] = [$nullable ? 'nullable' : 'required', 'string'];

            return $this->finalizeRules($meta, $name);
        }

        if ($this->isImageColumn($name, $dataType, $columnType)) {
            $meta['field_type'] = 'image';
            $meta['rules'] = [
                $nullable ? 'nullable' : 'required',
                'image',
                'max:4096',
                'mimes:jpeg,png,jpg,gif,webp,svg',
            ];

            return $this->finalizeRules($meta, $name);
        }

        if ($this->isRichDescription($name, $dataType)) {
            $meta['field_type'] = 'rich_text';
            $meta['rules'][] = 'string';

            return $this->finalizeRules($meta, $name);
        }

        if (in_array($dataType, ['text', 'mediumtext', 'longtext'], true)) {
            $meta['field_type'] = 'textarea';
            $meta['rules'][] = 'string';

            return $this->finalizeRules($meta, $name);
        }

        if ($this->isSelectByName($name)) {
            $meta['field_type'] = 'smart_select';
            $meta['rules'][] = 'string';

            return $this->finalizeRules($meta, $name);
        }

        if (in_array($dataType, ['date', 'datetime', 'timestamp'], true)) {
            $meta['field_type'] = 'date';
            $meta['rules'][] = 'date';

            return $this->finalizeRules($meta, $name);
        }

        if ($this->isNumeric($dataType)) {
            $meta['field_type'] = 'number';
            $meta['rules'][] = $this->isInteger($dataType) ? 'integer' : 'numeric';
            if (Str::contains($columnType, 'unsigned')) {
                $meta['rules'][] = 'min:0';
            }

            return $this->finalizeRules($meta, $name);
        }

        $meta['field_type'] = 'text';
        $meta['rules'][] = 'string';
        $max = $this->extractVarcharMax($columnType);
        if ($max !== null) {
            $meta['rules'][] = 'max:'.$max;
        }

        return $this->finalizeRules($meta, $name);
    }

    private function finalizeRules(array $meta, string $name): array
    {
        if (Str::endsWith($name, '_ar')) {
            $meta['rules'][] = 'regex:/^(?!\d)[\p{Arabic}0-9\s]+$/u';
        }

        if (Str::endsWith($name, '_en')) {
            $meta['rules'][] = 'regex:/^(?!\d)[A-Za-z0-9\s]+$/';
        }

        return $meta;
    }

    private function isImageColumn(string $name, string $dataType, string $columnType): bool
    {
        if (! in_array($dataType, ['varchar', 'char', 'string', 'text'], true)) {
            return false;
        }

        $n = strtolower($name);
        if (preg_match('/^(.*_)?(image|photo|picture|avatar|logo|thumbnail|banner|cover|icon|img|thumb)s?$/', $n)) {
            return true;
        }

        if (preg_match('/_(image|photo|img|thumb|avatar|logo|banner|cover|picture)$/', $n)) {
            return true;
        }

        return false;
    }

    private function isTagsColumn(string $name, string $dataType): bool
    {
        if (! in_array($dataType, ['varchar', 'char', 'string', 'text', 'json', 'longtext'], true)) {
            return false;
        }

        return $name === 'tags' || Str::endsWith($name, '_tags');
    }

    private function isRichDescription(string $name, string $dataType): bool
    {
        if (! in_array($dataType, ['text', 'mediumtext', 'longtext'], true)) {
            return false;
        }

        $n = strtolower($name);

        return (bool) preg_match('/(description|details|content|body|summary|notes|bio|article|story|html|desc|تفصيل|وصف)/u', $n);
    }

    /**
     * تسميات منفصلة للغتين: ملف en لا يُملأ بنص عربي.
     * تعليقات المايقريشن غالباً: @lang('app.x'), نص عربي → نستخرج العربية بعد الفاصلة ونولّد إنجليزية من اسم العمود.
     */
    private function labelFromCommentOrName(string $name, string $comment): array
    {
        $comment = trim($comment);
        $defaultEn = Str::headline(str_replace('_', ' ', $name));

        if ($comment === '') {
            return ['en' => $defaultEn, 'ar' => $defaultEn];
        }

        if (Str::contains($comment, ',')) {
            [$part1, $part2] = array_pad(explode(',', $comment, 2), 2, '');
            $part1 = trim($part1);
            $part2 = trim($part2);

            $ar = $part2 !== '' ? $part2 : (preg_match('/\p{Arabic}/u', $part1) ? $part1 : $defaultEn);

            if (preg_match('/@lang\s*\(/i', $part1)) {
                return ['en' => $defaultEn, 'ar' => $ar];
            }

            $en = $part1 !== '' && ! preg_match('/\p{Arabic}/u', $part1) ? $part1 : $defaultEn;

            return [
                'en' => $en,
                'ar' => $ar,
            ];
        }

        if (preg_match('/\p{Arabic}/u', $comment)) {
            return ['en' => $defaultEn, 'ar' => $comment];
        }

        return ['en' => $comment, 'ar' => $comment];
    }

    private function isEnum(string $columnType): bool
    {
        return Str::startsWith($columnType, 'enum(');
    }

    private function parseEnumOptions(string $columnType): array
    {
        if (! preg_match('/^enum\((.*)\)$/i', $columnType, $matches)) {
            return [];
        }

        return collect(explode(',', $matches[1]))
            ->map(fn (string $item) => trim($item, " '\""))
            ->filter()
            ->values()
            ->all();
    }

    private function isBoolean(string $dataType, string $columnType, string $name): bool
    {
        if (in_array($dataType, ['boolean', 'bool'], true)) {
            return true;
        }

        return $dataType === 'tinyint'
            && (Str::contains($columnType, 'tinyint(1)') || Str::startsWith($name, 'is_') || $name === 'status');
    }

    private function isSelectByName(string $name): bool
    {
        return preg_match('/(status|type|level|gender)$/i', $name) === 1;
    }

    private function isNumeric(string $dataType): bool
    {
        return in_array($dataType, ['int', 'integer', 'bigint', 'smallint', 'tinyint', 'decimal', 'float', 'double'], true);
    }

    private function isInteger(string $dataType): bool
    {
        return in_array($dataType, ['int', 'integer', 'bigint', 'smallint', 'tinyint'], true);
    }

    private function extractVarcharMax(string $columnType): ?int
    {
        if (! preg_match('/^varchar\((\d+)\)$/i', $columnType, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }
}
