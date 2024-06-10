<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function profile()
    {
        $id = Auth::user()->id;
        $user = User::find($id);
        return view('profile', compact(['user']));
    }

    public function infoUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Required Field missing',
            ]);
        }
        $user = User::find($id);
        if ($user) {
            $user->name = $request->input('name');
            $user->phone = $request->input('contactNo');
            if ($user->save()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile info updated Successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong! Try again.',
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => `The account doesn't exist`,
            ]);
        }
    }

    public function profileImageUpdate(Request $request, $id)
    {
        $user = User::find($id);
        if ($user) {
            $image = $request->file('file');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'uploads/user/profile/';
            $destinationPath = public_path($path);
            $image->move($destinationPath, $name);
            $user->image = $path . $name;
            $user->save();
            if ($user->save()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile image updated Successfully',
                    'file' => $path . $name,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong! Try again.',
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => `This account doesn't exist`,
            ]);
        }
    }
}
