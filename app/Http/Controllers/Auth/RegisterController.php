<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function index()
    {
        return view('auth.register');
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $rules = [
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'confirmPassword' => 'required|same:password',
            'terms_privacy' => 'required',
        ];

        $customMessages = [
            'required' => 'The :attribute field is required.',
            'email' => 'Enter a valid email',
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            Session::flash('error', __($validator->errors()->first()));
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        $role = Role::where('name', 'User')->first();

        $user = User::where('email', $input['email'])->first();
        // echo $user; exit;
        if (empty($user)) {
            
            $tokenAdd = Str::random(64);
            $user = User::create([
                'name' => $input['username'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'verification_token' => $tokenAdd,
                'image' => 'assets/img/team/1.png',
                'term_privacy' => $input['terms_privacy'],
            ]);
            $email = $user->email;
            $name = $user->name;
            $subject = 'Verification Email';
            define('TO_EMAIL_VERIFICATION', $email);
            define('TO_NAME_VERIFICATION', $name);
            define('TO_SUBJECT_VERIFICATION', $subject);
            $data = array('name' => $name, 'email' => $email, 'link' => 'http://127.0.0.1:8000/email-confirmation/' . $tokenAdd);
            Mail::send('email-verification', $data, function ($message) {
                $message->to(TO_EMAIL_VERIFICATION, TO_NAME_VERIFICATION)->subject(TO_SUBJECT_VERIFICATION);
                $message->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
            });
            $user->roles()->attach($role);

            return redirect()->back()->with('success', 'Registration successful. Please check your email and click on the verification link to verify your email address.');
        } else {
            $roleGet = $user->roles;
            // echo $roleGet; exit;
            if (!empty($roleGet) && isset($roleGet[0]) && $roleGet[0]['name'] == 'User') {
                return redirect()->back()->with('error', 'This account already exist!');
            } else {
                $tokenAdd = Str::random(64);
                $user = User::create([
                    'name' => $input['username'],
                    'email' => $input['email'],
                    'password' => Hash::make($input['password']),
                    'verification_token' => $tokenAdd,
                    'image' => 'assets/img/team/1.png',
                    'term_privacy' => $input['terms_privacy'],
                ]);
                $email = $user->email;
                $name = $user->name;
                $subject = 'Verification Email';
                define('TO_EMAIL_VERIFICATION', $email);
                define('TO_NAME_VERIFICATION', $name);
                define('TO_SUBJECT_VERIFICATION', $subject);
                $data = array('name' => $name, 'email' => $email, 'link' => 'http://127.0.0.1:8000/email-confirmation/' . $tokenAdd);
                Mail::send('email-verification', $data, function ($message) {
                    $message->to(TO_EMAIL_VERIFICATION, TO_NAME_VERIFICATION)->subject(TO_SUBJECT_VERIFICATION);
                    $message->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
                });
                $user->roles()->attach($role);

                return redirect()->back()->with('success', 'Registration successful. Please check your email and click on the verification link to verify your email address.');
            }
        }
    }
}
