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
                    'UserNotFoundException' => 'Usuario no encontrado.',
                    'InvalidParameterException' => 'Este usuario no tiene un correo electrónico o teléfono verificado.',
                    'LimitExceededException' => 'Se excedió el límite de intentos. Por favor, inténtalo de nuevo más tarde.',
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
                'field' => in_array($e->getAwsErrorCode(), ['CodeMismatchException', 'ExpiredCodeException', 'LimitExceededException'])
                    ? 'code'
                    : 'password',
                'message' => match ($e->getAwsErrorCode()) {
                    'CodeMismatchException' => 'Código de verificación inválido.',
                    'ExpiredCodeException' => 'El código de verificación ha expirado.',
                    'LimitExceededException' => 'Se excedió el límite de intentos. Por favor, inténtalo de nuevo más tarde.',
                    'InvalidPasswordException' => 'La contraseña no cumple con los requisitos de Cognito.',
                    'PasswordHistoryPolicyViolationException' => 'La contraseña ya fue utilizada anteriormente. Por favor, elige una diferente.',
                    default => throw $e,
                },
            ];
        }

        return ['status' => 'success'];
    }
}
