<?php

namespace App\Listeners;

use App\Events\JobApplicationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Auth;
use App\Models\Notification;
use App\Models\User;
use App\Models\JobApplication;
use App\Models\UserDeviceInfo;

class JobApplicationNotificationFired
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  JobApplicationNotification  $event
     * @return void
     */
    public function handle(JobApplicationNotification $event)
    { 
        $jobApplication = JobApplication::find($event->jobApplicationId);
        $user = User::find($jobApplication->job->user_id);

        $userDeviceInfo = UserDeviceInfo::where('user_id',$user->id)->latest()->first();
        if($userDeviceInfo)
        {
            $notification = new Notification;
            $notification->user_id          = $user->id;
            $notification->sender_id        = Auth::id();
            $notification->device_uuid      = $userDeviceInfo->device_uuid;
            $notification->device_platform  = $userDeviceInfo->platform;
            $notification->type             = 'Job Application';
            $notification->title            = 'New Application Request';
            $notification->sub_title        = 'New Job application on '.$jobApplication->job->title;
            $notification->message          = 'Applied by '.Auth::user()->first_name.'  '.Auth::user()->last_name;
            $notification->image_url        = '';
            $notification->read_status      = false;
            $notification->save();
        }
    }
}
