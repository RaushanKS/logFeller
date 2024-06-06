<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\OtpVerify;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['Login', 'register', 'logout', 'forgotPassword', 'otpVerification', 'resetPassword', 'socialLogin']]);
    }

    public function Login(Request $request)
    {
        try {
            $inputs = $request->all();
            $validatedData = Validator::make($inputs, [
                'email' => 'email|string|required',
                'password' => 'string|required',
                'device_token' => 'string',
                // 'device_id' => 'string',
            ]);

            if ($validatedData->fails()) {
                $errors = $validatedData->errors();

                $transformed = [];
                foreach ($errors->all() as $message) {
                    $transformed[] = $message;
                }
                return response()->json(['status' => 'failed', 'message' => $transformed], 422);
            }

            $user = User::join('user_role', 'user_role.user_id', '=', 'users.id')->where('users.email', $inputs['email'])->withTrashed()->first(['users.*', 'user_role.role_id', 'user_role.id as userRoleId']);

            if (!$user) {
                return response()->json(['status' => 'failed', 'message' => 'Your account does not exist!'], 404);
            }

            if ($user->email_verified_at === null) {
                return response()->json(['status' => 'failed', 'message' => 'Your email verification is pending!'], 403);
            }
            if ($user->status == 0) {
                return response()->json(['status' => 'failed', 'message' => 'Your Account has been banned Please contact support team'], 403);
            }
            if ($user->deleted_at !== null) {
                return response()->json(['status' => 'failed', 'message' => 'Your Account has been permanently delete Please contact support team'], 403);
            }

            $credentials = $request->only('email', 'password');
            //$remember_me = $request->has('remember_me') ? true : false;
            // print_r(auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')]));exit;
            if (!auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
                return response()->json(['status' => 'failed', 'message' => 'Invalid credentials.'], 401);
            }

            if (!$token = JWTAuth::fromUser($user, ['exp' => Carbon::now()->addDays(90)->timestamp])) {
                return response()->json(['status' => 'failed', 'message' => 'Invalid credentials.'], 401);
            }
            // auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')]);
            Auth::login($user);

            $userData = auth()->user();
            $userData->image = ($userData->image) ? asset($userData->image) : null;
            $deviceToken = DB::table('device_tokens')->where('device_id', $request->device_id)->where('user_id', $user->id)->get();
            if ($deviceToken->isEmpty()) {
                DB::table('device_tokens')->insert([
                    'user_id' => $user->id,
                    'device_token' => $request->device_token,
                    'device_id' => $request->device_id,
                    'email' => $user->email,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            $response = array(
                'user' => $userData,
                'token' => $token,
            );
            return response()->json(['status' => 'success', 'message' => 'Welcome to The Log Feller', 'data' => $response], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }
    
    public function register(Request $request)
    {
        try {
            $inputs = $request->all();
            $validatedData = Validator::make($inputs, [
                'email' => 'email|string|required',
                'name' => 'string|required',
                'password' => 'string|required|min:8',
                'device_token' => 'string',
                'device_id' => 'string',
            ]);

            if ($validatedData->fails()) {
                $errors = $validatedData->errors();
                $transformed = [];
                foreach ($errors->all() as $message) {
                    $transformed[] = $message;
                    return response()->json(['status' => 'failed', 'message' => $transformed], 422);
                }
            }
            $user = User::join('user_role', 'user_role.user_id', '=', 'users.id')->where('users.email', $request->email)->withTrashed()->first(['users.*', 'user_role.role_id', 'user_role.id as userRoleId']);
            if ($user) {
                return response()->json(['status' => 'failed', 'message' => 'Email already exists'], 403);
            }
            $tokenAdd = Str::random(64);
            $user = User::create(array_merge(
                $validatedData->validated(),
                [
                    'name_en' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'image' => 'profile/images.png',
                    'status' => 0,
                    'verification_token' => $tokenAdd,
                ]
            ));
            if ($user) {
                DB::table('user_role')->insert([
                    'user_id' => $user->id,
                    'role_id' => 2,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                DB::table('device_tokens')->insert([
                    'user_id' => $user->id,
                    'device_token' => $request->device_token,
                    'device_id' => $request->device_id,
                    'email' => $user->email,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $email = $user->email;
                $name = $user->name;
                $subject = 'Verification Email';
                define('TO_EMAIL_VERIFICATION', $email);
                define('TO_NAME_VERIFICATION', $name);
                define('TO_SUBJECT_VERIFICATION', $subject);
                $data = array('name' => $name, 'email' => $email, 'link' => env('APP_URL') . 'email-confirmation/' . $tokenAdd);
                Mail::send('email-verification', $data, function ($message) {
                    $message->to(TO_EMAIL_VERIFICATION, TO_NAME_VERIFICATION)->subject(TO_SUBJECT_VERIFICATION);
                    $message->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
                });

                // $adminEmail = "sharwancyberxinfosystem@gmail.com";
                // $userName = $user->name;
                // $adminSubject = 'New Registration';
                // define('TO_EMAIL_ADMIN', $adminEmail);
                // define('TO_NAME_ADMIN', 'CyberX');
                // define('TO_SUBJECT_ADMIN', 'New Registration');
                // $data = array('name' => $userName, 'email' => $user->email, 'link' => env('APP_URL') . 'approved/' . $user->email);
                // Mail::send('registration-notification', $data, function ($message) {
                //     $message->to(TO_EMAIL_ADMIN, TO_NAME_ADMIN)->subject(TO_SUBJECT_ADMIN);
                //     $message->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
                // });

                $user->verification_token = '';

                $response = array(
                    'user' => $user,
                );
                return response()->json(['status' => 'success', 'message' => 'Check your mail and confirm your account', 'data' => $response], 200);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Something went wrong. try again!'], 403);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {

            $validatedData = Validator::make($request->all(), [
                'username' => ['required', 'string', 'max:255'],
            ]);
            if ($validatedData->fails()) {
                $errors = $validatedData->errors();
                $transformed = [];
                foreach ($errors->all() as $message) {
                    return response()->json(['status' => 'failed', 'message' => $message], 422);
                }
            }
            $user = User::join('user_role', 'user_role.user_id', '=', 'users.id')->where('users.email', $request->username)->withTrashed()->first(['users.*', 'user_role.role_id', 'user_role.id as userRoleId']);
            if (!$user) {
                return response()->json(['status' => 'failed', 'message' => 'This account does not exist!'], 404);
            }

            if ($user->status == 0) {
                return response()->json(['status' => 'failed', 'message' => 'Your Account has been banned Please contact support team.'], 403);
            }
            if ($user->deleted_at !== null) {
                return response()->json(['status' => 'failed', 'message' => 'Your Account has been permanently delete Please contact support team.'], 403);
            }
            if ($user) {
                $otp = sprintf("%04d", mt_rand(1, 9999));
                $res = DB::table('otp_verify')->insert([
                    'user_id' => $user->id,
                    'otp' => $otp,
                    'created_at' => Carbon::now(),
                ]);
                if ($res) {
                    $email = $user->email;
                    $name = $user->name;
                    $subject = 'Reset Password';
                    $data = array('name' => $name, 'email' => $email, 'otpSend' => $otp);
                    define('TO_EMAIL', $email);
                    define('TO_NAME', $name);
                    define('TO_SUBJECT', $subject);
                    Mail::send('mail', $data, function ($message) {
                        $message->to(TO_EMAIL, TO_NAME)->subject(TO_SUBJECT);
                        $message->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
                    });
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Check your email for OTP',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Something went wrong please try again!',
                    ], 403);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid Information',
                ], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function otpVerification(Request $request)
    {
        try {

            $validatedData = Validator::make($request->all(), [
                'otp' => ['required', 'string', 'max:4'],
            ]);
            if ($validatedData->fails()) {
                $errors = $validatedData->errors();
                $transformed = [];
                foreach ($errors->all() as $message) {
                    return response()->json(['status' => 'failed', 'message' => $message], 422);
                }
            }
            $exist = DB::table('otp_verify')
            ->where('otp', '=', $request->otp)
                ->where('created_at', '>', Carbon::now()->subMinute(5))
                ->first();

            if (!empty($exist)) {
                $userInfo = User::where('id', $exist->user_id)->first();
                if ($userInfo) {
                    $token = Str::random(64);
                    $pass = DB::table('password_reset_tokens')->where('user_id', $userInfo->id)->first();
                    if ($pass) {
                        $res = DB::table('password_reset_tokens')->where('user_id', $userInfo->id)
                            ->update([
                                'token' => $token,
                                'created_at' => Carbon::now(),
                            ]);
                    } else {
                        $res = DB::table('password_reset_tokens')->insert([
                            'user_id' => $userInfo->id,
                            'email' => $userInfo->email,
                            'token' => $token,
                            'created_at' => Carbon::now(),
                        ]);
                    }

                    if (OtpVerify::where('id', $exist->id)->delete()) {
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Successful verification',
                            'data' => array('token' => $token),
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Something went wrong please try again!',
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'User could not be found',
                    ], 404);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid Otp',
                ], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {

            $validatedData = Validator::make($request->all(), [
                'token' => ['required', 'string', 'max:255'],
                'newPassword' => ['required', 'string', 'max:255'],
                'confirmPassword' => ['required', 'string', 'max:255'],
            ]);
            if ($validatedData->fails()) {
                $errors = $validatedData->errors();
                $transformed = [];
                foreach ($errors->all() as $message) {
                    return response()->json(['status' => 'failed', 'message' => $message], 422);
                }
            }
            if ($request->newPassword != $request->confirmPassword) {
                return response()->json(['status' => 'failed', 'message' => 'New password and confirm password not match try again'], 422);
            }
            $exist = DB::table('password_reset_tokens')
            ->where('token', '=', $request->token)
                ->where('created_at', '>', Carbon::now()->subMinute(30))
                ->first();
            if (!empty($exist)) {
                $user = User::where('id', $exist->user_id)->first();
                if ($user) {
                    $user->password = bcrypt($request->newPassword);
                    if ($user->save()) {
                        DB::table('password_reset_tokens')
                        ->where('token', '=', $request->token)->delete();
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Password Reset Successfully',
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Something went wrong',
                        ], 422);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'User not found',
                    ], 404);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Your request expired please try again',
                ], 403);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if ($token = $request->bearerToken()) {
            $checkToken = $this->invoke();
            if ($checkToken == 1) {
                JWTAuth::setToken($token)->invalidate();
                auth()->logout();
                return response()->json(['status' => 'success', 'message' => 'Successfully signed out'], 200);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Authorization Failed'], 401);
            }
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Invalided token'], 422);
        }
    }

    protected function invoke()
    {
        $response = auth('api')->check();
        $responseCode = 200;
        if (!$response) {
            try {
                if (!app(\Tymon\JWTAuth\JWTAuth::class)->parseToken()->authenticate()) {
                    $response = 0;
                }
            } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                $response = -1;
            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                $response = -2;
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                $response = -3;
            }
        } else {
            $response = (int) $response;
        }
        return $response;
    }
}
