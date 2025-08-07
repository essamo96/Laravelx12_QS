<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App;

class LoginController extends AdminController {

    public function __construct() {
        parent::__construct();
    }

    ///////////////////////////////
    public function getIndex() {
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
         return redirect()->intended('/admin');
    }

    // فشل تسجيل الدخول - رسالة عامة تظهر في الأعلى
    return redirect()->route('login')
                     ->withInput()
                     ->with('danger', 'اسم المستخدم أو كلمة المرور غير صحيحة');
}



    // public function postIndex(Request $request)
    // {
    //     $field = filter_var($request->get('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    //     $username = $request->get('username');
    //     $password = $request->get('password');
    //     $admin[$field] = $username;
    //     $admin['password'] = $password;
    //     $admin['status'] = 1;
    //     if (Auth::guard('admin')->attempt($admin)) {
    //         // Generate a unique session name or identifier
    //         $sessionName = 'admin_session_' . time(); // Example: using a timestamp as a session name
    //         // dd($sessionName);
    //         // Set the unique session name
    //         session([$sessionName => true]);
    //         return redirect()->intended('/admin');
    //     } else {
    //         return redirect('admin/login')->with(['danger' => 'Error username or password']);
    //     }
    // }
    // public function postIndex2(Request $request)
    // {
    //     $field = filter_var($request->get('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    //     $username = $request->get('username');
    //     $password = $request->get('password');
    //     $admin[$field] = $username;
    //     $admin['password'] = $password;
    //     $admin['status'] = 1;
    //     if (Auth::guard('admin')->attempt($admin)) {
    //         // Generate a unique session name or identifier
    //         $sessionName = 'admin_session_' . Auth::guard('admin')->id();
    //         // Set the unique session name
    //         session([$sessionName => true]);
    //         // Store the session name in the user's model or database
    //         $user = Auth::guard('admin')->user();
    //         $user->session_name = $sessionName;
    //         $user->save();
    //         return redirect()->intended('/admin');
    //     } else {
    //         return redirect('admin/login')->with(['danger' => 'Error username or password']);
    //     }
    // }
    //////////////////////////////////////////
    // public function getLogout() {
    //     Auth::guard('admin')->logout();
    //     return redirect('/admin');
    // }
    public function getLogout() {
        $sessionName = session()->get('login_token');
        if ($sessionName) {
            session()->forget($sessionName); // Clear the specific session data
            Auth::guard('admin')->logout();
            return redirect('/admin');
        }
        // Perform the logout operation
        // ...
        return redirect('/admin');
    }
}
