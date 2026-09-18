<?php

namespace App\Services;

use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;

class ResetCognitoPassword
{
    public function __construct(
        private readonly CognitoService $cognito
    ) {
    }

    public function sendCode(string $email): array
    {
        try {
            $this->cognito->forgotPassword($email);
        } catch (CognitoIdentityProviderException $e) {
            return [
                'status' => 'error',
                'field' => 'email',
                'message' => match ($e->getAwsErrorCode()) {
                    'UserNotFoundException' => 'User not found.',
                    'InvalidParameterException' => 'This user does not have a verified email or phone number.',
                    default => throw $e,
                },
            ];
        }

        return ['status' => 'success'];
    }

    public function confirm(string $email, string $code, string $password): array
    {
        try {
            $this->cognito->confirmForgotPassword($email, $code, $password);
        } catch (CognitoIdentityProviderException $e) {
            return [
                'status' => 'error',
                'field' => in_array($e->getAwsErrorCode(), ['CodeMismatchException', 'ExpiredCodeException'])
                    ? 'code'
                    : 'password',
                'message' => match ($e->getAwsErrorCode()) {
                    'CodeMismatchException' => 'Invalid verification code.',
                    'ExpiredCodeException' => 'The verification code has expired.',
                    'InvalidPasswordException' => 'The password does not meet Cognito requirements.',
                    default => throw $e,
                },
            ];
        }

        return ['status' => 'success'];
    }
}
