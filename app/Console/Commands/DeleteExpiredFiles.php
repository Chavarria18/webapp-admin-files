<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\S3Service;

#[Signature('files:delete-expired {--days=30}')]
#[Description('Command description')]
class DeleteExpiredFiles extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(S3Service $s3)
    {

        $days = $this->option('days');
        $this->info('Eliminando archivos de ' . $days . ' dias de antiguedad');

        if ($days == 0) {
            $files = File::onlyTrashed()

                ->get();
        } else {
            $files = File::onlyTrashed()
                ->where('deleted_at', '<', now()->subDays($days))
                ->get();
        }


        foreach ($files as $file) {
            $s3->deleteIfExists($file->s3dir);
            $file->forceDelete();
            $this->info("Eliminado: {$file->name}");
        }
        $this->info('Archivos en basurero eliminados');

        return Command::SUCCESS;
    }
}
