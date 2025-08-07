@extends('admin.layout.main_master')
@section('title')
    {{ $current_route->{'name_' . trans('app.lang')} }}
@stop
@section('page-breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ url('/') }}" class="text-muted text-hover-primary">@lang('app.home')</a>
    </li>
    <li class="breadcrumb-item text-muted">- {{ $current_route->{'name_' . trans('app.lang')} }}</li>
@stop
@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <input type="text" id="generalSearch" value="{{ old('name') }}"
                                    class="form-control form-control-solid w-250px ps-13 generalSearch"
                                    placeholder=" @lang('app.search') "/>
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                </div>
                                <a href="{{ route($active_menu . '.add') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i>@lang('app.add')</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.error')
                        <table id="kt_table" class="table table-row-bordered gy-5">
                            <thead>
                                <tr class="fw-semibold fs-6 text-muted">
                                    <th>#</th>
                                    <th>@lang('app.id')</th>
                                    <th>@lang('app.name') </th>
                                    <th>@lang('app.name_ar')</th>
                                    <th> @lang('app.name_en')</th>
                                    <th> @lang('app.parent')</th>
                                    <th> @lang('app.sort')</th>
                                    <th>@lang('app.status')</th>
                                    <th>@lang('app.actions')</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.' . $active_menu . '.parts.modal')
@stop
@section('js')
    <script>
        $(document).ready(function() {
            var table = $('#kt_table').DataTable({
                responsive: true,
                processing: true,
                "bLengthChange": false,
                "bFilter": false,
                serverSide: true,
                ajax: {
                    url: "<?= route($active_menu . '.list') ?>",
                    data: function(d) {
                        d.name = $('#generalSearch').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex'
                    },
                    {
                        data: 'id'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'name_ar'
                    },
                    {
                        data: 'name_en'
                    },
                    {
                        data: 'parent_id'
                    },
                    {
                        data: 'sort'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'actions',
                        responsivePriority: -1
                    },
                ],
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td:eq(1)').addClass('d-flex align-items-center');
                }
            });
            $('.generalSearch').on('input', function() {
                table.ajax.reload();
            });

            $('#confirm').on('show.bs.modal', function(e) {
                var link = $(e.relatedTarget);
                var href = link.data('href');
                $('.delete').on('click', function() {
                    $.ajax({
                        url: '<?= route($active_menu . '.delete') ?>',
                        type: 'POST',
                        data: {
                            id: href,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            $('#confirm').modal('hide');
                            Swal.fire({
                                text: "تم الحذف بنجاح",
                                title: "نجاح",
                                icon: "success",
                                buttonsStyling: false,
                                showConfirmButton: false,
                                timer: 2000
                            });

                            // toastr[data.status](data.message);
                            table.ajax.reload();

                        }
                    });
                });
                $('#delete_id').val(href);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#status').click(function() {
                var id = $(this).data('href');

                $.ajax({
                    type: 'POST',
                    url: '<?= route($active_menu . '.status') ?>',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        toastr[data.status](data.message);
                    },
                    error: function(error) {
                        Swal.fire({
                            title: "Oops...",
                            text: "Something went wrong!",
                            icon: "error",
                        });
                    }
                });
            });
            $(document).on('click', '.status', function() {
                var id = $(this).data('href');
                $.ajax({
                    type: 'POST',
                    url: '<?= route($active_menu . '.status') ?>',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        toastr[data.status](data.message);
                    },
                    error: function(error) {
                        Swal.fire({
                            title: "Oops...",
                            text: "Something went wrong!",
                            icon: "error",
                        });
                    }
                });
            });
        });
    </script>
@stop
