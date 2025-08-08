<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App;

class LoginController extends AdminController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getIndex()
    {
        $loginToken = bin2hex(random_bytes(16));
        session(['login_token' => $loginToken]);
        return view('admin.auth.login', compact('loginToken'));
    }

    public function postIndex(Request $request)
    {
        // تحقق الحقول
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string|min:6',
        ], [
            'username.required' => 'يرجى إدخال البريد الإلكتروني أو اسم المستخدم.',
            'password.required' => 'يرجى إدخال كلمة المرور.',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('login')
                ->withErrors($validator)
                ->withInput();
        }

        // تحديد نوع الحقل: إيميل أو اسم مستخدم
        $field = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field => $request->input('username'),
            'password' => $request->input('password'),
            'status' => 1,
        ];

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard.view'));
        }

        // فشل تسجيل الدخول - رسالة عامة تظهر في الأعلى
        return redirect()->route('login')
            ->withInput()
            ->with('danger', 'اسم المستخدم أو كلمة المرور غير صحيحة');
    }


    public function getLogout()
    {
        Auth::guard('admin')->logout();
        return redirect('/admin/login')->with(['success' => 'تم تسجيل الخروج بنجاح']);
    }
}
