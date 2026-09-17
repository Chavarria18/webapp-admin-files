<?php

namespace App\Services;

use App\Models\User;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    public function getMetrics(User $user)
    {
        $query = File::query();

        match ($user->role) {           
            'admin' => null,           
            'gerente' => $query->whereHas('user', function ($q) use ($user) {
                    $q->whereIn(
                    'area_id',
                    $user->areasGestionadas->pluck('id')
                    );
                }),           
            'jefe_area' => $query->whereHas('user', function ($q) use ($user) {
                    $q->where('area_id', $user->area_id);
                }),    
            default => $query->where('user_id', $user->id),
        };

        $totalFiles = (clone $query)->count();

        $filesToday = (clone $query)
            ->whereDate('created_at', today())
            ->count();

        $filesThisMonth = (clone $query)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalSize = (clone $query)->sum('size');

        return [
            'total_files' => $totalFiles,
            'files_today' => $filesToday,
            'files_this_month' => $filesThisMonth,
            'total_size' => $totalSize,
        ];

    }
}
