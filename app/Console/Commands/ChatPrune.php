<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use App\Support\EncryptedFileStore;
use Illuminate\Console\Command;

/** Retention policy (PRD 7): permanently remove messages and files older than the configured period. */
class ChatPrune extends Command
{
    protected $signature = 'chat:prune {--dry-run : Only report what would be removed}';

    protected $description = 'Delete chat messages and attachments older than the retention period';

    public function handle(EncryptedFileStore $files): int
    {
        $cutoff = now()->subYears(config('chat.retention_years'));
        $query = ChatMessage::withTrashed()->where('created_at', '<', $cutoff);

        if ($this->option('dry-run')) {
            $this->info($query->count().' message(s) older than '.$cutoff->toDateString().' would be removed.');

            return self::SUCCESS;
        }

        $removed = 0;
        $query->with('attachments')->chunkById(500, function ($messages) use ($files, &$removed) {
            foreach ($messages as $message) {
                foreach ($message->attachments as $attachment) {
                    $files->delete($attachment->path);
                }
                $message->forceDelete();
                $removed++;
            }
        });

        $this->info("Removed {$removed} message(s) older than {$cutoff->toDateString()}.");

        return self::SUCCESS;
    }
}
