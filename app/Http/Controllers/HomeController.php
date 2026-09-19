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

        $filterUser = $request->filled('user_id') ? User::findOrFail($request->user_id) : null;

        $search = trim((string) $request->input('search'));

        $files = File::visibleTo($user)
            ->with('user.area')
            ->when($filterUser, fn ($q) => $q->where('user_id', $filterUser->id))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $metrics = $this->fileService->getMetrics(auth()->user());
        return view('home.index', compact('files', 'metrics', 'filterUser', 'search'));
    }
}
