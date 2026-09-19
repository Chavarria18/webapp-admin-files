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
            'UsernameExistsException' => 'Ya existe un usuario con este correo electrónico en Cognito.',
            'InvalidPasswordException' => 'La contraseña no cumple con los requisitos de Cognito.',
            'InvalidParameterException' => 'Uno o más valores no son válidos.',
            'TooManyRequestsException' => 'Demasiadas solicitudes. Por favor, inténtalo de nuevo más tarde.',
            default => 'No se pudo crear el usuario. Por favor, inténtalo de nuevo.',
        };
    }
}
