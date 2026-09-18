<?php

namespace App\Services;

use App\Models\User;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;

class RegisterCognitoUser
{
    public function __construct(
        private readonly CognitoService $cognito
    ) {
    }

    public function __invoke(array $data): array
    {
        try {
            $result = $this->cognito->adminRegister(
                $data['email'],
                $data['password'],
                $data['name']
            );
        } catch (CognitoIdentityProviderException $e) {
            return [
                'status' => 'error',
                'message' => $this->errorMessageFor($e->getAwsErrorCode()),
            ];
        }

        $user = new User();
        $user->cognito_sub = $result['sub'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['rol'];
        $user->area_id = $data['rol'] === 'gerente' ? null : $data['area_id'];
        $user->save();

        if ($data['rol'] === 'gerente') {
            $user->areasGestionadas()->sync($data['area_ids']);
        }

        return ['status' => 'success', 'user' => $user];
    }

    private function errorMessageFor(?string $errorCode): string
    {
        return match ($errorCode) {
            'UsernameExistsException' => 'A user with this email already exists in Cognito.',
            'InvalidPasswordException' => 'The password does not meet Cognito requirements.',
            'InvalidParameterException' => 'One or more values are invalid.',
            'TooManyRequestsException' => 'Too many requests. Please try again later.',
            default => 'Unable to create the user. Please try again.',
        };
    }
}
