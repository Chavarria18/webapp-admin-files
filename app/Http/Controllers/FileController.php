<?php

namespace App\Http\Controllers;

use App\Services\S3Service;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\History;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;


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
        ], [
            'file.max' => 'El archivo supera el tamaño máximo permitido (10MB).',
        ]);
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();


        do {
            $uuid = (string) Str::uuid();
            $fileName = $uuid . '_' . $originalName;
        } while (File::where('uuid', $fileName)->exists());


        $path = $service->upload($file, 'archivos', $fileName);

        if (!$path) {
            return back()->withErrors(['file' => 'No se pudo subir el archivo. Intenta nuevamente.']);
        }

        try {
            $archivo = DB::transaction(function () use ($fileName, $originalName, $path, $file) {
                $archivo = File::create([
                    'uuid' => $fileName,
                    'name' => $originalName,
                    's3dir' => $path,
                    'size' => $file->getSize(),
                    'user_id' => auth()->id(),
                ]);

                $this->logHistory('upload', $archivo);

                return $archivo;
            });
        } catch (Throwable $e) {
            try {
                $service->deleteIfExists($path);
            } catch (Throwable $cleanupError) {
                report($cleanupError);
            }

            report($e);

            return back()
                ->withInput()
                ->withErrors(['file' => 'No se pudo guardar el archivo. Intenta nuevamente.']);
        }

        return redirect()->route('home')
            ->with('success', "Archivo \"{$originalName}\" subido correctamente.")
            ->with('new_file_id', $archivo->id);
    }

    public function downloadFiles(File $file, S3Service $service)
    {
        $this->authorize('view', $file);

        return $service->download($file->s3dir, $file->name);


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
                '403' => 'No tienes permiso para eliminar este archivo.',
            ]);
        }

        $file->delete();

        $this->logHistory('delete', $file);

        return redirect()->route('home')->with('success', 'Archivo movido a la papelera de reciclaje.');
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

        return redirect()->back()->with('success', 'Archivo restaurado.');
    }

    public function forceDelete($id, S3Service $service)
    {
        $file = File::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $file);

        $service->deleteIfExists($file->s3dir);

        $file->forceDelete();

        $this->logHistory('force_delete', $file);

        return back()->with('success', 'Archivo eliminado permanentemente.');
    }


}
