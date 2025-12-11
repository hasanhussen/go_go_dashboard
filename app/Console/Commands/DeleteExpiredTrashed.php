<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredTrashed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-expired-trashed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes expired trashed items from the database (e.g., soft-deleted records older than 30 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
       //$oneMonthago = Carbon::now()->subDays(30);
       $oneweekago = Carbon::now('UTC')->subDays(7);
       $twoDaysAgo = Carbon::now('UTC')->subDays(2);
         $models = [
          \App\Models\User::class,
          \App\Models\Store::class,
          \App\Models\Meal::class,
          \App\Models\Additional::class,
          \App\Models\MealVariant::class,
          \App\Models\Order::class,
          \App\Models\Cart::class,
         ];
            foreach ($models as $model) {
                $model::onlyTrashed()
                    ->where('deleted_at', '<', $oneweekago)
                    ->forceDelete();
    }
    \App\Models\Cart::withTrashed()
        ->where('created_at', '<', $twoDaysAgo)
->whereNull('order_id')->forceDelete();
        $this->info('Expired trashed items deleted successfully.');
    }
}
