@extends('admin.layout.main_master')
@section('title')
{{ $current_route->name_ar }}
@stop
@section('page-breadcrumb')
<li class="breadcrumb-item text-muted">
    <a href="{{ url('/')}}"
       class="text-muted text-hover-primary">الرئيسية</a>
</li>
<li class="breadcrumb-item text-muted">- {{ $current_route->name_ar }}</li>
<li class="breadcrumb-item text-muted">- تحرير</li>
@stop
@section('page-content')
<div class="card">
    <div class="card-body py-4">
        @include('admin.layout.error')
        <form action="" method="POST">
            <div class="row justify-content-center">
                <div class="col-6">
                    <div class="form-floating mb-7 row ">
                        <div class="col">
                            <label class="required">الاسم</label>
                            <input type="text" value="{{ $info?$info->name:old('name') }}" name="name" class="form-control" />
                        </div>
                    </div>
                    <div class="form-floating mb row">
                        <div class="col">
                            <label class="py-3">الحالة</label>
                            <label class="form-check form-switch">
                                <?php $data = $info ? $info->status : old('status') ?>
                                <input class="form-check-input" name="status" type="checkbox" value="1" {{ $data == 1 ? 'checked="checked"' : '' }}>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center pt-2">
                {{ csrf_field() }}
                <button type="submit" class="btn btn-primary">حفظ </button>
                <a type="reset" href="{{route($active_menu.'.view')}}" class="btn btn-light me-3">الغاء الامر</a>
            </div>
        </form>
    </div>
</div>
@stop
