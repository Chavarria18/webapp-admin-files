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


     public function authenticate(string $email, string $password): bool
    {
        try {
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
        } catch (CognitoIdentityProviderException $e) {
            if (in_array($e->getAwsErrorCode(), ['NotAuthorizedException', 'UserNotFoundException'])) {
                return false;
            }

            throw $e;
        }

        return isset($result['AuthenticationResult']);
    }


    private function secretHash(string $username): string
    {
        return base64_encode(
            hash_hmac('sha256', $username . $this->clientId, $this->clientSecret, true)
        );
    }
}