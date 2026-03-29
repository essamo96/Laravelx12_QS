@extends('admin.layout.main_master')

@section('title')
    {{ $current_route->{'name_' . trans('app.lang')} }}
@stop

@section('page-content')
<div class="card">
    <div class="card-body py-4">
        @include('admin.layout.masterLayouts.error')
        <form action="{{ !empty($info) ? route($active_menu . '.update', [Str::singular($active_menu) => encrypt($info->id)]) : route($active_menu . '.store') }}" method="POST">
            @csrf
            @if(!empty($info))
                @method('PUT')
            @endif
            <div class="row justify-content-center">
                <div class="col-9">
                    <div class="row mb-5">
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class="required fs-5 fw-semibold mb-2">@lang('app.name_ar')</label>
            <input type="text" class="form-control form-control-solid" name="name_ar" value="{{ $info ? $info->name_ar : old('name_ar') }}">
        </div>
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.name_en')</label>
            <input type="text" class="form-control form-control-solid" name="name_en" value="{{ $info ? $info->name_en : old('name_en') }}">
        </div>
                    </div>
                    <div class="row mb-5">
        <div class="col-md-12 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.description')</label>
            <textarea class="form-control form-control-solid" name="description" id="description">{{ $info ? $info->description : old('description') }}</textarea>
        </div>
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class="required fs-5 fw-semibold mb-2">@lang('app.age')</label>
            <input type="number" class="form-control form-control-solid" name="age" value="{{ $info ? $info->age : old('age') }}">
        </div>
                    </div>
                    <div class="row mb-5">
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.big_number')</label>
            <input type="number" class="form-control form-control-solid" name="big_number" value="{{ $info ? $info->big_number : old('big_number') }}">
        </div>
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class="required fs-5 fw-semibold mb-2">@lang('app.price')</label>
            <input type="number" class="form-control form-control-solid" name="price" value="{{ $info ? $info->price : old('price') }}">
        </div>
                    </div>
                    <div class="row mb-5">
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.weight')</label>
            <input type="number" class="form-control form-control-solid" name="weight" value="{{ $info ? $info->weight : old('weight') }}">
        </div>
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class="required fs-5 fw-semibold mb-2">@lang('app.status')</label>
            <?php $data = $info ? $info->status : old('status', 0); ?>
            <input type="hidden" name="status" value="0">
            <label class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="status" value="1" {{ (int) $data === 1 ? 'checked' : '' }}>
            </label>
        </div>
                    </div>
                    <div class="row mb-5">
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.birth_date')</label>
            <input type="date" class="form-control form-control-solid" name="birth_date" id="birth_date" value="{{ $info ? $info->birth_date : old('birth_date') }}">
        </div>
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.published_at')</label>
            <input type="date" class="form-control form-control-solid" name="published_at" id="published_at" value="{{ $info ? $info->published_at : old('published_at') }}">
        </div>
                    </div>
                    <div class="row mb-5">
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.last_login_at')</label>
            <input type="date" class="form-control form-control-solid" name="last_login_at" id="last_login_at" value="{{ $info ? $info->last_login_at : old('last_login_at') }}">
        </div>
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.uuid')</label>
            <input type="text" class="form-control form-control-solid" name="uuid" value="{{ $info ? $info->uuid : old('uuid') }}">
        </div>
                    </div>
                    <div class="row mb-5">
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.options')</label>
            <input type="text" class="form-control form-control-solid" name="options" value="{{ $info ? $info->options : old('options') }}">
        </div>
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.user_id')</label>
            <?php $data = $info ? $info->user_id : old('user_id'); ?>
            <select class="form-select form-select-solid" data-control="select2" name="user_id">
                <option value="">@lang('app.choose')</option>
                @foreach ($users as $item)
                    <option value="{{ $item->id }}" {{ (string) $data === (string) $item->id ? 'selected' : '' }}>
                        {{ $item->{'name_' . app()->getLocale()} ?? $item->name ?? $item->id }}
                    </option>
                @endforeach
            </select>
        </div>
                    </div>
                    <div class="row mb-5">
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.image')</label>
            <input type="text" class="form-control form-control-solid" name="image" value="{{ $info ? $info->image : old('image') }}">
        </div>
        <div class="col-md-6 fv-row fv-plugins-icon-container">
            <label class=" fs-5 fw-semibold mb-2">@lang('app.tags')</label>
            <input type="text" class="form-control form-control-solid" name="tags" value="{{ $info ? $info->tags : old('tags') }}">
        </div>
                    </div>
                </div>
            </div>
            <div class="text-center pt-2">
                <button type="submit" class="btn btn-primary">@lang('app.save')</button>
                <a href="{{ route($active_menu . '.view') }}" class="btn btn-light me-3">@lang('app.cancel')</a>
            </div>
        </form>
    </div>
</div>
@stop

