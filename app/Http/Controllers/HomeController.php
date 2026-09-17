<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\FileService;

class HomeController extends Controller
{
    //
public function __construct(private FileService $fileService)
    {

    }
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'estandar') {

            $query = File::where('user_id', $user->id);

        } elseif ($user->role === 'jefe_area') {

            $query = File::whereHas('user', function ($q) use ($user) {
                $q->where('area_id', $user->area_id);
            });

        } elseif ($user->role === 'gerente') {

            $query = File::whereHas('user', function ($q) use ($user) {
                $q->whereIn('area_id', $user->areasGestionadas->pluck('id'));
            });

        } elseif ($user->role === 'admin') {

           $query = File::query();

        } else {

            abort(403);
        }

        $filterUser = $request->filled('user_id') ? User::findOrFail($request->user_id) : null;

        $files = $query
            ->when($filterUser, fn ($q) => $q->where('user_id', $filterUser->id))
            ->paginate(2)
            ->withQueryString();
        $metrics = $this->fileService->getMetrics(auth()->user());
        return view('home.index', compact('files', 'metrics', 'filterUser'));
    }
}
