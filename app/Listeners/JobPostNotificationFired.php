<?php

namespace App\Listeners;

use App\Events\JobPostNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Auth;
use App\Models\Notification;
use App\Models\User;
use App\Models\Job;
use App\Models\UserDeviceInfo;

class JobPostNotificationFired
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
     * @param  JobPostNotification  $event
     * @return void
     */
    public function handle(JobPostNotification $event)
    {
        $users = User::where('user_type_id',2)->get();
        $job = Job::find($event->jobId);

        foreach ($users as $key => $user) {
            $userDeviceInfo = UserDeviceInfo::where('user_id',$user->id)->latest()->first();
            if($userDeviceInfo)
            {
                $notification = new Notification;
                $notification->user_id          = $user->id;
                $notification->sender_id        = Auth::id();
                $notification->device_uuid      = $userDeviceInfo->device_uuid;
                $notification->device_platform  = $userDeviceInfo->platform;
                $notification->type             = 'Job post';
                $notification->title            = 'New Job Posted';
                $notification->sub_title        = 'Job Name '.$job->title;
                $notification->message          = 'New Job Posted by '.Auth::user()->first_name.'  '.Auth::user()->last_name;
                $notification->image_url        = '';
                $notification->screen           = 'posted-jobs';
                $notification->data_id          = $job->id;
                $notification->user_type        = 'applicant';
                $notification->module           = 'job';
                $notification->read_status      = false;
                $notification->save();
            }
        } 
    }
}
