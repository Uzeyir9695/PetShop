<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\JwtTokenTrait;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Encoding\JoseEncoder;

class PasswordResetController extends Controller
{
    use JwtTokenTrait;

    /**
     * @OA\Post(
     *     path="/api/v1/user/forgot-password",
     *     tags={"Users"},
     *     summary="Send password reset link to user's email",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Password reset email sent",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Password reset email sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */

    public function forgot_password(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Generate a unique token for password reset
        $token = $this->generatePasswordResetToken($user);

        // Send email with password reset link
        $resetUrl = config('app.url') . '/api/v1/user/reset-password-token/?token=' . $token;
        // Send email with password reset link to user's email address

        return response()->json(['message' => 'Password reset email sent'], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/user/password-reset",
     *     tags={"Users"},
     *     summary="Reset user password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "password"},
     *             @OA\Property(property="token", type="string", example="{jwt-token}"),
     *             @OA\Property(property="password", type="string", format="password", example="new_password")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Password updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Invalid token provided"
     *     )
     * )
     */

    public function password_reset(Request $request)
    {   // check if uuids matches

        $parser = new Parser(new JoseEncoder());
        $tokenUuid = $parser->parse($request->token);

        $email = $tokenUuid->claims()->get('user_data')['email'];
        $tokenJti = DB::table('password_reset_tokens')->where('email', $email)->value('token');

        if($tokenJti === $tokenUuid->claims()->get('user_data')['uuid']){
            User::where('email', $email)->update([
                'password' => Hash::make($request->password)
            ]);
            DB::table('password_reset_tokens')->where('email', $email)->delete();
        } else {
            return response()->json(['message' => 'Invalid token provided!']);
        }

        return response()->json(['message' => 'Password updated successfully']);
    }

    private function generatePasswordResetToken(User $user): string
    {
        $currentTime = new \DateTimeImmutable();
        $data = [
            'uuid' => $user->uuid,
            'email' => $user->email,
            'expires_at' => $currentTime->add(new \DateInterval('PT1H')), // Token valid for 1 hour
        ];

        $token = $this->generateJwtToken($data);
        // Save the token and the associated user ID in a database table
        DB::table('password_reset_tokens')->update([
            'token' => $token->claims()->get('jti'),
            'email' => $user->email,
        ]);

        return $token->toString();
    }
}
