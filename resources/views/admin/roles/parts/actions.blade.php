@can('admin.'.$active_menu.'.edit')
<a href="{{ route($active_menu.'.edit', Crypt::encrypt($id)) }}"
   class="menu-link px-3  btn btn-success btn-sm "><i class="bi bi-pencil-square mx-2"> </i>
    @lang('app.edit')
</a>
@endcan
@can('admin.'.$active_menu.'.permissions')
<a href="{{ route('roles.permissions',[ 'id' => Crypt::encrypt($id)]) }}"
   class="menu-link px-3  btn btn-primary btn-sm"><i class="bi bi-pencil-square mx-2"> </i>
    @lang('app.permission')
</a>
@endcan
@can('admin.'.$active_menu.'.delete')
<a class="menu-link px-3  btn  btn-danger btn-sm" href="javascript:void(0)" data-href="{{ Crypt::encrypt($id) }}" data-bs-toggle="modal" data-bs-target="#confirm">
    <i class="bi bi-trash3-fill fs-4 me-2"></i> @lang('app.delete')
</a>
@endcan
