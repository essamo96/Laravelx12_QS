<div class="card-title">

    <div class="mb-0 mx-2">
        <label class="form-label">@lang('app.name')</label>
        <input type="text" id="generalSearch" value="{{ old('name') }}"
            class="form-control form-control-solid  ps-13 generalSearch" placeholder="@lang('app.searsh')" />
    </div>

    <div class="mb-0 mx-2">
        <label class="form-label">@lang('app.from_close_date')</label>
        <input class="form-control filter_date" value="{{ date('Y-m-01') }}" id="from_date" />
    </div>

    <div class="mb-0 mx-2">
        <label class="form-label">@lang('app.to_close_date')</label>
        <input class="form-control filter_date" value="{{ date('Y-m-t') }}" id="to_date" />
    </div>
    
    @if (Route::currentRouteName() == 'holidays.view' || Route::currentRouteName() == 'holiday_requests.view')
        {{-- || Route::currentRouteName() == 'admin.edit' --}}
        <div class="mb-0 mx-2">

            <label class="form-label">@lang('app.type_holdays_status')</label>
            <select id="holdays_status" class="form-select form-select-solid rounded-0 border-start border-end  w-200px"
                data-control="holdays_status" data-placeholder="@lang('app.type_holdays_status')">
                <option value="">@lang('app.choose')</option>

                <option value="1">@lang('app.underrevion_total_aholdays')</option> 
                <option value="0">@lang('app.waiting_total_aholdays')</option> 
                <option value="2">@lang('app.accepted_total_aholdays')</option> 
                <option value="3">@lang('app.ended_total_advances')</option> 

            </select>
        </div>

        <div class="mb-0 mx-2">

            <label class="form-label">@lang('app.type_holiday')</label>
            <select id="holdays_type" class="form-select form-select-solid rounded-0 border-start border-end  w-200px"
                data-control="holdays_type" data-placeholder="@lang('app.type_holiday')">
                <option value=" ">@lang('app.choose')</option>
                @php $vacations =  Helpers::get_vacations() @endphp
                @foreach ($vacations as $item)
                    <option value="{{ $item->id }}">
                        <?= $item->{'name_' . trans('app.lang')} ?>
                    </option>
                @endforeach
            </select>
        </div>
    @endif
</div>
