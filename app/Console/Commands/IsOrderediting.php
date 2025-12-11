<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
class IsOrderediting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:is-orderediting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fiveMinutesAgo = Carbon::now('UTC')->subMinutes(5);
        \App\Models\Order::where('is_editing', true)
            ->where('editing_started_at', '<', $fiveMinutesAgo)
            ->update(['is_editing' => false, 'editing_started_at' => null]);

    }
}
