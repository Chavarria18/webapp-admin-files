<?php

namespace App\Services;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;

class CognitoService
{
    public function __construct(
        private readonly CognitoIdentityProviderClient $client,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $userPoolId,
    ) {
    }

    public function register(string $email, string $password, string $name): void
    {
        $this->client->signUp([
            'ClientId' => $this->clientId,
            'SecretHash' => $this->secretHash($email),
            'Username' => $email,
            'Password' => $password,
            'UserAttributes' => [
                ['Name' => 'email', 'Value' => $email],
                ['Name' => 'name', 'Value' => $name],
            ],
        ]);
    }


    public function authenticate(string $email, string $password): array
    {
        $result = $this->client->adminInitiateAuth([
            'AuthFlow' => 'ADMIN_USER_PASSWORD_AUTH',
            'ClientId' => $this->clientId,
            'UserPoolId' => $this->userPoolId,
            'AuthParameters' => [
                'USERNAME' => $email,
                'PASSWORD' => $password,
                'SECRET_HASH' => $this->secretHash($email),
            ],
        ]);

        return [
            'challenge' => $result->get('ChallengeName'),
            'session' => $result->get('Session'),
            'authentication' => $result->get('AuthenticationResult'),
        ];
    }


    private function secretHash(string $username): string
    {
        return base64_encode(
            hash_hmac('sha256', $username . $this->clientId, $this->clientSecret, true)
        );
    }



    //Update password 
    public function completeNewPassword(
        string $email,
        string $newPassword,
        string $session
    ): array {
        $result = $this->client->respondToAuthChallenge([
            'ClientId' => $this->clientId,
            'ChallengeName' => 'NEW_PASSWORD_REQUIRED',
            'Session' => $session,
            'ChallengeResponses' => [
                'USERNAME' => $email,
                'NEW_PASSWORD' => $newPassword,

                'userAttributes.name' => $email,
                'SECRET_HASH' => $this->secretHash($email),

            ],
        ]);

        return [
            'access_token' => $result->get('AuthenticationResult')['AccessToken'] ?? null,
            'id_token' => $result->get('AuthenticationResult')['IdToken'] ?? null,
            'refresh_token' => $result->get('AuthenticationResult')['RefreshToken'] ?? null,
        ];
    }
}