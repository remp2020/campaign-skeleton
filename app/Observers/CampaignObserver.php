<?php

namespace App\Observers;

use Remp\CampaignModule\Campaign;

class CampaignObserver
{
    public function created(Campaign $campaign): void
    {
        // handle the Campaign "created" event
    }

    public function updated(Campaign $campaign): void
    {
        // handle the Campaign "updated" event
    }

    public function deleted(Campaign $campaign): void
    {
        // handle the Campaign "deleted" event
    }

    public function forceDeleted(Campaign $campaign): void
    {
        // handle the Campaign "forceDeleted" event
    }
}
