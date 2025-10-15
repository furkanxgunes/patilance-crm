<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SendTestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test {user?} {--count=1} {--email=} {--username=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test notification to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int)$this->option('count');

        // Resolve user by --email, --username or {user} id
        $user = null;
        if ($email = $this->option('email')) {
            $user = User::where('email', $email)->first();
        } elseif ($username = $this->option('username')) {
            $user = User::where('username', $username)->first();
        } elseif ($id = $this->argument('user')) {
            $user = User::find($id);
        }

        if (!$user) {
            $this->error('User not found. Provide {user} id or use --email / --username.');
            return 1;
        }
        
        for ($i = 1; $i <= $count; $i++) {
            $user->notify(new \App\Notifications\TestNotification($i, $count));
            $this->info("Sent test notification {$i}/{$count} to user #{$user->id} ({$user->name})");
        }
        
        return 0;
    }
}
