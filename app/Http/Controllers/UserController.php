<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\File;
use App\Models\Area;
use App\Services\CognitoService;
class UserController extends Controller
{
    public function __construct(
        private readonly CognitoService $cognito
    ) {
    }
    public function index(Request $request)
    {
        $search = $request->search;

        $query = User::visibleTo($request->user())->with(['area', 'areasGestionadas']);

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
        $this->authorize('delete', $user);

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
