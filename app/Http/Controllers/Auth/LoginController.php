<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'role' => 'required',
        ]);
        $user = User::where('email', $input['email'])->first();
        if (empty($user)) {
            return redirect()->route('login')
            ->with('error', 'This account not exist!');
        }
        $role = 0;
        if ($user) {
            if ($user->email_verified_at == '' || $user->email_verified_at == null) {
                return redirect()->route('login')
                ->with('error', 'Email not verified, Please check your email to validate it');
            }
            $roleGet = $user->roles;
            $role = $roleGet[0]['id'];
            // print_r($role);exit;
            if ($role != $input['role']) {
                return redirect()->route('login')
                ->with('error', 'This account not exist!');
            }
            if (!empty($roleGet) && isset($roleGet[0]) && $roleGet[0]['name'] == 'User') {
                return redirect()->route('login')
                ->with('error', 'This account not exist!');
            }
        }
        $remember = $request->has('remember') ? true : false;

        if (auth()->attempt(array('email' => $input['email'], 'password' => $input['password']), $remember)) {

            if (auth()->user()) {
                return redirect()->route('dashboard');
            } else {
                return redirect()->route('login');
            }
        } else {
            return redirect()->route('login')
            ->with('error', 'Email-Address And Password Are Wrong.');
        }
    }
}
