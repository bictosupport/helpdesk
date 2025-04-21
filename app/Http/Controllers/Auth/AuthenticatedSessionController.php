<?php

namespace App\Http\Controllers\Auth;

use App\Events\ForgotPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\Department;
// use App\Mail\OtpMail;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Laravel\Socialite\Facades\Socialite;
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function create() {
        $is_demo = (int)config('app.demo');
        $env = DotenvEditor::load();
        $siteKey = $env->keyExists('RE_CAPTCHA_KEY')?$env->getValue('RE_CAPTCHA_KEY'):'';
        return Inertia::render('Auth/Login', ['is_demo' => $is_demo, 'site_key' => $siteKey]);
    }

    public function register()
    {
        $is_demo = (int)config('app.demo');
        $env = DotenvEditor::load();
        $siteKey = $env->keyExists('RE_CAPTCHA_KEY') ? $env->getValue('RE_CAPTCHA_KEY') : '';
    
        // Fetch departments and order them by name
        $departments = Department::orderBy('name')->get();
    
        return Inertia::render('Auth/Register', [
            'is_demo' => $is_demo, 
            'site_key' => $siteKey,
            'departments' => $departments
        ]);
    }
    public function forgotPassword() {
        $is_demo = (int)config('app.demo');
        return Inertia::render('Auth/ForgotPassword', ['is_demo' => $is_demo]);
    }

    public function forgotPasswordMail(Request $request) {
        $requestData = $request->validate(['email' => 'required|email|exists:users']);

        $token = Str::random(64);
        DB::table('password_resets')->insert([
            'email' => $requestData['email'],
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        event(new ForgotPassword(['email' => $requestData['email'], 'token' => $token]));

        return back()->with('success', 'We have e-mailed your password reset link!');
    }

    public function forgotPasswordToken($token){
        return Inertia::render('Auth/ForgotPasswordInput', ['token' => $token]);
    }

    public function forgotPasswordStore(Request $request){
        $requestData = $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
            'token' => 'required'
        ]);

        $updatePassword = DB::table('password_resets')
            ->where([
                'email' => $requestData['email'],
                'token' => $requestData['token']
            ])
            ->first();

        if(!$updatePassword){
            return Redirect::back()->with('error', 'Invalid email or token!');
        }

        User::where('email', $requestData['email'])->update(['password' => Hash::make($requestData['password'])]);

        DB::table('password_resets')->where(['email'=> $requestData['email']])->delete();

        return Redirect::route('login')->with('success', 'Your password has been changed!');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();
    
        $user = Auth::user();
    
        // 1. Generate OTP + Timer
        $otpCode = rand(100000, 999999);
        $otpTimer = now()->addMinutes(2);
    
        $user->otp = $otpCode;
        $user->otp_timer = $otpTimer;
        $user->save();
    
        // 2. Send OTP Email (assuming you have an OTP email template)
        \Mail::to($user->email)->queue(new \App\Mail\OtpMail($otpCode));
    
        // 3. Redirect to OTP page
        return redirect()->route('otp.verify');
    }
    


    
    

    

      public function registerStore(Request $request) {
        // Validate the form data including the department_id
        $requestData = $request->validate([
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:10'],
            'phone' => ['nullable', 'max:20'],
            'country_id' => ['nullable', 'max:20'],
            'city' => ['nullable', 'max:30'],
            'address' => ['nullable'],
            'department_id' => ['required', 'exists:departments,id'], // Ensure department exists
        ]);
    
        // Check if the email already exists
        if (User::where('email', $requestData['email'])->exists()) {
            return back()->withErrors(['email' => 'The email address is already registered.'])->withInput();
        }
    
        $role = Role::where('slug', 'customer')->first();
        $requestData['role_id'] = !empty($role) ? $role->id : 2;
    
        // Create the new user with department_id
        $user = User::create([
            'first_name' => $requestData['first_name'],
            'last_name' => $requestData['last_name'],
            'email' => $requestData['email'],
            'password' => Hash::make($requestData['password']),
            'phone' => $requestData['phone'],
            'country_id' => $requestData['country_id'],
            'city' => $requestData['city'],
            'address' => $requestData['address'],
            'role_id' => $requestData['role_id'],
            'department_id' => $requestData['department_id'], // Save department_id
        ]);
    
        // Log the user in and regenerate the session
        Auth::loginUsingId($user->id, true);
        $request->session()->regenerate();
    
        return redirect()->intended(RouteServiceProvider::DASHBOARD);
    }
    
    public function redirect(){
        return Socialite::driver('google')->redirect();
    }
    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();
    
        $user = User::where('email', $googleUser->email)->first();
    
        if ($user) {
            Auth::login($user);
    
            // ✅ Mark OTP as verified for Google login
            session()->put('otp_verified', true);
    
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('login')->withErrors([
                'email' => 'Login with this Google account is not registered in our system.'
            ]);
        }
    }


    public function showDepartmentSelection()
    {
        $departments = Department::orderBy('name')->get(); // Get all departments

        return Inertia::render('Auth/DepartmentSelection', [
            'departments' => $departments
        ]);
    }

    public function showEnterPhone()
    {
        return Inertia::render('Auth/EnterPhone', [
            'user' => Auth::user(),
        ]);
    }

    public function showDepartmentPhoneSelection()
    {
        $departments = Department::orderBy('name')->get();

        return Inertia::render('Auth/DepartmentPhoneSelection', [
            'departments' => $departments,
            'user' => Auth::user(),
        ]);
    }



    public function saveDepartmentSelection(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
        ]);

        $user = Auth::user();
        $user->department_id = $request->department_id;
        $user->save(); // Save the department

        return redirect()->route('dashboard'); // Redirect to dashboard after saving
    }

    public function savePhone(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'digits:11', 'regex:/^[0-9]+$/'],
        ]);

        $user = Auth::user();
        $user->update([
            'phone' => $request->phone,
        ]);

        return redirect()->route('dashboard')->with('success', 'Phone number updated successfully.');
    }




    public function saveDepartmentPhone(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'phone' => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $user->update([
            'department_id' => $request->department_id,
            'phone' => $request->phone,
        ]);

        return redirect()->route('dashboard')->with('success', 'Information updated successfully.');
    }


    /**
     * Destroy an authenticated session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        
        // Invalidate the session
        $request->session()->invalidate();
        
        // Regenerate the CSRF token to prevent session fixation
        $request->session()->regenerateToken();
        
        \Log::info('Session invalidated successfully.');

        return redirect('/login');
    }

}
