@extends('admin.layout.main_master')
@section('title')
{{ $current_route->{'name_' . trans('app.lang')} }}
@stop
@section('page-content')
<div class="card">
    <div class="card-body py-4">
        @include('admin.layout.masterLayouts.error')
        <form action="" method="POST">
            <div class="row justify-content-center">
                <div class="col-6">
                    <div class="form-floating mb-7 row ">
                        <div class="col">
                            <label class="required">@lang('app.name')</label>
                            <input type="text" value="{{ $info?$info->name:old('name') }}" name="name" class="form-control" />
                        </div>
                    </div>
                    <div class="form-floating mb row">
                        <div class="col">
                            <label class="py-3">@lang('app.status')</label>
                            <label class="form-check form-switch">
                                <?php $data = $info ? $info->status : old('status') ?>
                                <input class="form-check-input" name="status" type="checkbox" value="1" {{ $data == 1 ? 'checked="checked"' : '' }}>
                            </label>
                        </div>
                        <div class="col">
                            <label class="py-3">@lang('app.is_user')</label>
                            <label class="form-check form-switch">
                                <?php $data = $info ? $info->is_user : old('is_user') ?>
                                <input class="form-check-input" name="is_user" type="checkbox" value="1" {{ $data == 1 ? 'checked="checked"' : '' }}>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center pt-2">
                {{ csrf_field() }}
                <button type="submit" class="btn btn-primary">@lang('app.save') </button>
                <a type="reset" href="{{route($active_menu.'.view')}}" class="btn btn-light me-3">@lang('app.cancel')</a>
            </div>
        </form>
    </div>
</div>
@stop
