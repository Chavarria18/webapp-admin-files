<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\JwtHelper;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class CognitoAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = session('cognito_user');
        if (!$user) {
            return redirect()->route('auth.login');
        }
        $user = session('cognito_user');
        if (
            !isset($user['expires_at']) ||
            now()->greaterThanOrEqualTo($user['expires_at'])
        ) {

            session()->invalidate();

            return redirect()
                ->route('auth.login')
                ->withErrors(['error' => 'Tu sesión ha expirado.']);
        }

        try {
            $claims = JwtHelper::decode($user['id_token']);
        } catch (\Exception $e) {
            session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Sesión inválida, por favor inicia sesión nuevamente.']);
        }
        $user = User::where('cognito_sub', $claims->sub)->first();
        if (!$user) {
            session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->withErrors(['error' => 'No se encontró tu usuario, por favor inicia sesión nuevamente.']);
        }

       
        return $next($request);
    }


}
