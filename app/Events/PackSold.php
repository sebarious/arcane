<?php

namespace App\Events;

use App\Models\Pack;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackSold implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $storeId;
    public int $batchId;
    public int $packId;
    public ?string $band;

    public function __construct(Pack $pack)
    {
        $this->storeId = $pack->batch->store_id;
        $this->batchId = $pack->batch_id;
        $this->packId  = $pack->id;
        $this->band    = $pack->card?->rarity_band;
    }

    public function broadcastOn(): array
    {
        // One channel per store
        return [new Channel("store.{$this->storeId}")];
    }

    public function broadcastAs(): string
    {
        return 'PackSold';
    }

    public function broadcastWith(): array
    {
        return [
            'store_id' => $this->storeId,
            'batch_id' => $this->batchId,
            'pack_id'  => $this->packId,
            'band'     => $this->band,
        ];
    }
}
