<?php

namespace App\Http\Controllers;

use App\Services\CognitoService;
use App\Services\AuthenticateCognitoUser;
use App\Services\RegisterCognitoUser;
use App\Services\ResetCognitoPassword;
use App\Http\Requests\StoreUserRequest;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Area;

class AuthController extends Controller
{
    public function __construct(
        private readonly CognitoService $cognito,
        private readonly AuthenticateCognitoUser $authenticateCognitoUser,
        private readonly RegisterCognitoUser $registerCognitoUser,
        private readonly ResetCognitoPassword $resetCognitoPassword
    ) {
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }



    public function showRegisterForm()
    {
        $this->authorize('create', User::class);

        $areas = Area::all();
        return view('auth.register', compact('areas'));
    }

    public function storeUser(StoreUserRequest $request)
    {
        $result = ($this->registerCognitoUser)($request->validated());

        if ($result['status'] === 'error') {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['email' => $result['message']]);
        }

        return redirect()->route('users.register')
            ->with('success', 'User succesfuly created');
    }



    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $result = ($this->authenticateCognitoUser)(
            $request->email,
            $request->password
        );

        return match ($result['status']) {
            'password_reset_required' => $this->redirectToPasswordReset($request->email),
            'invalid_credentials' => back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']),
            'new_password_required' => $this->redirectToNewPassword($result['email'], $result['session']),
            'user_not_registered' => back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'User is not registered in the system.']),
            'success' => $this->establishSession($result['user'], $result['authentication']),
        };
    }

    private function redirectToPasswordReset(string $email)
    {
        session(['cognito_reset_email' => $email]);

        return redirect()
            ->route('auth.forgot-password')
            ->with('info', 'Your password must be reset before you can sign in.');
    }

    private function redirectToNewPassword(string $email, string $cognitoSession)
    {
        session([
            'cognito_email' => $email,
            'cognito_session' => $cognitoSession,
        ]);

        return redirect()->route('auth.new-password');
    }

    private function establishSession(User $user, array $authentication)
    {
        auth()->login($user);

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

        $result = $this->resetCognitoPassword->confirm(
            $email,
            $request->code,
            $request->password
        );

        if ($result['status'] === 'error') {
            return back()->withErrors([$result['field'] => $result['message']]);
        }

        return redirect()
            ->route('auth.login')
            ->with('success', 'Password reset successfully. You can now log in.');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $result = $this->resetCognitoPassword->sendCode($request->email);

        if ($result['status'] === 'error') {
            return back()->withErrors([$result['field'] => $result['message']]);
        }

        session([
            'cognito_reset_email' => $request->email,
        ]);

        return redirect()->route('auth.confirm-forgot-password.index');
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