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

        $files = File::visibleTo($user)
            ->when($filterUser, fn ($q) => $q->where('user_id', $filterUser->id))
            ->paginate(10)
            ->withQueryString();
        $metrics = $this->fileService->getMetrics(auth()->user());
        return view('home.index', compact('files', 'metrics', 'filterUser'));
    }
}
