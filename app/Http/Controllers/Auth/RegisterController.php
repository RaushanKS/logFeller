<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $data = array('name' => $name, 'email' => $email, 'link' =>  env('APP_URL') . 'email-confirmation/' . $tokenAdd);
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
                $data = array('name' => $name, 'email' => $email, 'link' =>  env('APP_URL') . 'email-confirmation/' . $tokenAdd);
                Mail::send('email-verification', $data, function ($message) {
                    $message->to(TO_EMAIL_VERIFICATION, TO_NAME_VERIFICATION)->subject(TO_SUBJECT_VERIFICATION);
                    $message->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
                });
                $user->roles()->attach($role);

                return redirect()->back()->with('success', 'Registration successful. Please check your email and click on the verification link to verify your email address.');
            }
        }
    }

    public function passwordForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request)
    {
        $input = $request->all();
        $rules = [
            'email' => 'required|email',
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
        $role = Role::where('name', 'Vendor')->first();


        $user = User::where('email', $input['email'])->first();
        if (empty($user)) {

            return redirect()->back()->with('error', 'This email account not exist!');
        } else {
            $roleGet = $user->roles;
            // echo $roleGet[0]['name'];
            // exit;
            if (!empty($roleGet) && isset($roleGet[0]) && $roleGet[0]['name'] == 'User') {
                return redirect()->route('login')
                ->with('error', 'This email account not exist!');
            }
            DB::table('password_reset_tokens')->insert([
                'user_id' => $user->id,
                'email' => $request->email,
                'token' => Str::random(64),
                'created_at' => Carbon::now()->addMinutes(25),
            ]);
            $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)->first();

            if ($this->sendResetEmail($request->email, $tokenData->token)) {
                return redirect()->back()->with('success', 'A password reset link has been sent to your email address.');
            } else {
                return redirect()->back()->withErrors(['error' => 'A Network Error occurred. Please try again.']);
            }
        }
    }

    private function sendResetEmail($email, $token)
    {
        $user = DB::table('users')->where('email', $email)->select('name', 'email')->first();
        $link = env('APP_URL') . 'password/reset/' . $token . '?email=' . urlencode($user->email);

        try {
            $email = $user->email;
            $name = $user->name;
            $subject = 'Password Reset Link';
            define(
                'TO_EMAIL_VERIFICATION',
                $email
            );
            define('TO_NAME_VERIFICATION', $name);
            define('TO_SUBJECT_VERIFICATION', $subject);
            $data = array('name' => $name, 'email' => $email, 'link' => $link);
            // echo $data;
            // exit;
            Mail::send('password-forgot', $data, function ($message) {
                $message->to(
                    TO_EMAIL_VERIFICATION,
                    TO_NAME_VERIFICATION
                )->subject(TO_SUBJECT_VERIFICATION);
                $message->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
            });

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function passwordReset($token)
    {
        $tokenData = DB::table('password_reset_tokens')
            ->where('token', $token)->first();
        if (!empty($tokenData)) {
            $created = $tokenData->created_at;
            $currentDate = Carbon::now();
            $startTime = (new Carbon)->parse($created);
            $endTime = (new Carbon)->parse($currentDate);

            if ($endTime > $startTime) {
                DB::table('password_reset_tokens')->where('user_id', $tokenData->user_id)->delete();
                return view('auth.reset-password')->withErrors(['error' => 'This link expired!']);
            } else {
                $id = $tokenData->user_id;
                return view('auth.reset-password', compact(['id']));
            }
        } else {
            return redirect('/forgot-password')->withErrors(['error' => 'A Network Error occurred. Please try again.']);
        }
    }

    public function passwordUpdate(Request $request)
    {
        $input = $request->all();
        $rules = [
            'user_id' => 'required',
            'password' => 'required|min:8',
            'confirmPassword' => 'required|same:password',
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.',
        ];
        $validator = Validator::make(
            $request->all(),
            $rules,
            $customMessages
        );
        if ($validator->fails()) {
            Session::flash('error', __($validator->errors()->first()));
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        $user = User::where('id', $input['user_id'])->first();
        if (empty($user)) {
            return redirect('/login')->with('error', 'This email account not exist!');
        } else {
            $userPassword = DB::table('users')
            ->where('id', $input['user_id'])
            ->update(['password' => Hash::make($input['password'])]);

            if ($userPassword) {
                DB::table('password_reset_tokens')->where('user_id', $input['user_id'])->delete();
                return redirect('/login')->with('success', 'Successfully change password');
            } else {
                return redirect()->back()->withErrors(['error' => 'Something went wrong please try again!']);
            }
        }
    }

}
