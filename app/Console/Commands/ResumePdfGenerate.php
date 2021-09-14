<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserCvDetail;
use Str;
use PDF;

class ResumePdfGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumePdf:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $destinationPath = 'uploads/';
        $cvDetails = UserCvDetail::where('cv_update_status',1)->get();
        foreach ($cvDetails as $key => $cvDetail) {
            $cv_name = Str::slug(substr($cvDetail->user->first_name, 0, 15)).'-'.Str::slug(substr($cvDetail->user->last_name, 0, 15)).'-'.$cvDetail->user->qr_code_number.'.pdf';
            
            if($cvDetail->user->userWorkExperiences->count() > 0 && $cvDetail->user->userEducationDetails->count() > 0)
            {
                $this->createResume($cv_name,$cvDetail->user);
                $cvDetail->generated_cv_file = env('CDN_DOC_URL').$destinationPath.$cv_name;
                $cvDetail->cv_update_status = 0;
                $cvDetail->save();
            }
            
        }
        return 0;
    }

    private function createResume($fileName,$user)
    {
        if(file_exists('public/uploads/'.$fileName)){ 
            unlink('public/uploads/'.$fileName);
        }
        $data = [
            'user' => $user,
        ];
        $pdf = PDF::loadView('pdf', $data);
        return $pdf->save('public/uploads/'.$fileName);
    }
}
