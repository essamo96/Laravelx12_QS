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
                        {{-- <div class="col">
                            <label class="p-2 required">@lang('app.full_name')(AR) </label>
                            <input type="text" value="{{ $info ? $info->full_name_ar : old('full_name_ar') }}" name="full_name_ar"
                                   class="form-control" />
                        </div>
                        <div class="col">
                            <label class="p-2 required">@lang('app.full_name')(EN) </label>
                            <input type="text" value="{{ $info ? $info->full_name_en : old('full_name_en') }}" name="full_name_en"
                                   class="form-control" />
                        </div> --}}
                        <div class="col">
                            <label class="p-2 required">@lang('app.username') </label>
                            <input type="text" value="{{ $info ? $info->username : old('username') }}"
                                   name="username" class="form-control" />
                        </div>
                    </div>
                    {{-- <div class="form-floating mb-9 row ">
                        <div class="col">
                            <label class="p-2 required">@lang('app.identity') </label>
                            <input type="text" value="{{ $info ? $info->id_no : old('id_no') }}" name="id_no"
                                   class="form-control" />
                        </div>
                        <div class="col">
                            <label class="p-2 required">@lang('app.mobile') </label>
                            <input type="text" value="{{ $info ? $info->mobile : old('mobile') }}"
                                   name="mobile" class="form-control" />
                        </div>
                    </div> --}}

                    <div class="form-floating mb-9 row ">
                        <div class="col">
                            <label class="p-2 required"> @lang('app.password')  </label>
                            <input type="password" value="{{ $info ? $info->password : old('password') }}"
                                   name="password" class="form-control" />
                        </div>
                        <div class="col">
                            <label class="p-2 required"> @lang('app.password_confirmation')  </label>
                            <input type="password"
                                   value="{{ $info ? $info->password_confirmation : old('password_confirmation') }}"
                                   name="password_confirmation" class="form-control" />
                        </div>
                    </div>

                    <div class="form-floating mb-9 row ">
                        <div class="col">
                            <label class="p-2 required"> @lang('app.email')  </label>
                            <input type="text" placeholder="example@domain.com"
                                   value="{{ $info ? $info->email : old('email') }}" name="email" class="form-control" />
                        </div>
                        <div class="col">
                            <label class="p-2 required"> @lang('app.group')  </label>
                            <select class="form-select" aria-label="Select example" name="role_id">
                                <option>@lang('app.choose')</option>
                                <?php $data = $info ? $info->role_id : old('role_id'); ?>
                                @foreach ($roles as $item)
                                <option value="{{ $item->id }}"
                                        {{ $data == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-floating mb row">
                        <div class="col">
                            <label class="p-2 required"> @lang('app.city')</label>
                            <select id="city" class="form-select city" data-control="select2" aria-label="Select example" data-target="1" name="city_id">
                                <option value="">@lang('app.choose')</option>
                                <?php $data = $info ? $info->city_id : old('city_id'); ?>
                                @foreach ($Cities as $item)
                                <option value="{{ $item->id }}" {{ $data == $item->id ? 'selected' : '' }}>
                                    {{ $item->{'name_' . trans('app.lang')} }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label class="p-2 required"> @lang('app.governorates')</label>
                            <select id="governorates" class="form-select" data-control="select2" aria-label="Select example" name="gove_id" data-target="2">
                                    @if ($info->gove_id != null)
                                        <option value="{{ $info->gove_id }}" selected>
                                            <?= $info->Governoraty->{'name_' . trans('app.lang')} ?></option>
                                    @else
                                        <option value="">@lang('app.choose')</option>
                                    @endif
                            </select>
                        </div>
                        <div class="col">
                            <label class="p-2 "> @lang('app.neighborhood')</label>
                            <select id="street" class="form-select" data-control="select2"
                                    aria-label="Select example" name="street_id">
                                @if ($info->street_id != null)
                                <option value="{{ $info->street_id }}" selected>
                                    <?= $info->street->{'name_' . trans('app.lang')} ?></option>
                                @else
                                <option value="">@lang('app.choose')</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="form-floating mb row">
                        <div class="col my-5">
                            <div class="d-flex flex-stack mx-y">
                                <div class="fw-semibold me-5">
                                    <label class="fs-6">نوع الموظف</label>
                                    <div class="fs-7 text-muted">هل الموظف ادمن ام باحث</div>
                                </div>
                                <div class="d-flex align-items-center">

                                    <label class="form-check form-check-custom form-check-solid me-10">
                                        <input class="form-check-input h-20px w-20px" type="radio" name="emp_type" value="1" checked="checked">
                                        <span class="form-check-label fw-semibold">admin</span>
                                    </label>

                                    <label class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input h-20px w-20px" type="radio" name="emp_type" value="0">
                                        <span class="form-check-label fw-semibold">Social researcher</span>
                                    </label>

                                </div>
                            </div>
                        </div>


                        <div class="col " id="show_admin" style="display: none">
                            <label class="p-2 required"> Supervisor Name</label>
                            <select  class="form-select" data-control="select2" aria-label="Select example" name="supervisor_id">
                                <option value="">@lang('app.choose')</option>
                                <?php $data = $info ? $info->supervisor_id : old('supervisor_id'); ?>
                                @foreach ($admins as $item)
                                <option value="{{ $item->id }}" {{ $data == $item->id ? 'selected' : '' }}>
                                    <?= $item->{'full_name_' . trans('app.lang')} ?? '---' ?></option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-floating mb row">
                        <div class="col">
                            <label class="p-2 required">@lang('app.status')</label>
                            <label class="form-check form-switch">
                                <?php $data = $info ? $info->status : old('status'); ?>
                                <input class="form-check-input" name="status" type="checkbox" value="1"
                                       {{ $data == 1 ? 'checked="checked"' : '' }}>
                            </label>
                        </div>

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
<script>

    $(document).ready(function () {
        function toggleAdminDiv() {
            $('#show_admin').toggle($('input[name="emp_type"]:checked').val() === '0');
        }
        toggleAdminDiv();
        $('input[name="emp_type"]').on('change', function () {
            toggleAdminDiv();
        });
    });




</script>
<script>
    $(document).ready(function () {
        $('.city').on('change', function () {
            var cityId = $(this).val();
            var dataTarget = $(this).data('target');
            var $governoratesSelect = $('#governorates');

            if (cityId) {
            $.ajax({
            url: 'admin/get_governorates_and_streets_by_city',
                    type: 'POST',
                    data: {
                    city_id: cityId,
                            target: dataTarget
                    },
                    headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                    // Clear and populate governorates
                    $governoratesSelect.empty().append(
                            '<option value="0">@lang('app.choose')</option>');
                            if (response.governorates.length > 0) {
                    $.each(response.governorates, function(index, governorate) {
                    $governoratesSelect.append($('<option></option>')
                            .attr('value', governorate.id).text(
                            governorate.name));
                    });
                    }
                    $governoratesSelect.select2();
                    },
                    error: function() {
                    alert('Failed to fetch governorates and streets.');
                    }
            });
            } else {
            $governoratesSelect.empty().append('<option value="0">@lang('app.choose')</option>')
                    .select2();
            }
        });
    });
    $(document).ready(function () {
        $('#governorates').on('change', function () {
            var governorateId = $(this).val();
            var dataTarget = $(this).data('target');
            var cityId = $('#city').val();
            var $streetSelect = $('#street');

            if (governorateId) {
            $.ajax({
            url: 'admin/get_governorates_and_streets_by_city',
                    type: 'POST',
                    data: {
                    gove_id: governorateId,
                            cityId: cityId,
                            target: dataTarget
                    },
                    headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                    $streetSelect.empty().append(
                            '<option value="0">@lang('app.choose')</option>');
                            if (response.streets.length > 0) {
                    $.each(response.streets, function(index, street) {
                    $streetSelect.append($('<option></option>')
                            .attr('value', street.id).text(street.name));
                    });
                    }
                    $streetSelect.select2();
                    },
                    error: function() {
                    alert('Failed to fetch streets.');
                    }
            });
            } else {
            $streetSelect.empty().append('<option value="">@lang('app.choose')</option>')
                    .select2();
            }
        });
    });
</script>
@stop
