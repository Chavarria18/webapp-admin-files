<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\File;
use App\Models\Area;
use App\Services\CognitoService;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Illuminate\Auth\Access\AuthorizationException;
class UserController extends Controller
{
    public function __construct(
        private readonly CognitoService $cognito
    ) {
    }
    public function index(Request $request)
    {
        $search = $request->search;

        $query = User::visibleTo($request->user())->with(['area', 'areasGestionadas'])->withCount('files');

        if ($request->filled('area_id')) {
            if ($request->role === 'gerente') {
                $query->whereHas('areasGestionadas', fn ($q) => $q->where('areas.id', $request->area_id));
            } else {
                $query->where('area_id', $request->area_id);
            }
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if (! empty($search)) {
            $query->where('email', 'like', "%{$search}%");
        }

        $users = $query->paginate(10);

        return view('users.index', compact('users'));
    }
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', ['user' => $user, 'areas' => Area::all()]);
    }

    public function organigrama()
    {
        $this->authorize('viewOrganigrama', User::class);

        $admins = User::where('role', 'admin')->get();

        $areas = Area::with([
            'gerentes',
            'usuarios' => fn ($query) => $query->whereIn('role', ['jefe_area', 'estandar']),
        ])->get();

        return view('users.organigarm', compact('admins', 'areas'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:estandar,jefe_area,gerente,admin'],
           
        ]);

        if ($request->role === 'gerente') {

            $request->validate([
                'area_ids' => ['required', 'array', 'min:1'],
                'area_ids.*' => ['exists:areas,id'],
            ]);

            $user->update([
                'name' => $validated['name'],
                'role' => $validated['role'],
                'area_id' => null,
            ]);

            $user->areasGestionadas()->sync($request->area_ids);

        } else {

            $request->validate([
                'area_id' => ['required', 'exists:areas,id'],
            ]);

            $user->update([
                'name' => $validated['name'],
                'role' => $validated['role'],
                'area_id' => $request->area_id,
            ]);

            $user->areasGestionadas()->detach();
        }

       

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        try {

            $this->authorize('delete', $user);

        } catch (AuthorizationException $e) {

            return redirect()
                ->route('users.index')
                ->withErrors(['user' => $e->getMessage()]);
        }

        try {

            $this->cognito->deleteUser($user->email);

        } catch (CognitoIdentityProviderException $e) {

            return redirect()
                ->route('users.index')
                ->withErrors([
                    'user' => match ($e->getAwsErrorCode()) {
                        'UserNotFoundException' =>
                            'El usuario no existe en Cognito.',
                        'NotAuthorizedException' =>
                            'No tienes autorización para eliminar este usuario.',
                        'TooManyRequestsException' =>
                            'Demasiadas solicitudes. Por favor, inténtalo de nuevo más tarde.',
                        default =>
                            'No se pudo eliminar el usuario de Cognito.',
                    },
                ]);
        }
        $filesCount = File::where('user_id', $user->id)->count();

        File::where('user_id', $user->id)->update([
            'user_id' => auth()->id(),
            'observation' => 'Archivo transferido debido a la eliminación del usuario.',
        ]);
        $user->delete();

        $message = $filesCount > 0
            ? "Usuario eliminado. Se te transfirieron {$filesCount} archivo(s)."
            : 'Usuario eliminado.';

        return redirect()->route('users.index')->with('success', $message);
    }
}
