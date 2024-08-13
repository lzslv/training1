<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResendEmailVerificationLinkRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use OpenApi\Annotations as OA;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Info(
 *     title="Testing1 API",
 *     version="1.0.0"
 * ),
 * @OA\PathItem(
 *     path="/api/"
 * )
 *
 * @OA\Components(
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer"
 *     )
 * )
 **/
class AuthController extends Controller
{

    protected $service;
    protected $emailVerificationService;

    public function __construct(AuthService $service, EmailVerificationService $emailVerificationService)
    {
        $this->service = $service;
        $this->emailVerificationService = $emailVerificationService;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Регистрация",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="vlad11"),
     *              @OA\Property(property="role_id", type="integer", example=1),
     *              @OA\Property(property="authtype", type="string", example="email"),
     *              @OA\Property(property="email", type="string", example="vlad11@gmail.com"),
     *              @OA\Property(property="password", type="string", example="123123"),
     *              @OA\Property(property="password_confirmation", type="string", example="123123"),
     *          )
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Успешная регистрация",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="name", type="string", example="vlad11"),
     *              @OA\Property(property="email", type="string", example="vlad11@gmail.com"),
     *              @OA\Property(property="role", type="string", example="user"),
     *              @OA\Property(property="authtype", type="string", example="email"),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-12T14:30:00Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-12T14:30:00Z"),
     *              @OA\Property(property="email_verified_at", type="string", format="date-time", example="2024-08-12T14:30:00Z")
     *          )
     *      ),
     * )
     **/

    public function register(RegisterRequest $request)
    {
        $user = $this->service->register($request->validated());

        $this->emailVerificationService->sendVerificationLink($user);
        return UserResource::make($user)->resolve();
    }

    /**
     * @OA\Post(
     *     path="/api/auth/email/resend-verification",
     *     summary="Повторная отправка ссылки для верификации электронной почты",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="vlad11@gmail.com")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешная отправка ссылки для верификации",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Verification link sent to your email")
     *         )
     *     ),
     * )
     **/

    public function resendEmailVerificationLink(ResendEmailVerificationLinkRequest $request)
    {
        $response = $this->emailVerificationService->resendVerificationLink($request->email);
        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/email/verify",
     *     summary="Подтверждение аккаунта",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="vlad11@gmail.com"),
     *             @OA\Property(property="token", type="string", example="7cbae7ec-e4a9-4ab0-9e9f-bf5caadc5810")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешное подтверждение электронной почты",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Email verified successfully")
     *         )
     *     ),
     * )
     **/


    public function verifyUserEmail(VerifyEmailRequest $request)
    {
        return $this->emailVerificationService->verifyEmail($request->email, $request->token);
    }


    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Аутентификация и авторизация",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property(property="email", type="string", example="vlad11@gmail.com"),
     *              @OA\Property(property="password", type="string", example="123123"),
     *          )
     *      ),
     *
     *     @OA\Response(
     *           response=200,
     *           description="Успешная аутентификация. Успешная авторизация",
     *           @OA\JsonContent(
     *               @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE3MjM0Njg4NDMsImV4cCI6MTcyMzQ3MjQ0MywibmJmIjoxNzIzNDY4ODQzLCJqdGkiOiJoT2xESE9tbDhRMk9KVTBIIiwic3ViIjoiMjEiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.TbJdwV8a0Nfn_cKazDxBQTcPbR49C43JIMz2PK3ucug"),
     *               @OA\Property(property="token_type", type="string", example="bearer"),
     *               @OA\Property(property="expires_in", type="integer", example=3600),
     *               @OA\Property(
     *                   property="user",
     *                   type="object",
     *                   @OA\Property(property="id", type="integer", example=13),
     *                   @OA\Property(property="name", type="string", example="admin"),
     *                   @OA\Property(property="email", type="string", example="admin@user.com"),
     *                   @OA\Property(property="role", type="string", example="admin"),
     *                   @OA\Property(property="authtype", type="string", example="email"),
     *                   @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-08T18:09:48.000000Z"),
     *                   @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-08T18:09:48.000000Z"),
     *                   @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example=null)
     *               )
     *           )
     *       ),
     * )
     **/

    public function login(LoginRequest $request)
    {
        if (!$token = auth('api')->attempt($request->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token);
    }

    public function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => new UserResource(auth('api')->user())
        ]);
    }

    public function user()
    {
        return response()->json(new UserResource(auth()->user()));
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function googleRedirect(Request $request)
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback(Request $request)
    {
        $userdata = Socialite::driver('google')->user();
        $uuid = Str::uuid()->toString();

        $user = User::where('email', $userdata->email)->where('authtype', 'google')->first();
        if ($user) {
            $token = JWTAuth::fromUser($user);
            auth('api')->setToken($token)->authenticate();
            return $this->createNewToken($token);
        } else {
            $user = new User();
            $user->name = $userdata['name'];
            $user->email = $userdata['email'];
            $user->password = Hash::make($uuid . now());
            $user->authtype = 'google';
            $user->role_id = 1;
            $user->email_verified_at = now();
            $user->save();
            return new UserResource($user);
        }
    }

    public function facebookRedirect(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function facebookCallback(Request $request): UserResource|\Illuminate\Http\JsonResponse
    {
        $userdata = Socialite::driver('facebook')->user();
        $uuid = Str::uuid()->toString();

        $user = User::where('email', $userdata->email)->where('authtype', 'facebook')->first();
        if ($user) {
            $token = JWTAuth::fromUser($user);
            auth('api')->setToken($token)->authenticate();
            return $this->createNewToken($token);
        } else {
            $user = new User();
            $user->name = $userdata['name'];
            $user->email = $userdata['email'];
            $user->password = Hash::make($uuid . now());
            $user->authtype = 'facebook';
            $user->role_id = 1;
            $user->email_verified_at = now();
            $user->save();
            return new UserResource($user);
        }
    }
}
