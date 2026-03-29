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
        ];

        if (Str::endsWith($name, '_id')) {
            $base = Str::before($name, '_id');
            $meta['is_relation'] = true;
            $meta['field_type'] = 'belongs_to';
            $meta['relation_model'] = Str::studly(Str::singular($base));
            $meta['relation_var'] = Str::camel(Str::pluralStudly(Str::studly($base)));
            $meta['relation_table'] = Str::snake(Str::pluralStudly(Str::studly($base)));
            $meta['rules'][] = 'integer';
            $meta['rules'][] = 'exists:' . $meta['relation_table'] . ',id';
        } elseif ($this->isEnum($columnType)) {
            $options = $this->parseEnumOptions($columnType);
            $meta['field_type'] = 'enum_select';
            $meta['enum_options'] = $options;
            if ($options !== []) {
                $meta['rules'][] = 'in:' . implode(',', $options);
            }
        } elseif ($this->isBoolean($dataType, $columnType, $name)) {
            $meta['field_type'] = 'boolean';
            $meta['rules'][] = 'boolean';
        } elseif ($this->isSelectByName($name)) {
            $meta['field_type'] = 'smart_select';
            $meta['rules'][] = 'string';
        } elseif (in_array($dataType, ['text', 'mediumtext', 'longtext'], true)) {
            $meta['field_type'] = 'textarea';
            $meta['rules'][] = 'string';
        } elseif (in_array($dataType, ['date', 'datetime', 'timestamp'], true)) {
            $meta['field_type'] = 'date';
            $meta['rules'][] = 'date';
        } elseif ($this->isNumeric($dataType)) {
            $meta['field_type'] = 'number';
            $meta['rules'][] = $this->isInteger($dataType) ? 'integer' : 'numeric';
            if (Str::contains($columnType, 'unsigned')) {
                $meta['rules'][] = 'min:0';
            }
        } else {
            $meta['field_type'] = 'text';
            $meta['rules'][] = 'string';
            $max = $this->extractVarcharMax($columnType);
            if ($max !== null) {
                $meta['rules'][] = 'max:' . $max;
            }
        }

        if (Str::endsWith($name, '_ar')) {
            $meta['rules'][] = 'regex:/^(?!\\d)[\\p{Arabic}0-9\\s]+$/u';
        }

        if (Str::endsWith($name, '_en')) {
            $meta['rules'][] = 'regex:/^(?!\\d)[A-Za-z0-9\\s]+$/';
        }

        return $meta;
    }

    private function labelFromCommentOrName(string $name, string $comment): array
    {
        $comment = trim($comment);
        if ($comment !== '' && Str::contains($comment, ',')) {
            [$en, $ar] = array_pad(explode(',', $comment, 2), 2, '');
            return [
                'en' => trim($en) ?: Str::headline($name),
                'ar' => trim($ar) ?: trim($en),
            ];
        }

        return [
            'en' => $comment !== '' ? $comment : Str::headline($name),
            'ar' => $comment !== '' ? $comment : Str::headline($name),
        ];
    }

    private function isEnum(string $columnType): bool
    {
        return Str::startsWith($columnType, 'enum(');
    }

    private function parseEnumOptions(string $columnType): array
    {
        if (!preg_match('/^enum\((.*)\)$/i', $columnType, $matches)) {
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
        if (!preg_match('/^varchar\((\d+)\)$/i', $columnType, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }
}

