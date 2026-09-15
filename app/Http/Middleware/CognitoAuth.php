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
                ->with('error', 'Your session has expired.');
        }

        try {
            $claims = JwtHelper::decode($user['id_token']);
        } catch (\Exception $e) {
            session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->with('error', 'Invalid session, please log in again.');
        }
        $user = User::where('cognito_sub', $claims->sub)->first();
        Auth::login($user);
        return $next($request);
    }
}
