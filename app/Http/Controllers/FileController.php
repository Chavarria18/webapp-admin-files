<?php

namespace App\Http\Controllers;

use App\Services\S3Service;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\History;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class FileController extends Controller
{

    private function logHistory(string $action, File $file): void
    {
        $user = auth()->user();

        History::create([
            'action' => $action,
            'file_name' => $file->name,
            'user_id' => $user->id,
            'username' => $user->name,
        ]);
    }


    public function showFilesForm()
    {
        $user = auth()->user();

        return view('files.index');
    }

    public function storeFiles(Request $request, S3Service $service)
    {
        
        $this->authorize('create', File::class);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB
        ]);
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();


        do {
            $uuid = (string) Str::uuid();
            $fileName = $uuid . '_' . $originalName;
        } while (File::where('uuid', $fileName)->exists());


        $path = $service->upload($request->file('file'), 'archivos', $fileName);


        $archivo = File::create([
            'uuid' => $fileName,
            'name' => $originalName,
            's3dir' => $path,
            'size' => $request->file('file')->getSize(),
            'user_id' => auth()->id(),
        ]);

        $this->logHistory('upload', $archivo);

        return redirect()->route('home')
            ->with('success', "Archivo \"{$originalName}\" subido correctamente.")
            ->with('new_file_id', $archivo->id);
    }

    public function downloadFiles(File $file)
    {
        $this->authorize('view', $file);
       
        return Storage::disk('s3')->download(
            $file->s3dir,
            $file->name
        );


    }

    public function checkName(Request $request)
    {
        $exists = File::where('name', $request->name)
            ->where('user_id', auth()->id())
            ->exists();

        return response()->json([
            'exists' => $exists,
        ]);
    }


    public function destroy(File $file)
    {
        if (auth()->user()->cannot('delete', $file)) {
            return redirect()->back()->withErrors([
                    '403' => 'This user does not have permision to delete this file.',
                ]);
        }

        $file->delete();

        $this->logHistory('delete', $file);

        return redirect()->route('home')->with('success', 'File moved to recycle bin');
    }


    public function recycleBin()
    {
        $user = auth()->user();

        $files = File::visibleTo($user)->onlyTrashed()->paginate(10);
        return view('recyclebin.index', compact('files'));
    }

    public function restoreFile($id)
    {
        $file = File::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $file);

        $file->restore();

        $this->logHistory('restore', $file);

        return redirect()->back()->with('success', 'File restored');
    }

    public function forceDelete($id)
    {
        $file = File::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $file);

        if ($file->s3dir && Storage::disk('s3')->exists($file->s3dir)) {
            Storage::disk('s3')->delete($file->s3dir);
        }

        $file->forceDelete();

        $this->logHistory('force_delete', $file);

        return back()->with('success', 'File permanently deleted');
    }


}
