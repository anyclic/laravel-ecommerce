<?php
namespace Vtlabs\Ecommerce\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Vtlabs\Core\Models\PushNotification;
use Vtlabs\Core\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Vtlabs\Ecommerce\Models\DeliveryProfile;
use Vtlabs\Core\Helpers\PushNotificationHelper;
use Vtlabs\Ecommerce\Http\Resources\DeliveryOrderRequestResource;
use Vtlabs\Ecommerce\Models\DeliveryOrderRequest;

class FirebaseUpdateDelivery implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $deliveryProfile;

    private $deliveryRequest;

    /**
     * Create a new job instance.
     *
     * @param  PushNotification  $pushNotification
     * @return void
     */
    public function __construct(DeliveryProfile $deliveryProfile, DeliveryOrderRequest $deliveryRequest)
    {
        $this->deliveryProfile = $deliveryProfile;
        $this->deliveryRequest = $deliveryRequest;
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
                $firebaseDatabase
                        ->getReference('/deliveries/' . $this->deliveryProfile->id . "/order-request")
                        ->set(new DeliveryOrderRequestResource($this->deliveryRequest->fresh()));
            }
        } catch (\Exception $ex) {
            //
        }
    }
}
