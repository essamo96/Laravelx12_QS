@extends('admin.layout.master')
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
<div class="card">
    <div class="card-body py-4">
        @include('admin.layout.error')
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="row justify-content-center">
                <div class="col-9">
                    <div class="form-floating mb-9 row ">
                        <div class="col">
                            <label class="col-form-label required fw-semibold fs-6">الاسم</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name',$info->name) }}" />
                        </div>
                        <div class="col">
                            <label class="col-form-label required fw-semibold fs-6">الرمز</label>
                            <input type="text" name="coutry_prefix" class="form-control" value="{{ old('coutry_prefix',$info->coutry_prefix) }}" />
                        </div>
                        <div class="col">
                            <label class="col-form-label required fw-semibold fs-6">الترتيب</label>
                            <input type="text" value="{{ old('coutry_order',$info->coutry_order) }}" name="coutry_order" class="form-control" />
                        </div>
                    </div>
                    <div class="form-floating mb row">

                    </div>
                    <div class="form-floating mb row">
                        <div class="col">
                            <label class="p-2">الحالة</label>
                            <label class="form-check form-switch">
                                <input class="form-check-input" name="status" type="checkbox" value="1" {{ old('status',$info->status) == 1 ? 'checked="checked"' : '' }}>
                            </label>
                        </div>

                    </div>
                </div>
            </div>
            <div class="text-center pt-2">
                {{ csrf_field() }}
                <button type="submit" class="btn btn-primary">حقظ </button>
                <a type="reset" href="{{ route($active_menu . '.view') }}" class="btn btn-light me-3">الغاء الامر</a>
            </div>
        </form>
    </div>
</div>
@stop