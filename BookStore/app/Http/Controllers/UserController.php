<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\BookStoreException;
use App\Models\User;
use App\Notifications\PasswordResetRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\Auth;


class UserController extends Controller
{

    /**
     * @OA\Post(
     *   path="/api/register",
     *   summary="register",
     *   description="register the user for login",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"role","firstname","lastname","phone_no","email", "password", "confirm_password"},
     *               @OA\Property(property="role", type="string"),
     *               @OA\Property(property="firstname", type="string"),
     *               @OA\Property(property="lastname", type="string"),
     *               @OA\Property(property="phone_no", type="string"),
     *               @OA\Property(property="email", type="string"),
     *               @OA\Property(property="password", type="password"),
     *               @OA\Property(property="confirm_password", type="password")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="User successfully registered"),
     *   @OA\Response(response=401, description="The email has already been taken"),
     * )
     * It takes a POST request and requires fields for the user to register,
     * and validates them if it is validated,creates those values in DB
     * and returns success response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'role' => 'required|string|between:2,10',
                'firstname' => 'required|string|between:2,50',
                'lastname' => 'required|string|between:2,50',
                'phone_no' => 'required|string|min:10',
                'email' => 'required|string|email|max:100',
                'password' => 'required|string|min:6',
                'confirm_password' => 'required|same:password',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $userObject = new User();

            $user = $userObject->userEmailValidation($request->email);
            if ($user) {
                throw new BookStoreException("The email has already been taken", 200);
            }

            $userObject->saveUserDetails($request);
            Log::info('Registered user Email : ' . 'Email Id :' . $request->email);
            Cache::remember('users', 3600, function () {
                return DB::table('users')->get();
            });

            return response()->json([
                'status' => 201,
                'message' => 'User Successfully Registerd',
            ], 201);
        } catch (BookStoreException $exception) {
            Log::error('Invalid User');
            return $exception->message();
        }
    }


    /**
     * @OA\Post(
     *   path="/api/login",
     *   summary="login",
     *   description=" login ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email", "password"},
     *               @OA\Property(property="email", type="string"),
     *               @OA\Property(property="password", type="password"),
     *            ),
     *        ),
     *    ),
     * @OA\Response(response=200, description="Login successfull"),
     * @OA\Response(response=401, description="email not found register first"),
     * 
     * )
     * Takes the POST request and user credentials checks if it correct,
     * if so, returns JWT access token.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            Cache::remember('users', 3600, function () {
                return User::all();
            });

            $userObject = new User();
            $user = $userObject->userEmailValidation($request->email);
            if (!$user) {
                Log::error('email not found register first', ['id' => $request->email]);
                throw new BookStoreException("email not found register first", 401);
            }

            if (!$token = auth()->attempt($validator->validated())) {
                throw new BookStoreException("Invalid Credentials", 400);
            }

            Log::info('Login Success : ' . 'Email Id :' . $request->email);
            return response()->json([
                'status' => 200,
                'access_token' => $token,
                'message' => 'Login successfull'
            ], 200);
        } catch (BookStoreException $exception) {
            Log::error('Invalid User');
            return $exception->message();
        }
    }

    /**
     * Takes the POST request and JWT access token to logout the user profile
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *   path="/api/logout",
     *   summary="logout",
     *   description=" logout ",
     *  @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"token",},
     *               @OA\Property(property="token", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=200, description="User successfully signed out"),
     * )
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status' => 200,
            'message' => 'User successfully signed out'
        ], 200);
    }

    /**
     *  @OA\Post(
     *   path="/api/forgotPassword",
     *   summary="forgot password",
     *   description="forgot user password",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="email", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=200, description="Password Reset link is send to your email"),
     *   @OA\Response(response=400, description="we can not find a user with that email address"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * This API Takes the request which is the email id and validates it and check where that email id
     * is present in DataBase or not, if it is not,it returns failure with the appropriate response code and
     * checks for password reset model once the email is valid and calling the function Mail::Send
     * by passing args and successfully sending the password reset link to the specified email id.
     *
     * @return success reponse about reset link.
     */

    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'Validation_error' => $validator->errors(),
                ]);
            }
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                log::error('Not a registered email');
                throw new BookStoreException('Not a Registered Email', 404);
            }

            $token = JWTAuth::fromUser($user);

            if ($user) {
                $delay = now()->addSeconds(30);
                $user->notify((new PasswordResetRequest($user->email, $token))->delay($delay));
                }
                Log::info('Reset Password Token Sent to your Email');
                return response()->json([
                    'status' => 200,
                    'message' => 'Password Reset link is send to your email'
                ]);
            
        } catch (BookStoreException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->message());
        }
    }


/**
     * @OA\Post(
     *   path="/api/resetPassword",
     *   summary="resetpassword",
     *   description="reset your password",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "new_password", "confirm_password"},
     *               @OA\Property(property="new_password", type="password"),
     *               @OA\Property(property="confirm_password", type="password"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Password reset successfull!"),
     *   @OA\Response(response=400, description="we can not find the user with that e-mail address"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
     /**
     * This API Takes the request which has new password and confirm password and validates both of them
     * if validation fails returns failure resonse and if it passes it checks with DB whether the token
     * is there or not if not returns a failure response and checks the user email also if everything is
     * good resets the password successfully.
     *
     */
    public function resetPassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'new_password' => 'min:6|required|',
            'confirm_password' => 'required|same:new_password'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => "Password doesn't match"
            ], 400);
        }
        try {
            $currentUser = JWTAuth::user();
            $userObject = new User();
            $user = $userObject->userEmailValidation($currentUser->email);
            if (!$user) {
                Log::error('Email not found.', ['id' => $request->email]);
                throw new BookStoreException("we can not find a user with that email address", 404);
            } else {
                $user->password = bcrypt($request->new_password);
                $user->save();
                Log::info('Reset Successful : ' . 'Email Id :' . $request->email);
                return response()->json([
                    'status' => 201,
                    'message' => 'Password reset successfull!'
                ], 201);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }

}
