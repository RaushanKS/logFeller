<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    private $user_id;
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['profileUpdate', 'infoUpdate', 'usersFetch', 'userFetch', 'addAddress', 'fetchSingleAddress', 'updateAddress', 'fetchAllAddress', 'deleteAddress', 'changePassword', 'deleteUserAccount']]);
        $this->middleware(function ($request, $next) {
            $checkToken = $this->invoke();
            if ($checkToken) {
                $this->user_id = $checkToken;
                return $next($request);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Authorization Failed'], 401);
            }
        });
    }

    public function profileUpdate(Request $request)
    {
        try {
            $image = $request->profile;
            $image_parts = explode(";base64,", $image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(10) . '.' . $image_type;
            $path = 'uploads/user/profile/';
            $destinationPath = public_path($path);
            file_put_contents($destinationPath . $imageName, base64_decode($image));
            $user = User::where('id', $this->user_id)->first();
            $user->image = $path . $imageName;
            if ($user->save()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile image updated successfully.',
                    'data' => array('filePath' => URL::to('/') . '/' . $path . $imageName),
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Something went wrong! Try again.',
                ], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function infoUpdate(Request $request)
    {
        try {
            $inputs = $request->all();
            $validatedData = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                //'dob' => ['required', 'date'],
                // 'phone' => ['required', 'string', 'min:10', 'max:11'],
            ]);

            if ($validatedData->fails()) {
                $errors = $validatedData->errors();

                $transformed = [];
                foreach ($errors->all() as $message) {
                    $transformed[] = $message;
                    return response()->json(['status' => 'failed', 'message' => $transformed], 422);
                }
            }
            $user = User::where('id', $this->user_id)->first();
            $user->name = $request->name;
            $userCheck = User::where('id', $this->user_id)->where('phone', $request->phone)->first();
                $user = User::where('id', $this->user_id)->first();
                $user->name = $request->name;
                $userCheck = User::where('id', $this->user_id)->where('phone', $request->phone)->first();
                if (empty($userCheck)) {
                    $userCheckExist = User::where('phone', $request->phone)->where('id', '!=', $this->user_id)->get();
                    if (count($userCheckExist) > 0) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Phone number already exists',
                        ], 403);
                    } else {
                        $user->phone = $request->phone;
                    }
                }
            if ($user->save()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile information updated successfully.',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Something went wrong! Try again.',
                ], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function fetchUser()
    {
        try {
            $users = User::join('user_role', 'user_role.user_id', '=', 'users.id')->where('users.id', $this->user_id)->where('users.status', '=', 1)->first(['users.*', 'user_role.role_id', 'user_role.id as userRoleId']);
            if ($users) {
                $users->image = ($users->image) ? URL::to('/') . '/' . $users->image : '';
                return response()->json(['status' => 'success', 'message' => '', 'data' => array('user' => $users)], 200);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Not exist'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $inputs = $request->all();
            $validatedData = Validator::make($inputs, [
                'oldPassword' => 'bail|required|min:8',
                'newPassword' => 'bail|required|min:8',
                'confirmNewPassword' => 'bail|required|min:8|same:newPassword'
            ]);

            if ($validatedData->fails()) {
                $errors = $validatedData->errors();
                $transformed = [];

                foreach ($errors->all() as $message) {
                    $transformed[] = $message;
                }

                return response()->json(['status' => 'failed', 'message' => $transformed], 422);
            }

            $user_id = $this->user_id;
            $user = User::where('id', $user_id)->first();
            $oldPassword = $inputs['oldPassword'];

            if (!Hash::check($oldPassword, $user->password)) {
                return response()->json(['status' => 'failed', 'message' => 'Please enter correct old password.'], 422);
            }

            $newPassword = $inputs['newPassword'];
            $user->password = bcrypt($newPassword);
            $user->save();

            return response()->json(['status' => 'success', 'message' => 'Password changed successfully.']);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function addAddress(Request $request)
    {
        try {

            $validatedData = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'mobile' => ['required', 'string'],
                'phone_code' => ['required', 'string'],
                'phone_country' => ['required', 'string'],
                'street' => ['required', 'string'],
                'state' => ['required', 'string'],
                'city' => ['required', 'string'],
                'code' => ['required', 'string'],
                'address_type' => ['required', 'string'],
            ]);

            if ($validatedData->fails()) {
                $errors = $validatedData->errors();

                $transformed = [];
                foreach ($errors->all() as $message) {
                    $transformed[] = $message;
                    return response()->json(['status' => 'failed', 'message' => $transformed], 422);
                }
            }
            if ($request->default == 1) {
                $addressGet = UserAddress::where('default', 1)->where('user_id', $this->user_id)->first();
                if ($addressGet) {
                    $addressGet->default = 0;
                    $addressGet->save();
                }
            }
            $address = UserAddress::create([
                "user_id" => $this->user_id,
                "name" => $request->name,
                "mobile" => $request->mobile,
                "phone_code" => $request->phone_code,
                "phone_country" => $request->phone_country,
                "street" => $request->street,
                "landmark" => $request->landmark,
                "state" => $request->state,
                "city" => $request->city,
                "code" => $request->code,
                "address_type" => $request->address_type,
                "default" => ($request->default) ? $request->default : 0,
            ]);
            if ($address) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'New address added successfully.',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Something went wrong! Try again.',
                ], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateAddress(Request $request)
    {
        try {

            $validatedData = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'mobile' => ['required', 'string'],
                'phone_code' => ['required', 'string'],
                'phone_country' => ['required', 'string'],
                'street' => ['required', 'string'],
                'state' => ['required', 'string'],
                'city' => ['required', 'string'],
                'code' => ['required', 'string'],
                'address_type' => ['required', 'string'],
            ]);

            if ($validatedData->fails()) {
                $errors = $validatedData->errors();

                $transformed = [];
                foreach ($errors->all() as $message) {
                    $transformed[] = $message;
                    return response()->json(['status' => 'failed', 'message' => $transformed], 422);
                }
            }
            if ($request->default == 1) {
                $addressGet = UserAddress::where('default', 1)->where('id', '!=', $request->addressId)->where('user_id', $this->user_id)->first();
                if ($addressGet) {
                    $addressGet->default = 0;
                    $addressGet->save();
                }
            }

            $address = UserAddress::where('id', $request->addressId)->where('user_id', $this->user_id)->first();
            if ($address) {
                $address->user_id = $this->user_id;
                $address->name = $request->name;
                $address->mobile = $request->mobile;
                $address->phone_code = $request->phone_code;
                $address->phone_country = $request->phone_country;
                $address->street = $request->street;
                $address->landmark = $request->landmark;
                $address->state = $request->state;
                $address->city = $request->city;
                $address->code = $request->code;
                $address->address_type = $request->address_type;
                $address->default = ($request->default) ? $request->default : 0;
                if ($address->save()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Address updated successfully.',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Something went wrong! Try again.',
                    ], 422);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Something went wrong! Try again.',
                ], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function fetchSingleAddress(Request $request, $id)
    {
        try {
            $address = UserAddress::where('user_id', $this->user_id)->where('id', $id)->first();
            if ($address) {
                $fetchAddress = array(
                    "name" => $address->name,
                    "mobile" => $address->mobile,
                    "phone_code" => $address->phone_code,
                    "phone_country" => $address->phone_country,
                    "street" => $address->street,
                    "landmark" => $address->landmark,
                    "state" => $address->state,
                    "city" => $address->city,
                    "code" => $address->code,
                    "address_type" => $address->address_type,
                    "default" => $address->default,
                );
                return response()->json(['status' => 'success', 'message' => '', 'data' => array('address' => $fetchAddress)], 200);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Not exist'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function fetchAllAddress(Request $request)
    {
        try {
            $addressData = UserAddress::where('user_id', $this->user_id)->get();
            $addressArr = array();
            if ($addressData) {
                foreach ($addressData as $address) {
                    $addressArr[] = array(
                        "id" => $address->id,
                        "name" => $address->name,
                        "mobile" => $address->mobile,
                        "phone_code" => $address->phone_code,
                        "phone_country" => $address->phone_country,
                        "street" => $address->street,
                        "landmark" => $address->landmark,
                        "state" => $address->state,
                        "city" => $address->city,
                        "code" => $address->code,
                        "address_type" => $address->address_type,
                        "default" => $address->default,
                    );
                }

                return response()->json(['status' => 'success', 'message' => '', 'data' => array('addressList' => $addressArr)], 200);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Not exist'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteAddress(Request $request)
    {
        try {
            $addressId = $request->addressId;
            // echo $addressId; exit;
            $address = UserAddress::where('user_id', $this->user_id)->where('id', $addressId)->delete();

            if ($address) {
                return response()->json(['status' => 'success', 'message' => 'Address deleted'], 200);
            } else {
                return response()->json(['status' => 'Failed', 'message' => 'Address not found'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function invoke()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return false;
            }
        } catch (JWTException $e) {
            return false;
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return false;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return false;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return false;
        }
        return $user->id;
    }
}
