@extends('admin.layout.main_master')

@section('title')
    {{ $current_route->{'name_' . trans('app.lang')} }}
@stop

@section('page-content')
    <div class="card">
        <div class="card-body py-4">
            @include('admin.layout.masterLayouts.error')
            <form action="" method="POST">
                {{ csrf_field() }}
                <div class="row justify-content-center">
                    <div class="col-9">
                        <div class="row mb-3">
                            <div class="col">
                                <label class="p-2 required">@lang('app.full_name')</label>
                                <input type="text" name="name" value="{{ old('name', $info->name) }}"
                                    class="form-control" required>
                            </div>
                            <div class="col">
                                <label class="p-2 required">@lang('app.username')</label>
                                <input type="text" name="username" value="{{ old('username', $info->username) }}"
                                    class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="col">
                                <label class="p-2 required">@lang('app.email')</label>
                                <input type="email" name="email" value="{{ old('email', $info->email) }}"
                                    class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="p-2 required">@lang('app.password')</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col">
                                <label class="p-2 required">@lang('app.password_confirmation')</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="p-2 required">@lang('app.group')</label>
                                <select name="role_id" class="form-select" required>
                                    <option value="">@lang('app.choose')</option>
                                    @php $selectedRole = old('role_id', $info->role_id); @endphp
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ $selectedRole == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="col">
                                    <label class="p-2 required">@lang('app.status')</label>
                                    @php $statusValue = old('status', $info->status); @endphp
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="status" value="1"
                                            {{ $statusValue == 1 ? 'checked' : '' }}>
                                    </div>
                                </div>
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
