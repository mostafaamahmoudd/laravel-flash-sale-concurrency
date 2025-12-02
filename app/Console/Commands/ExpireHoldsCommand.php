<?php

namespace App\Console\Commands;

use App\Models\Hold;
use App\Services\StockService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireHoldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-holds-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire active holds whose expires_at has passed';

    public function __construct(protected StockService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Hold::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->chunkById(100, function ($holds) {
                $productIds = $holds->pluck('product_id')->unique()->values();

                DB::transaction(function () use ($holds) {
                    foreach ($holds as $hold) {
                        $hold->update([
                            'status' => 'expired',
                        ]);
                    }
                });

                foreach ($productIds as $productId) {
                    $this->service->forgetStock($productId);
                }
            });

        return self::SUCCESS;
    }
}
