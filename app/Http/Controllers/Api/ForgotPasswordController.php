<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    // ════════════════════════════════════════════════════════
    // Step 1 ─ POST /api/forgot-password
    // OTP generate + email পাঠাও
    // ════════════════════════════════════════════════════════
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'এই ইমেইলে কোনো অ্যাকাউন্ট নেই',
            ], 404);
        }

        // ── OTP generate (6 digits) ────────────────────────
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // ── পুরনো OTP মুছে নতুন save করো ─────────────────
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => $otp,
            'created_at' => Carbon::now(),
        ]);

        // ── HTML Email build করো (blade ছাড়া) ─────────────
        $html = $this->buildOtpEmailHtml($otp, $user->name);

        try {
            Mail::html($html, function (Message $message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('পাসওয়ার্ড রিসেট OTP - HisabZone');
            });

            return response()->json([
                'success' => true,
                'message' => "{$user->email} এ OTP পাঠানো হয়েছে",
            ]);

        } catch (\Exception $e) {
            // ── Email fail হলে error log করো + dev OTP দাও ─
            Log::error('OTP Mail Error: ' . $e->getMessage());

            return response()->json([
                'success' => true,
                'message' => 'OTP তৈরি হয়েছে (dev mode - email যায়নি)',
                'otp'     => $otp, // ⚠️ production এ এই line সরিয়ে দাও
                'error'   => $e->getMessage(), // debug এর জন্য
            ]);
        }
    }

    // ════════════════════════════════════════════════════════
    // Step 2 ─ POST /api/verify-otp
    // OTP যাচাই করো, reset token দাও
    // ════════════════════════════════════════════════════════
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->otp)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'ভুল OTP। আবার চেষ্টা করুন।',
            ], 422);
        }

        // ── 10 মিনিট expiry check ──────────────────────────
        $createdAt = Carbon::parse($record->created_at);
        if (Carbon::now()->diffInMinutes($createdAt) > 10) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            return response()->json([
                'success' => false,
                'message' => 'OTP মেয়াদ শেষ (১০ মিনিট)। নতুন OTP নিন।',
            ], 422);
        }

        // ── Secure reset token generate ────────────────────
        $resetToken = bin2hex(random_bytes(32));

        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->update([
                'token'      => $resetToken,
                'created_at' => Carbon::now(), // timer reset করো
            ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP সফলভাবে যাচাই হয়েছে',
            'token'   => $resetToken,
        ]);
    }

    // ════════════════════════════════════════════════════════
    // Step 3 ─ POST /api/reset-password
    // নতুন পাসওয়ার্ড সেট করো
    // ════════════════════════════════════════════════════════
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required|string',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        // ── Token যাচাই ────────────────────────────────────
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'অবৈধ বা মেয়াদ শেষ token। আবার শুরু করুন।',
            ], 422);
        }

        // ── 15 মিনিট expiry check ──────────────────────────
        $createdAt = Carbon::parse($record->created_at);
        if (Carbon::now()->diffInMinutes($createdAt) > 15) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            return response()->json([
                'success' => false,
                'message' => 'Token মেয়াদ শেষ (১৫ মিনিট)। আবার শুরু করুন।',
            ], 422);
        }

        // ── User খোঁজো ────────────────────────────────────
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'ব্যবহারকারী খুঁজে পাওয়া যায়নি',
            ], 404);
        }

        // ── পাসওয়ার্ড আপডেট ──────────────────────────────
        $user->update(['password' => Hash::make($request->password)]);

        // ── Token মুছে দাও ────────────────────────────────
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // ── সব পুরনো Sanctum token revoke করো ────────────
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে। লগইন করুন।',
        ]);
    }

    // ════════════════════════════════════════════════════════
    // HTML Email Builder (blade ছাড়া inline HTML)
    // ════════════════════════════════════════════════════════
    private function buildOtpEmailHtml(string $otp, string $name): string
    {
        $year = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>পাসওয়ার্ড রিসেট OTP</title>
</head>
<body style="margin:0;padding:0;background:#f5f0ff;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0ff;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="480" cellpadding="0" cellspacing="0"
               style="background:#ffffff;border-radius:20px;overflow:hidden;
                      box-shadow:0 4px 24px rgba(124,58,237,0.10);max-width:480px;">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#7C3AED,#A855F7);
                        padding:36px 40px;text-align:center;">
              <div style="font-size:40px;margin-bottom:8px;">🔐</div>
              <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:800;">HisabZone</h1>
              <p style="margin:6px 0 0;color:rgba(255,255,255,0.8);font-size:13px;">
                পাসওয়ার্ড রিসেট অনুরোধ
              </p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:40px;">
              <p style="margin:0 0 8px;color:#1E1040;font-size:16px;font-weight:600;">
                হ্যালো, {$name} 👋
              </p>
              <p style="margin:0 0 28px;color:#6B5B8A;font-size:14px;line-height:1.7;">
                আপনার <strong>HisabZone</strong> অ্যাকাউন্টের পাসওয়ার্ড রিসেট করতে
                নিচের <strong>৬ সংখ্যার OTP</strong> কোডটি ব্যবহার করুন।
                এই কোডটি <strong>১০ মিনিট</strong> পর্যন্ত কার্যকর থাকবে।
              </p>

              <!-- OTP Box -->
              <div style="background:#f5f0ff;border:2px dashed #7C3AED;border-radius:16px;
                           padding:28px;text-align:center;margin-bottom:28px;">
                <p style="margin:0 0 10px;color:#6B5B8A;font-size:12px;
                            text-transform:uppercase;letter-spacing:2px;font-weight:600;">
                  আপনার OTP কোড
                </p>
                <div style="font-size:44px;font-weight:900;letter-spacing:12px;
                              color:#7C3AED;font-family:monospace;">
                  {$otp}
                </div>
              </div>

              <!-- Warning -->
              <div style="background:#fffbeb;border-left:4px solid #f59e0b;
                           border-radius:8px;padding:14px 16px;margin-bottom:20px;">
                <p style="margin:0;color:#92400e;font-size:13px;line-height:1.6;">
                  ⚠️ <strong>সতর্কতা:</strong> আপনি যদি পাসওয়ার্ড রিসেট করার
                  চেষ্টা না করে থাকেন, তাহলে এই ইমেইল উপেক্ষা করুন।
                </p>
              </div>

              <p style="margin:0;color:#9CA3AF;font-size:12px;text-align:center;">
                এই কোডটি কখনো কারো সাথে শেয়ার করবেন না।
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#faf5ff;padding:20px 40px;text-align:center;
                        border-top:1px solid #ede9fe;">
              <p style="margin:0;color:#9CA3AF;font-size:12px;">
                &copy; {$year} HisabZone &middot; Manage Your Accounts
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }
}
