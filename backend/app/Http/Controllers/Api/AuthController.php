<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Member;
use App\Models\RefreshToken;

class AuthController extends Controller
{

    /**
     * Deskripsi singkat:
     * Melakukan proses otentikasi (login) untuk member menggunakan JWT.
     * Mengembalikan dua token: Access Token (JWT, pendek, 15 menit) dan
     * Refresh Token (opaque, panjang, 14 hari) yang disimpan ke database.
     *
     * Parameter:
     * @param  \Illuminate\Http\Request  $request  Objek request klien. Membutuhkan 'member_number' dan 'password'.
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi access_token, refresh_token, dan data user.
     *
     * Contoh penggunaan:
     * POST /api/auth/login
     * Body JSON: { "member_number": "MEMBER-001", "password": "password123" }
     */
    public function login(Request $request)
    {
        $request->validate([
            'member_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('member_number', 'password');

        if (! $accessToken = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized or invalid credentials'], 401);
        }

        $user = auth('api')->user();

        // Buat Refresh Token opaque baru dan simpan ke database
        $refreshTtlMinutes = (int) config('jwt.refresh_ttl', 20160);
        ['token' => $refreshToken] = RefreshToken::createForMember($user->id, $refreshTtlMinutes);

        return $this->respondWithToken($accessToken, $refreshToken);
    }

    /**
     * Deskripsi singkat:
     * Mendaftarkan member baru ke dalam sistem dan menandainya sebagai member aktif
     * berdasarkan durasi promosi yang dipilih.
     *
     * Parameter:
     * @param  \Illuminate\Http\Request  $request  Objek request klien berisi data registrasi member.
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi token JWT member baru.
     *
     * Contoh penggunaan:
     * POST /api/auth/register
     * Body JSON: { "name": "Budi", "email": "budi@email.com", ... }
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

        $accessToken = auth('api')->login($member);

        $refreshTtlMinutes = (int) config('jwt.refresh_ttl', 20160);
        ['token' => $refreshToken] = RefreshToken::createForMember($member->id, $refreshTtlMinutes);

        return $this->respondWithToken($accessToken, $refreshToken);
    }

    /**
     * Deskripsi singkat:
     * Memperbarui profil data dari member yang saat ini sedang login.
     *
     * Parameter:
     * @param  \Illuminate\Http\Request  $request  Objek request klien berisi field-field data yang ingin diubah.
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi data profil user yang sudah diperbarui.
     *
     * Contoh penggunaan:
     * POST /api/auth/update-profile
     * Headers: Authorization: Bearer <token>
     * Body JSON: { "phone": "08123456789" }
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
     * Deskripsi singkat:
     * Mendapatkan data profil dari member yang saat ini sedang login beserta status membershipnya.
     *
     * Parameter:
     * (Tidak ada parameter spesifik, menggunakan token JWT dari header Auth)
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi data user yang tervalidasi.
     *
     * Contoh penggunaan:
     * POST /api/auth/me
     * Headers: Authorization: Bearer <token>
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Deskripsi singkat:
     * Melakukan proses logout untuk member yang sedang aktif.
     * Me-revoke Refresh Token yang dikirim dalam body request dan me-invalidate JWT saat ini.
     *
     * Parameter:
     * @param  \Illuminate\Http\Request  $request  Membutuhkan field 'refresh_token' di body.
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON yang mengkonfirmasi proses logout berhasil.
     *
     * Contoh penggunaan:
     * POST /api/auth/logout
     * Headers: Authorization: Bearer <token>
     * Body JSON: { "refresh_token": "<refresh_token_plaintext>" }
     */
    public function logout(Request $request)
    {
        // Revoke Refresh Token dari database agar tidak bisa dipakai lagi
        if ($request->filled('refresh_token')) {
            $hashed = hash('sha256', $request->input('refresh_token'));
            RefreshToken::where('token', $hashed)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }

        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Deskripsi singkat:
     * Memperbarui Access Token menggunakan Refresh Token yang valid.
     * Endpoint ini TIDAK membutuhkan Access Token yang masih valid di header Authorization.
     * Menggunakan strategi "Refresh Token Rotation": setiap pemanggilan menghasilkan
     * Refresh Token baru dan me-revoke yang lama.
     *
     * Parameter:
     * @param  \Illuminate\Http\Request  $request  Membutuhkan field 'refresh_token' di body.
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi access_token dan refresh_token baru.
     *
     * Contoh penggunaan:
     * POST /api/auth/refresh
     * Body JSON: { "refresh_token": "<refresh_token_plaintext>" }
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        // Cari dan validasi Refresh Token dari database
        $refreshTokenModel = RefreshToken::findValid($request->input('refresh_token'));

        if (! $refreshTokenModel) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        $member = $refreshTokenModel->member;

        // Revoke Refresh Token lama (Refresh Token Rotation untuk keamanan)
        $refreshTokenModel->update(['revoked_at' => now()]);

        // Issue Access Token JWT baru untuk member ini
        $newAccessToken = auth('api')->login($member);

        // Buat Refresh Token baru
        $refreshTtlMinutes = (int) config('jwt.refresh_ttl', 20160);
        ['token' => $newRefreshToken] = RefreshToken::createForMember($member->id, $refreshTtlMinutes);

        return $this->respondWithToken($newAccessToken, $newRefreshToken);
    }

    /**
     * Deskripsi singkat:
     * Menghasilkan *QR Code* absensi dinamis (yang berisi custom JWT Payload) untuk member yang sedang login.
     * Validasi status member harus aktif terlebih dahulu. QR Code ini hanya valid selama 1 menit.
     *
     * Parameter:
     * (Tidak ada parameter spesifik, menggunakan token otentikasi JWT dari header Auth)
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi token QR untuk di-*render* pada aplikasi mobile.
     *
     * Contoh penggunaan:
     * GET /api/auth/generate-qr
     * Headers: Authorization: Bearer <token>
     */
    public function generateQR()
    {
        $user = auth('api')->user();

        if ($user->status !== 'active') {
            return response()->json(['error' => 'Membership is not active'], 403);
        }

        // Create a custom 1-minute expiration payload for QR
        // Adding 'jti' ensures uniqueness per request even within the same second
        $payload = auth('api')->factory()->customClaims([
            'sub' => $user->id,
            'member_number' => $user->member_number,
            'purpose' => 'attendance',
            'jti' => (string) \Illuminate\Support\Str::uuid()
        ])->setTTL(1)->make();

        $token = auth('api')->manager()->encode($payload)->get();

        return response()->json([
            'qr_code_data' => $token,
            'message' => 'Scan this dynamic QR code at the receptionist. Valid for 1 minute.',
            'expires_in' => 60
        ]);
    }

    /**
     * Deskripsi singkat:
     * Membangun struktur response JSON yang berisi Access Token, Refresh Token, dan data user.
     *
     * Parameter:
     * @param  string $accessToken   JWT Access Token yang baru dibuat.
     * @param  string $refreshToken  Refresh Token plaintext yang baru dibuat.
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken(string $accessToken, string $refreshToken)
    {
        return response()->json([
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'bearer',
            'expires_in'    => config('jwt.ttl') * 60, // dalam detik
            'user'          => auth('api')->user()
        ]);
    }
}
