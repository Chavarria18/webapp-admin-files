<?php

namespace App\Services;

use App\Helpers\JwtHelper;
use App\Models\User;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;

class AuthenticateCognitoUser
{
    public function __construct(
        private readonly CognitoService $cognito
    ) {
    }

    public function __invoke(string $email, string $password): array
    {
        try {
            $result = $this->cognito->authenticate($email, $password);
        } catch (CognitoIdentityProviderException $e) {
            return $this->handleCognitoError($e);
        }

        if (($result['challenge'] ?? null) === 'NEW_PASSWORD_REQUIRED') {
            return [
                'status' => 'new_password_required',
                'email' => $email,
                'session' => $result['session'],
            ];
        }

        $authentication = $result['authentication'];

        $claims = JwtHelper::decode($authentication['IdToken']);

        $user = User::where('cognito_sub', $claims->sub)->first();

        if (!$user) {
            return ['status' => 'user_not_registered'];
        }

        return [
            'status' => 'success',
            'user' => $user,
            'authentication' => $authentication,
        ];
    }

    private function handleCognitoError(CognitoIdentityProviderException $e): array
    {
        return match ($e->getAwsErrorCode()) {
            'PasswordResetRequiredException' => [
                'status' => 'password_reset_required',
            ],
            'NotAuthorizedException', 'UserNotFoundException' => [
                'status' => 'invalid_credentials',
            ],
            default => throw $e,
        };
    }
}
