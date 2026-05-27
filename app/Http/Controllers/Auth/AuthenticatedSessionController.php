<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */



    // public function store(LoginRequest $request): RedirectResponse
    // {
    //     $request->authenticate();

    //     $request->session()->regenerate();

    //     $notification = array(
    //         'message' => 'تم تسجيل الدخول بنجاح',
    //         'alert-type' => 'success'
    //     );

    //     return redirect()->intended(route('dashboard', absolute: false))->with($notification);
    // }


    public function store(LoginRequest $request): RedirectResponse
    {
        // Get login credentials
        $credentials = $request->only('email', 'password');

        // Attempt to authenticate manually
        if (!Auth::attempt($credentials)) {
            // Login failed
            $notification = [
                'message' => 'فشل في تسجيل الدخول، تأكد من صحة البيانات',
                'alert-type' => 'error'
            ];

            return redirect()->back()->withInput()->with($notification);
        }

        // Login successful
        $request->session()->regenerate();

        $notification = [
            'message' => 'تم تسجيل الدخول بنجاح',
            'alert-type' => 'success'
        ];

        return redirect()->intended(route('dashboard', absolute: false))->with($notification);
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();



        return redirect('/');
    }
}
