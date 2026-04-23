<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\AdminUser;
use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class AuthController
 *
 * Handles user authentication via JWT, sessions, and refresh tokens.
 *
 * @package App\Http\Controllers\Auth
 */
final class AuthController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'usernameOrEmail' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $usernameOrEmail = $request->input('usernameOrEmail');
        $password = $request->input('password');

        \Log::info('Login attempt', ['identifier' => $usernameOrEmail]);

        $user = AdminUser::query()
            ->with(['roles', 'roles.permissions', 'unit'])
            ->where('email', $usernameOrEmail)
            ->orWhere('username', $usernameOrEmail)
            ->first();

        if ($user === null || !Hash::check($password, $user->password)) {
            return $this->errorResponse(__('auth.login_error'), 'INVALID_CREDENTIALS', HttpStatus::UNAUTHORIZED);
        }

        $token = JWTAuth::fromUser($user);
        $refreshToken = Str::random(60);

        DB::table('ipa_auth_session')->insert([
            'user_id' => $user->id,
            'access_token_jti' => Str::random(40),
            'refresh_token_hash' => Hash::make($refreshToken),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'issued_at' => now(),
            'expires_at' => now()->addMinutes(config('jwt.refresh_ttl', 20160)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userData = $user->toArray();
        $userData['role_codes'] = $user->role_codes;
        $userData['permission_codes'] = $user->permission_codes;

        return $this->successResponse([
            'accessToken' => $token,
            'refreshToken' => $refreshToken,
            'expiresIn' => (int) config('jwt.ttl', 60) * 60,
            'user' => $userData,
        ], __('auth.login_success'));
    }

    /**
     * Get the authenticated User's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'roles.permissions', 'unit']);
        $userData = $user->toArray();
        $userData['role_codes'] = $user->role_codes;
        $userData['permission_codes'] = $user->permission_codes;

        return $this->successResponse(
            $userData,
            __('auth.login_success')
        );
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Invalidate current JWT
            JWTAuth::invalidate(JWTAuth::getToken());

            // Revoke sessions in DB
            if ($user) {
                DB::table('ipa_auth_session')
                    ->where('user_id', $user->id)
                    ->whereNull('revoked_at')
                    ->update([
                        'revoked_at' => now(),
                        'updated_at' => now()
                    ]);
            }

            return $this->successResponse(null, __('auth.logout_success'));
        } catch (JWTException $e) {
            return $this->errorResponse(__('auth.logout_error'), 'LOGOUT_FAILED', HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Refresh the user's JWT access token using a refresh token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refreshToken' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        try {
            $refreshToken = (string) $request->input('refreshToken');

            $session = DB::table('ipa_auth_session')
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->orderByDesc('id')
                ->get()
                ->first(static fn (object $row): bool => Hash::check($refreshToken, (string) $row->refresh_token_hash));

            if ($session === null) {
                return $this->errorResponse(__('auth.refresh_error'), 'REFRESH_TOKEN_INVALID', HttpStatus::UNAUTHORIZED);
            }

            $user = AdminUser::query()->find((int) $session->user_id);

            if ($user === null) {
                return $this->errorResponse(__('auth.refresh_error'), 'REFRESH_TOKEN_INVALID', HttpStatus::UNAUTHORIZED);
            }

            $newAccessToken = JWTAuth::fromUser($user);

            return $this->successResponse([
                'accessToken' => $newAccessToken,
                'expiresIn' => (int) config('jwt.ttl', 60) * 60,
            ], __('auth.refresh_success'));
        } catch (JWTException $exception) {
            return $this->errorResponse(__('auth.refresh_error'), 'REFRESH_TOKEN_INVALID', HttpStatus::UNAUTHORIZED);
        }
    }
}