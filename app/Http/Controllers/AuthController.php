<?php

namespace App\Http\Controllers;

use App\Services\CognitoService;
use Exception;
use Illuminate\Http\Request;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use App\Models\User;
use App\Models\Area;
use App\Helpers\JwtHelper;

class AuthController extends Controller
{
    public function __construct(
        private readonly CognitoService $cognito
    ) {
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }



    public function showRegisterForm()
    {
        $areas = Area::all();
        return view('auth.register', compact('areas'));
    }

    public function storeUser(Request $request)
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'rol' => ['required', 'in:estandar,jefe_area,gerente,admin'],
            'area_id' => ['required', 'exists:areas,id'],
        ]);
        try {
            $result = $this->cognito->adminRegister(
                $validated['email'],
                $validated['password'],
                $validated['name']
            );

        } catch (CognitoIdentityProviderException $e) {

            $errorCode = $e->getAwsErrorCode();

            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'email' => match ($errorCode) {
                        'UsernameExistsException' =>
                            'A user with this email already exists in Cognito.',
                        'InvalidPasswordException' =>
                            'The password does not meet Cognito requirements.',
                        'InvalidParameterException' =>
                            'One or more values are invalid.',
                        'TooManyRequestsException' =>
                            'Too many requests. Please try again later.',
                        default =>
                            'Unable to create the user. Please try again.',
                    }
                ]);
        }

        $user = new User();
        $user->cognito_sub = $result['sub'];
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['rol'];
        $user->area_id = $validated['area_id'];
        $user->save();

        return redirect()->route('users.register')
            ->with('success', 'User succesfuly created');
    }



    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $result = $this->cognito->authenticate(
                $request->email,
                $request->password
            );

        } catch (CognitoIdentityProviderException $e) {

            $errorCode = $e->getAwsErrorCode();

            if ($errorCode === 'PasswordResetRequiredException') {

                session([
                    'cognito_reset_email' => $request->email,
                ]);

                return redirect()
                    ->route('auth.forgot-password')
                    ->with(
                        'info',
                        'Your password must be reset before you can sign in.'
                    );
            }


            if (
                in_array($errorCode, [
                    'NotAuthorizedException',
                    'UserNotFoundException',
                ])
            ) {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => 'Invalid email or password.',
                    ]);
            }

            throw $e;
        }


        if (($result['challenge'] ?? null) === 'NEW_PASSWORD_REQUIRED') {

            session([
                'cognito_email' => $request->email,
                'cognito_session' => $result['session'],
            ]);

            return redirect()->route('auth.new-password');
        }

        // Successful authentication
        $authentication = $result['authentication'];

        $claims = JwtHelper::decode(
            $authentication['IdToken']
        );

        $user = User::where(
            'cognito_sub',
            $claims->sub
        )->first();

        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'User is not registered in the system.',
                ]);
        }

        // Laravel authentication
        auth()->login($user);

        // Store Cognito session
        session([
            'cognito_user' => [
                'email' => $user->email,
                'access_token' => $authentication['AccessToken'],
                'id_token' => $authentication['IdToken'],
                'refresh_token' => $authentication['RefreshToken'] ?? null,
                'expires_at' => now()->addSeconds(
                    $authentication['ExpiresIn']
                ),
            ],
        ]);

        return redirect()->route('home');
    }

    public function showNewPasswordForm()
    {
        return view('auth.new-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'min:8'],
        ]);

        $this->cognito->completeNewPassword(
            session('cognito_email'),
            $request->password,
            session('cognito_session')
        );

        session()->forget([
            'cognito_email',
            'cognito_session',
        ]);

        return redirect()->route('auth.login')
            ->with('success', 'Password updated successfully.');
    }



    public function forgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function confirmPassword()
    {
        return view('auth.confirm-password');
    }

    public function confirmForgotPassword(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $email = session('cognito_reset_email');

        if (!$email) {
            return redirect()
                ->route('auth.forgot-password')
                ->withErrors([
                    'email' => 'Password reset session expired.',
                ]);
        }

        try {
            $this->cognito->confirmForgotPassword(
                $email,
                $request->code,
                $request->password
            );



            return redirect()
                ->route('auth.login')
                ->with('success', 'Password reset successfully. You can now log in.');

        } catch (CognitoIdentityProviderException $e) {

            if ($e->getAwsErrorCode() === 'CodeMismatchException') {
                return back()->withErrors([
                    'code' => 'Invalid verification code.',
                ]);
            }

            if ($e->getAwsErrorCode() === 'ExpiredCodeException') {
                return back()->withErrors([
                    'code' => 'The verification code has expired.',
                ]);
            }

            if ($e->getAwsErrorCode() === 'InvalidPasswordException') {
                return back()->withErrors([
                    'password' => 'The password does not meet Cognito requirements.',
                ]);
            }

            throw $e;
        }
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $this->cognito->forgotPassword($request->email);

            session([
                'cognito_reset_email' => $request->email,
            ]);

            return redirect()->route('auth.confirm-forgot-password.index');

        } catch (CognitoIdentityProviderException $e) {

            if ($e->getAwsErrorCode() === 'UserNotFoundException') {
                return back()->withErrors([
                    'email' => 'User not found.',
                ]);
            }

            if ($e->getAwsErrorCode() === 'InvalidParameterException') {
                return back()->withErrors([
                    'email' => 'This user does not have a verified email or phone number.',
                ]);
            }

            throw $e;
        }
    }


    public function logout()
    {
        auth()->logout();

        session()->forget('cognito_user');
        session()->invalidate();
        session()->regenerateToken();

        return redirect()
            ->route('auth.login')
            ->with('success', 'Logged out successfully.');
    }




}