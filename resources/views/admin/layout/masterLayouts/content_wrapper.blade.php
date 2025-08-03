<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
              <!--begin::Toolbar container-->
              <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                  <!--begin::Page title-->
                  <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                      <!--begin::Title-->
                      <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Add
                          New Contact</h1>
                      <!--end::Title-->
                      <!--begin::Breadcrumb-->
                      <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                          <!--begin::Item-->
                          <li class="breadcrumb-item text-muted">
                              <a href="../../demo1/dist/index.html" class="text-muted text-hover-primary">Home</a>
                          </li>
                          <!--end::Item-->
                          <!--begin::Item-->
                          <li class="breadcrumb-item">
                              <span class="bullet bg-gray-400 w-5px h-2px"></span>
                          </li>
                          <!--end::Item-->
                          <!--begin::Item-->
                          <li class="breadcrumb-item text-muted">Contacts</li>
                          <!--end::Item-->
                      </ul>
                      <!--end::Breadcrumb-->
                  </div>
                  <!--begin::Actions-->
                  <div class="d-flex align-items-center gap-2 gap-lg-3">
                      <!--begin::Filter menu-->
                      <div class="m-0">
                          <a href="#"
                              class="btn btn-sm btn-flex bg-body btn-color-gray-700 btn-active-color-primary fw-bold"
                              data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                              <i class="ki-duotone ki-filter fs-6 text-muted me-1">
                                  <span class="path1"></span>
                                  <span class="path2"></span>
                              </i>Filter</a>
                          <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true"
                              id="kt_menu_641ac4224902a">
                              <div class="px-7 py-5">
                                  <div class="fs-5 text-dark fw-bold">Filter Options</div>
                              </div>
                              <div class="separator border-gray-200"></div>
                              <div class="px-7 py-5">
                                  <div class="mb-10">
                                      <label class="form-label fw-semibold">Status:</label>
                                      <div>
                                          <select class="form-select form-select-solid" data-kt-select2="true"
                                              data-placeholder="Select option"
                                              data-dropdown-parent="#kt_menu_641ac4224902a" data-allow-clear="true">
                                              <option></option>
                                              <option value="1">Approved</option>
                                              <option value="2">Pending</option>
                                          </select>
                                      </div>
                                  </div>
                                  <div class="mb-10">
                                      <label class="form-label fw-semibold">Member Type:</label>
                                      <div class="d-flex">
                                          <label
                                              class="form-check form-check-sm form-check-custom form-check-solid me-5">
                                              <input class="form-check-input" type="checkbox" value="1" />
                                              <span class="form-check-label">Author</span>
                                          </label>
                                          <label class="form-check form-check-sm form-check-custom form-check-solid">
                                              <input class="form-check-input" type="checkbox" value="2"
                                                  checked="checked" />
                                              <span class="form-check-label">Customer</span>
                                          </label>
                                      </div>
                                  </div>
                                  <div class="mb-10">
                                      <label class="form-label fw-semibold">Notifications:</label>
                                      <div
                                          class="form-check form-switch form-switch-sm form-check-custom form-check-solid">
                                          <input class="form-check-input" type="checkbox" value=""
                                              name="notifications" checked="checked" />
                                          <label class="form-check-label">Enabled</label>
                                      </div>
                                  </div>
                                  <div class="d-flex justify-content-end">
                                      <button type="reset" class="btn btn-sm btn-light btn-active-light-primary me-2"
                                          data-kt-menu-dismiss="true">Reset</button>
                                      <button type="submit" class="btn btn-sm btn-primary"
                                          data-kt-menu-dismiss="true">Apply</button>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <a href="#" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal"
                          data-bs-target="#kt_modal_create_app">Create</a>
                  </div>
              </div>
    </div>
    <!--end::Toolbar-->
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-xxl">
              <!--begin::Row-->
              <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                  <!--begin::Col-->
                  @yield('page-content')
                  <!--end::Col-->
              </div>
              <!--end::Row-->
        </div>
        <!--end::Content container-->
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
