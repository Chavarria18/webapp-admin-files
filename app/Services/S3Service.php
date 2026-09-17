<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Service
{
    protected string $disk = 's3';

    public function upload(UploadedFile $file, string $folder = 'archivos',$name =''): string
    {
        $filename = empty($name) ?  Str::uuid() . '.' . $file->getClientOriginalExtension() : $name; 

        return $file->storeAs($folder, $filename, $this->disk);
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    public function temporaryUrl(string $path, int $minutes = 5): string
    {
        return Storage::disk($this->disk)->temporaryUrl(
            $path,
            now()->addMinutes($minutes)
        );
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }
}