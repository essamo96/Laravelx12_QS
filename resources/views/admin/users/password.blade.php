@extends('admin.layout.main_master')
@section('title')
    {{ $current_route->{'name_' . trans('app.lang')} }}
@stop
@section('page-content')
    <div class="card">
        <div class="card-body py-4">
    <form action="" method="POST">
        <div class="card-body fs-6 py-15 px-10 py-lg-15 px-lg-15 text-gray-700">
            @include('admin.layout.masterLayouts.error')
            <div class="form-floating mb-7 row ">
                <div class="col">
                    <label class="required" for="password" style="margin-block: 5px;">@lang('app.password')</label>
                    <input type="password" value="" name="password" class="form-control" id="password" />
                </div>
                <div class="col">
                    <label for="password_confirmation" class="required" style="margin-block: 5px;">  @lang('app.password_confirmation')</label>
                    <input type="password" value="" class="form-control" id="password_confirmation"
                        name="password_confirmation" />
                </div>
            </div>
        </div>
                <div class="text-center pt-2">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-primary">@lang('app.save') </button>
                    <a type="reset" href="{{ route($active_menu . '.view') }}" class="btn btn-light me-3">@lang('app.cancel')</a>
                </div>
    </form>
        </div>
    </div>
@stop
@section('js')
