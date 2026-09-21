<?php

namespace App\Services;

use App\Models\User;
use App\Models\File;

class FileService
{
    public function getMetrics(User $user)
    {
        $query = File::visibleTo($user);

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
