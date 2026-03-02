<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // ─────────────────────────────────────────
    //  REGISTER
    // ─────────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required'      => 'নাম দিন',
            'email.required'     => 'ইমেইল দিন',
            'email.email'        => 'সঠিক ইমেইল দিন',
            'email.unique'       => 'এই ইমেইলটি আগেই ব্যবহার হয়েছে',
            'password.required'  => 'পাসওয়ার্ড দিন',
            'password.min'       => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষর',
            'password.confirmed' => 'পাসওয়ার্ড মিলছে না',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        $token = $user->createToken('hisabzone_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'রেজিস্ট্রেশন সফল!',
            'data'    => [
                'user'  => $this->userResource($user),
                'token' => $token,
            ],
        ], 201);
    }

    // ─────────────────────────────────────────
    //  LOGIN (Email + Password)
    // ─────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'ইমেইল দিন',
            'email.email'       => 'সঠিক ইমেইল দিন',
            'password.required' => 'পাসওয়ার্ড দিন',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'ইমেইল বা পাসওয়ার্ড সঠিক নয়',
            ], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('hisabzone_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'লগিন সফল!',
            'data'    => [
                'user'  => $this->userResource($user),
                'token' => $token,
            ],
        ]);
    }

    // ─────────────────────────────────────────
    //  LOGOUT — শুধু current device
    // ─────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        // শুধু এই device এর token delete
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'লগআউট সফল',
        ]);
    }

    // ─────────────────────────────────────────
    //  LOGOUT ALL — সব device থেকে লগআউট
    // ─────────────────────────────────────────
    public function logoutAll(Request $request): JsonResponse
    {
        // সব token একসাথে delete করে
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'সকল ডিভাইস থেকে লগআউট সফল',
        ]);
    }

    // ─────────────────────────────────────────
    //  ME — লগিন করা user এর তথ্য
    // ─────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->userResource($request->user()),
        ]);
    }

    // ─────────────────────────────────────────
    //  FORGOT PASSWORD
    // ─────────────────────────────────────────
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'ইমেইল দিন',
            'email.email'    => 'সঠিক ইমেইল দিন',
            'email.exists'   => 'এই ইমেইলে কোনো অ্যাকাউন্ট নেই',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'পাসওয়ার্ড রিসেট লিংক ইমেইলে পাঠানো হয়েছে',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'কিছু সমস্যা হয়েছে, আবার চেষ্টা করুন',
        ], 500);
    }

    // ─────────────────────────────────────────
    //  GOOGLE — Redirect (Web flow)
    // ─────────────────────────────────────────
    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    // ─────────────────────────────────────────
    //  GOOGLE — Token Exchange
    //  Flutter থেকে id_token পাঠাবে এখানে
    // ─────────────────────────────────────────
    public function googleCallback(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Google token পাওয়া যায়নি',
            ], 422);
        }

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($request->id_token);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google লগিন যাচাই ব্যর্থ হয়েছে',
                'error'   => $e->getMessage(),
            ], 401);
        }

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name'              => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                'email_verified_at' => now(),
                'password'          => Hash::make(Str::random(24)),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
            ]
        );

        $user->tokens()->delete();
        $token = $user->createToken('hisabzone_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Google লগিন সফল!',
            'data'    => [
                'user'  => $this->userResource($user),
                'token' => $token,
            ],
        ]);
    }

    // ─────────────────────────────────────────
    //  HELPER
    // ─────────────────────────────────────────
    private function userResource(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'avatar'     => $user->avatar ?? null,
            'created_at' => $user->created_at?->toDateTimeString(),
        ];
    }
}
