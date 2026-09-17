<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\User;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->filled('user_id') ? User::findOrFail($request->user_id) : null;

        $historials = History::query()
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('history.index', compact('historials', 'user'));
    }
}
