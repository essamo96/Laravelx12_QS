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
                        <div class="row mb-5">
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class="required fs-5 fw-semibold mb-2">@lang('app.age')</label>
                                <input type="number" class="form-control form-control-solid" name="age"
                                    value="{{ $info ? $info->age : old('age') }}">
                            </div>
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class="required fs-5 fw-semibold mb-2">@lang('app.name_ar')</label>
                                <input type="text" class="form-control form-control-solid" name="name_ar"
                                    value="{{ $info ? $info->name_ar : old('name_ar') }}">
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class="required fs-5 fw-semibold mb-2">@lang('app.price')</label>
                                <input type="number" class="form-control form-control-solid" name="price"
                                    value="{{ $info ? $info->price : old('price') }}">
                            </div>
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">@lang('app.big_number')</label>
                                <input type="number" class="form-control form-control-solid" name="big_number"
                                    value="{{ $info ? $info->big_number : old('big_number') }}">
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">@lang('app.birth_date')</label>
                                <input type="date" class="form-control form-control-solid" name="birth_date"
                                    id="birth_date" value="{{ $info ? $info->birth_date : old('birth_date') }}">
                            </div>
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">@lang('app.last_login_at')</label>
                                <input type="date" class="form-control form-control-solid" name="last_login_at"
                                    id="last_login_at" value="{{ $info ? $info->last_login_at : old('last_login_at') }}">
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">@lang('app.name_en')</label>
                                <input type="text" class="form-control form-control-solid" name="name_en"
                                    value="{{ $info ? $info->name_en : old('name_en') }}">
                            </div>
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">@lang('app.options')</label>
                                <input type="text" class="form-control form-control-solid" name="options"
                                    value="{{ $info ? $info->options : old('options') }}">
                            </div>
                        </div>
                        <div class="row mb-5">

                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">@lang('app.published_at')</label>
                                <input type="date" class="form-control form-control-solid" name="published_at"
                                    id="published_at" value="{{ $info ? $info->published_at : old('published_at') }}">
                            </div>

                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">@lang('app.user_id')</label>
                                <input type="number" class="form-control form-control-solid" name="user_id"
                                    value="{{ $info ? $info->user_id : old('user_id') }}">
                            </div>
                            
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">@lang('app.uuid')</label>
                                <input type="text" class="form-control form-control-solid" name="uuid"
                                    value="{{ $info ? $info->uuid : old('uuid') }}">
                            </div>
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">@lang('app.weight')</label>
                                <input type="number" class="form-control form-control-solid" name="weight"
                                    value="{{ $info ? $info->weight : old('weight') }}">
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="form-floating mb-9 row">
                                <div class="fv-row mb-10 col-12">
                                    <label class="fw-semibold fs-6 mb-2" for="description">@lang('app.description')</label>
                                    <textarea name="description" id="description" class="form-control form-control-solid">{{ $info ? $info->description : old('description') }}</textarea>
                                </div>
                            </div>
                            <div class="form-floating mb row">
                                <div class="col">
                                    <label class="p-2">@lang('app.status')</label>
                                    <label class="form-check form-switch">
                                        <?php $data = $info ? $info->status : old('status'); ?>
                                        <input class="form-check-input" name="status" type="checkbox" value="1"
                                            {{ $data == 1 ? "checked=\"checked\"" : '' }}>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center pt-2">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-primary">@lang('app.save')</button>
                    <a type="reset" href="{{ route($active_menu . '.view') }}"
                        class="btn btn-light me-3">@lang('app.cancel')</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script src="{{ asset('admin/ckeditor/ckeditor-classic.bundle.js') }}"></script>
    <script>
        ClassicEditor.create(document.querySelector('#description'))
            .then(editor => {
                console.log(editor);
            })
            .catch(error => {
                console.error(error);
            });
        $('#birth_date,#last_login_at,#published_at').flatpickr();
    </script>
@stop
