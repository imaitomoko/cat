<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mail;
use Carbon\Carbon;

class DeleteOldMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mails:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '送信日から2ヶ月以上経過したメールを削除する';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        
        // 2ヶ月以上前のメールを削除
        $deleted = Mail::where('sent_at', '<', $twoMonthsAgo)->delete();

        $this->info("削除されたメールの数: {$deleted}");
        //
    }
}
