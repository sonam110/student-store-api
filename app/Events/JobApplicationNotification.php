<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobApplicationNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jobApplicationId;
    public function __construct($jobApplicationId)
    {
        $this->jobApplicationId = $jobApplicationId;
    }
    public function broadcastOn()
    {
        return [];
    }
}
