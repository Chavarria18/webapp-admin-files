<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\File;
use App\Services\CognitoService;
class UserController extends Controller
{
    public function __construct(
        private readonly CognitoService $cognito
    ) {
    }
    public function index()
    {
        $users = User::paginate(20);
        return view('users.index', compact('users'));
    }
    public function edit(User $user)
    {
        return view('users.edit', ['user' => $user, 'areas' => Area::all()]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:estandar,jefe_area,gerente,admin'],
            'area_id' => ['required', 'exists:areas,id'],
        ]);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        try {

            $this->cognito->deleteUser($user->email);

        } catch (CognitoIdentityProviderException $e) {

            return redirect()
                ->route('users.index')
                ->withErrors([
                    'user' => match ($e->getAwsErrorCode()) {
                        'UserNotFoundException' =>
                            'The user does not exist in Cognito.',
                        'NotAuthorizedException' =>
                            'You are not authorized to delete this user.',
                        'TooManyRequestsException' =>
                            'Too many requests. Please try again later.',
                        default =>
                            'Unable to delete the user from Cognito.',
                    },
                ]);
        }
        $admin = User::where('role', 'admin')->first();
        File::where('user_id', $user->id)->update([
            'user_id' => $admin->id,
            'observacion' => 'Archivo transferido debido a la eliminación del usuario.',
        ]);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }
}
