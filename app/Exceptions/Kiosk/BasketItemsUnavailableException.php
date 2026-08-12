<?php

namespace App\Exceptions\Kiosk;

use RuntimeException;

/** Thrown when one or more basket items are no longer held by this session at checkout time (hold expired and someone/something else claimed them). */
class BasketItemsUnavailableException extends RuntimeException
{
    /** @param int[] $cardInventoryIds */
    public function __construct(public readonly array $cardInventoryIds)
    {
        parent::__construct('One or more basket items are no longer available.');
    }
}
