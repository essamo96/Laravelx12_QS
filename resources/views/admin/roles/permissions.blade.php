@extends('admin.layout.main_master')
@section('title')
{{ $current_route->name_ar }}
@stop
@section('page-breadcrumb')
<li class="breadcrumb-item text-muted">
    <a href="{{ url('/') }}" class="text-muted text-hover-primary">الرئيسية</a>
</li>
<li class="breadcrumb-item text-muted">- {{ $current_route->name_ar }}</li>
@stop
@section('page-content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <div class="card">
                <div class="card-body py-4">
                    @include('admin.layout.error')
                    <form id="kt_modal_add_user_form" class="form" action="" method="POST">
                        <label class="fs-5 fw-bold form-label mb-2">حدد الصلاحيات</label>
                        @foreach($permission_group as $row)
                        <div class="form-group row mb-10">
                            <label class="form-label"><b>{{ $row->name_ar }}:</b></label>
                            <div class="kt-checkbox-inline">
                                @foreach($row->permissions as $item)
                                <div class="form-check form-check-inline">
                                    <input name="permissions[]" class="form-check-input" type="checkbox" value="{{ $item->id }}" id="prm{{ $item->id }}" {{ in_array($item->id,array_column ($role_permissions,'permission_id')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="prm{{ $item->id }}">
                                        @php
                                        $parts = explode('.', $item->name);
                                        $viewPart = end($parts);
                                        @endphp
                                        {{ trans('permissions.'.$viewPart)  }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                        <div class="text-center pt-2">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-primary">حقظ </button>
                            <a type="reset" href="{{ route($active_menu . '.view') }}" class="btn btn-light me-3">الغاء الامر</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
