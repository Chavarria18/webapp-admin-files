<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\User;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $authUser = $request->user();
        $user = $request->filled('user_id') ? User::findOrFail($request->user_id) : null;

        if ($user) {
            $this->authorize('viewHistory', $user);
        }

        $historials = History::visibleTo($authUser)
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('history.index', compact('historials', 'user'));
    }
}
