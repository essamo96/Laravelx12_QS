<?php

use App\Services\AdminGenerator\FieldDetectorService;

it('detects belongsTo for _id columns', function () {
    $svc = new FieldDetectorService;
    $col = (object) [
        'COLUMN_NAME' => 'user_id',
        'COLUMN_TYPE' => 'bigint unsigned',
        'DATA_TYPE' => 'bigint',
        'IS_NULLABLE' => 'YES',
        'COLUMN_COMMENT' => '',
    ];
    $meta = $svc->detect($col);
    expect($meta['is_relation'])->toBeTrue()
        ->and($meta['relation_model'])->toBe('User')
        ->and($meta['relation_table'])->toBe('users')
        ->and($meta['rules'])->toContain('exists:users,id');
});

it('parses enum options from COLUMN_TYPE', function () {
    $svc = new FieldDetectorService;
    $col = (object) [
        'COLUMN_NAME' => 'kind',
        'COLUMN_TYPE' => "enum('a','b')",
        'DATA_TYPE' => 'enum',
        'IS_NULLABLE' => 'NO',
        'COLUMN_COMMENT' => '',
    ];
    $meta = $svc->detect($col);
    expect($meta['field_type'])->toBe('enum_select')
        ->and($meta['enum_options'])->toBe(['a', 'b']);
});

it('adds arabic regex only for _ar suffix', function () {
    $svc = new FieldDetectorService;
    $col = (object) [
        'COLUMN_NAME' => 'title_ar',
        'COLUMN_TYPE' => 'varchar(100)',
        'DATA_TYPE' => 'varchar',
        'IS_NULLABLE' => 'NO',
        'COLUMN_COMMENT' => '',
    ];
    $meta = $svc->detect($col);
    $rules = implode('|', $meta['rules']);
    expect($rules)->toContain('regex:');
    expect($rules)->toContain('Arabic');
});
