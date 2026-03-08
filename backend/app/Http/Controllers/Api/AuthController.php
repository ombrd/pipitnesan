<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Member;

class AuthController extends Controller
{

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'member_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('member_number', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized or invalid credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Register a new member.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'email' => 'required|string|email|max:255|unique:members',
            'password' => 'required|string|min:6',
            'id_card_number' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'fcm_token' => 'nullable|string',
            'promotion_id' => 'required|exists:promotions,id',
            'address' => 'required|string',
            'birth_place' => 'required|string',
            'birth_date' => 'required|date'
        ]);

        $promotion = \App\Models\Promotion::findOrFail($request->promotion_id);
        
        // Pick random AO for this branch
        $randomAo = \App\Models\AccountOfficer::where('branch_id', $request->branch_id)->inRandomOrder()->first();

        $member = Member::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_card_number' => $request->id_card_number,
            'branch_id' => $request->branch_id,
            'address' => $request->address,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'account_officer_code' => $randomAo ? $randomAo->code : null,
            'fcm_token' => $request->fcm_token,
            'status' => 'active', 
            'active_until' => now()->addDays($promotion->duration_days)
        ]);

        \App\Models\Payment::create([
            'member_id' => $member->id,
            'amount' => $promotion->price,
            'payment_date' => now(),
        ]);

        event(new \Illuminate\Auth\Events\Registered($member));

        $token = auth('api')->login($member);
        return $this->respondWithToken($token);
    }

    /**
     * Update the authenticated member's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string',
            'email' => 'sometimes|string|email|max:255|unique:members,email,'.$user->id,
            'password' => 'sometimes|string|min:6',
            'fcm_token' => 'nullable|string',
            'birth_place' => 'sometimes|string|nullable',
            'birth_date' => 'sometimes|date|nullable',
            'address' => 'sometimes|string|nullable'
        ]);

        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('phone')) $user->phone = $request->phone;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('fcm_token')) $user->fcm_token = $request->fcm_token;
        if ($request->has('birth_place')) $user->birth_place = $request->birth_place;
        if ($request->has('birth_date')) $user->birth_date = $request->birth_date;
        if ($request->has('address')) $user->address = $request->address;
        if ($request->has('password') && filled($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Generate QR String for Attendance
     */
    public function generateQR()
    {
        $user = auth('api')->user();
        
        if ($user->status !== 'active') {
            return response()->json(['error' => 'Membership is not active'], 403);
        }

        // Create a custom 1-minute expiration payload for QR
        $payload = auth('api')->factory()->customClaims([
            'sub' => $user->id,
            'member_number' => $user->member_number,
            'purpose' => 'attendance'
        ])->setTTL(1)->make();

        $token = auth('api')->manager()->encode($payload)->get();

        return response()->json([
            'qr_code_data' => $token,
            'message' => 'Scan this dynamic QR code at the receptionist. Valid for 1 minute.',
            'expires_in' => 60
        ]);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => auth('api')->user()
        ]);
    }
}
