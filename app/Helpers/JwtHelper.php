<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Cache;

class JwtHelper
{
    public static function decode(string $idToken): object
    {
        $jwks = Cache::remember('cognito_jwks', 3600, function () {
            $url = "https://cognito-idp." . config('cognito.region') . ".amazonaws.com/"
                . config('cognito.user_pool_id') . "/.well-known/jwks.json";

            return json_decode(file_get_contents($url), true);
        });

        return JWT::decode($idToken, JWK::parseKeySet($jwks));
    }
}