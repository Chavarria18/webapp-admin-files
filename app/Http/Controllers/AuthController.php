<?php

namespace App\Http\Controllers;

use App\Services\CognitoService;
use Exception;
use Illuminate\Http\Request;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use App\Models\User;
use App\Http\Controllers\Area;
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

    public function storeUser(Request $request, string $cognito)
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'rol' => ['required', 'in:estandar,jefe_area,gerente,admin'],
            'area_id' => ['required', 'exists:areas,id'],
        ]);


        $user = new User();
        $user->cognito_sub = $cognito;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->rol = $validated['rol'];
        $user->area_id = $validated['area_id'];
        $user->save();

        return redirect()->route('auth.login')
            ->with('success', 'Password updated successfully.');
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

            if (
                in_array($e->getAwsErrorCode(), [
                    'NotAuthorizedException',
                    'UserNotFoundException'
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
        $authentication = $result['authentication'];
        session([
            'cognito_user' => [
                'email' => $request->email,
                'access_token' => $authentication['AccessToken'],
                'id_token' => $authentication['IdToken'],
                'refresh_token' => $authentication['RefreshToken'] ?? null,
                'expires_at' => now()->addSeconds(
                    $authentication['ExpiresIn']
                ),
                'expires_in' => $authentication['ExpiresIn'],
                'now' => now()
            ],
        ]);
        $claims =  JwtHelper::decode($authentication['IdToken']);
        $user = User::where('cognito_sub', $claims->sub)->first();

        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Usuario no registrado en el sistema.']);
        }


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



}