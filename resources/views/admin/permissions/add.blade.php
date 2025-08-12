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
                    <div class="col-9">
                        <div class="form-floating mb-9 row ">
                            <div class="col">
                                <label class="p-2 required">@lang('app.name')</label>
                                <input type="text" value="{{ $info ? $info->name : old('name') }}" name="name"
                                    class="form-control" />
                            </div>
                        </div>
                        <div class="form-floating mb-9 row ">
                            <div class="col">
                                <label class="p-2 required">@lang('app.parent')</label>
                                <select class="form-select" data-control="select2" aria-label="Select example" name="group_id">
                                    <option value="0">@lang('app.choose')</option>
                                    <?php $data = $info ? $info->group_id : old('group_id'); ?>
                                    @foreach ($permissions as $item)
                                    <option value="{{ $item->id }}" {{ $data == $item->id ? 'selected' : '' }}>
                                        {{ $item->name_ar }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col">
                                <label class="p-2 required">@lang('app.guard_name')</label>
                                <select class="form-select" aria-label="Select example" name="guard_name">
                                    <option value="0">@lang('app.choose')</option>
                                    <?php $data = $info ? $info->guard_name : old('guard_name'); ?>
                                    @foreach ($guards as $item)
                                        <option value="{{ $item }}" {{ $data == $item ? 'selected' : '' }} {{ $item == 'admin' ? 'selected' : '' }}>
                                            {{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center pt-2">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-primary">@lang('app.save')</button>
                    <a type="reset" href="{{ route($active_menu . '.view') }}" class="btn btn-light me-3">@lang('app.cancel')</a>
                </div>
            </form>
        </div>
    </div>
@stop
