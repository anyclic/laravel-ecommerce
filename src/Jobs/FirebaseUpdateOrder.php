<?php
namespace Vtlabs\Ecommerce\Jobs;

use Illuminate\Bus\Queueable;
use Vtlabs\Ecommerce\Models\Order;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Vtlabs\Core\Models\PushNotification;
use Vtlabs\Core\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Vtlabs\Core\Helpers\PushNotificationHelper;
use Vtlabs\Ecommerce\Http\Resources\OrderResource;

class FirebaseUpdateOrder implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $orderId;

    /**
     * Create a new job instance.
     *
     * @param  PushNotification  $pushNotification
     * @return void
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;       
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        try {
            $firebaseDatabase = FirebaseService::getDatabaseInstance();
            if ($firebaseDatabase) {
                $order = new OrderResource(Order::find($this->orderId));
                $firebaseDatabase->getReference('/orders/' . $order->id . "/data")->set($order);
                $firebaseDatabase->getReference('/vendors/' .  $order->vendor->id . '/orders/' . $order->id . "/data")->set($order);
                $firebaseDatabase->getReference('/users/' .  $order->user->id . '/orders/' . $order->id . "/data")->set($order);
            }
        } catch (\Exception $ex) {
            //
        }
    }
}
