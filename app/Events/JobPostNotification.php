<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobPostNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jobId;
    public function __construct($jobId)
    {
        $this->jobId = $jobId;
    }
    public function broadcastOn()
    {
        return [];
    }
}
