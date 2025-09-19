<?php

namespace App\Http\Controllers\Staff;

use App\Models\Staff;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string',
            'email'        => 'required|email|unique:staff,email',
            'password'     => 'required|min:6',
            'phone'        => 'nullable|string',
            'address'      => 'nullable|string',
            'dob'          => 'nullable|date',
            'gender'       => 'nullable|in:Male,Female,Other',
            'certification'=> 'nullable|string',
            'lga'          => 'nullable|string',
            'passport'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $passportPath = null;
        if ($request->hasFile('passport')) {
            $passportPath = $request->file('passport')->store('passports', 'public');
        }

        $staff = Staff::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => bcrypt($request->password),
            'phone'        => $request->phone,
            'address'      => $request->address,
            'dob'          => $request->dob,
            'gender'       => $request->gender,
            'certification'=> $request->certification,
            'lga'          => $request->lga,
            'passport'     => $passportPath,
        ]);

        $token = $staff->createToken('staff_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration Successful',
            'token' => $token,
            'staff' => $staff,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $staff = Staff::where('email', $request->email)->first();

        if (!$staff || !Hash::check($request->password, $staff->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $staff->createToken('staff_token')->plainTextToken;

   return response()->json([
    'message' => 'Login successful',
    'token'   => $token,
    // 'staff'   => $staff,
    // 'roles'   => $staff->roles->pluck('name'),        // multiple roles
    // 'permissions' => $staff->permissions->pluck('name') // multiple permissions
]);

    }

    public function me(Request $request)
{
    $staff = $request->user(); // Current authenticated staff

    return response()->json([
        'id'          => $staff->id,
        // 'name'        => $staff->name,
        // 'email'       => $staff->email,
        // 'phone'       => $staff->phone,
        // 'address'     => $staff->address,
        // 'dob'         => $staff->dob,
        // 'gender'      => $staff->gender,
        // 'certification'=> $staff->certification,
        // 'lga'         => $staff->lga,
        // 'passport'    => $staff->passport,
        'roles'       => $staff->roles->pluck('name'),
        'permissions' => $staff->allPermissions()->pluck('name'),
    ]);
}


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

public function listAll()
{
    $staff = Staff::with(['roles:id,name', 'permissions:id,name'])->get();

    return response()->json([
        'data' => $staff->map(function ($s) {
            return [
                'id'          => $s->id,
                'name'        => $s->name,
                'email'       => $s->email,
                'roles'       => $s->roles->pluck('name'),
                'permissions' => $s->allPermissions()->pluck('name'),
            ];
        }),
    ]);
}

public function view($id)
{
    $staff = Staff::with(['roles:id,name', 'permissions:id,name'])->find($id);

    if (!$staff) {
        return response()->json(['message' => 'Staff not found'], 404);
    }

    return response()->json([
        'id'          => $staff->id,
        'name'        => $staff->name,
        'email'       => $staff->email,
        'roles'       => $staff->roles->pluck('name'),
        'permissions' => $staff->allPermissions()->pluck('name'),
    ]);
}


}
