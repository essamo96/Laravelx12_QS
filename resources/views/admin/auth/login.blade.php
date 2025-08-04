<x-guest-layout>
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <div class="d-flex flex-lg-row-fluid">
            <div class="d-flex flex-column flex-center pb-0 pb-lg-10 p-10 w-100">
                <img class="theme-light-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20" src="admin/assets/media/auth/agency.png" alt="" />
                <img class="theme-dark-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20" src="admin/assets/media/auth/agency-dark.png" alt="" />
                <h1 class="text-gray-800 fs-2qx fw-bold text-center mb-7">Fast, Efficient and Productive</h1>
                <div class="text-gray-600 fs-base text-center fw-semibold">
                    In this kind of post,
                    <a href="#" class="opacity-75-hover text-primary me-1">the blogger</a>
                    introduces a person they’ve interviewed
                    <br />and provides some background information about
                    <a href="#" class="opacity-75-hover text-primary me-1">the interviewee</a>
                    and their
                    <br />work following this is a transcript of the interview.
                </div>
            </div>
        </div>

        <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
            <div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px p-10">
                <div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">
                    <div class="d-flex flex-center flex-column flex-column-fluid pb-15 pb-lg-20">
                        <form method="POST" action="{{ route('login') }}" class="form w-100" id="kt_sign_in_form">
                            @csrf
                            <div class="text-center mb-11">
                                <h1 class="text-dark fw-bolder mb-3">Sign In</h1>
                                <div class="text-gray-500 fw-semibold fs-6">Your Social Campaigns</div>
                            </div>

                            <div class="row g-3 mb-9">
                                <div class="col-md-6">
                                    <a href="#" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
                                        <img alt="Logo" src="admin/assets/media/svg/brand-logos/google-icon.svg" class="h-15px me-3" />
                                        Sign in with Google
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="#" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
                                        <img alt="Logo" src="admin/assets/media/svg/brand-logos/apple-black.svg" class="theme-light-show h-15px me-3" />
                                        <img alt="Logo" src="admin/assets/media/svg/brand-logos/apple-black-dark.svg" class="theme-dark-show h-15px me-3" />
                                        Sign in with Apple
                                    </a>
                                </div>
                            </div>

                            <div class="separator separator-content my-14">
                                <span class="w-125px text-gray-500 fw-semibold fs-7">Or with email</span>
                            </div>

                            <div class="fv-row mb-8">
                                <input type="email" name="email" placeholder="Email" class="form-control bg-transparent" required autofocus>
                            </div>

                            <div class="fv-row mb-3">
                                <input type="password" name="password" placeholder="Password" class="form-control bg-transparent" required autocomplete="current-password">
                            </div>

                            <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                                <div></div>
                                @if (Route::has('password.request'))
                                    <a class="link-primary" href="{{ route('password.request') }}">
                                        Forgot your password?
                                    </a>
                                @endif
                            </div>

                            <div class="d-grid mb-10">
                                <button type="submit" class="btn btn-primary">
                                    <span class="indicator-label">Sign In</span>
                                    <span class="indicator-progress">Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>
                            </div>

                            <div class="text-gray-500 text-center fw-semibold fs-6">
                                Not a Member yet?
                                <a href="{{ route('register') }}" class="link-primary">Sign up</a>
                            </div>
                        </form>
                    </div>

                    <div class="d-flex flex-stack">
                        <div class="me-10">
                            <button class="btn btn-flex btn-link btn-color-gray-700 btn-active-color-primary rotate fs-base" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start">
                                <img class="w-20px h-20px rounded me-3" src="admin/assets/media/flags/united-states.svg" alt="" />
                                <span class="me-1">English</span>
                                <i class="ki-duotone ki-down fs-5 text-muted rotate-180 m-0"></i>
                            </button>
                        </div>
                        <div class="d-flex fw-semibold text-primary fs-base gap-5">
                            <a href="#" target="_blank">Terms</a>
                            <a href="#" target="_blank">Plans</a>
                            <a href="#" target="_blank">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('js')
    <script src="{{asset('admin/assets/js/custom/authentication/sign-in/general.js')}}"></script>
    @endsection
</x-guest-layout>

{{-- <x-guest-layout>
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <!-- الجانب الأيسر -->
        <div class="d-flex flex-lg-row-fluid">
            <div class="d-flex flex-column flex-center pb-0 pb-lg-10 p-10 w-100">
                <img class="theme-light-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20" src="{{ asset('admin/assets/media/auth/agency.png') }}" alt="Logo" />
                <img class="theme-dark-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20" src="{{ asset('admin/assets/media/auth/agency-dark.png') }}" alt="Logo" />
                <h1 class="text-gray-800 fs-2qx fw-bold text-center mb-7">سريع، فعال ومنتج</h1>
                <div class="text-gray-600 fs-base text-center fw-semibold">
                    في هذا النوع من المنشورات،
                    <a href="#" class="opacity-75-hover text-primary me-1">المدون</a> يقدم شخصاً تمت مقابلته
                    <br /> ويوفر بعض المعلومات الخلفية عن
                    <a href="#" class="opacity-75-hover text-primary me-1">المقابل</a> وعمله.
                    <br />التالي هو نص المقابلة.
                </div>
            </div>
        </div>

        <!-- جانب تسجيل الدخول -->
        <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
            <div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px p-10">
                <div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">

                    <form method="POST" action="{{ route('login') }}" class="form w-100" novalidate>
                        @csrf
                        <div class="text-center mb-11">
                            <h1 class="text-dark fw-bolder mb-3">تسجيل الدخول</h1>
                            <div class="text-gray-500 fw-semibold fs-6">أدخل بريدك الإلكتروني وكلمة المرور</div>
                        </div>

                        <!-- البريد الإلكتروني -->
                        <div class="fv-row mb-8">
                            <input type="email" placeholder="البريد الإلكتروني" name="email" autocomplete="off" class="form-control bg-transparent @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus />
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- كلمة المرور -->
                        <div class="fv-row mb-3">
                            <input type="password" placeholder="كلمة المرور" name="password" autocomplete="off" class="form-control bg-transparent @error('password') is-invalid @enderror" required />
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- تذكرني -->
                        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                            <label class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} />
                                <span class="form-check-label" for="remember">تذكرني</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="link-primary">هل نسيت كلمة المرور؟</a>
                            @endif
                        </div>

                        <!-- زر تسجيل الدخول -->
                        <div class="d-grid mb-10">
                            <button type="submit" class="btn btn-primary" id="kt_sign_in_submit">
                                تسجيل الدخول
                            </button>
                        </div>

                        <div class="text-gray-500 text-center fw-semibold fs-6">
                            لا تمتلك حساب؟
                            <a href="{{ route('register') }}" class="link-primary">إنشاء حساب</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-guest-layout> --}}
