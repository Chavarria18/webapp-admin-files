<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('files:delete-expired')]
#[Description('Command description')]
class DeleteExpiredFiles extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Expired files deleted.');
        $files = File::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(30))
            ->get();

        foreach ($files as $file) {
            if ($file->s3dir && Storage::disk('s3')->exists($file->s3dir)) {
                Storage::disk('s3')->delete($file->s3dir);
            }
            $file->forceDelete();
            $this->info("Deleted: {$file->name}");
        }


        return Command::SUCCESS;
    }
}
