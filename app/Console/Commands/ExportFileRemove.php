<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ExportFileRemove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:remove';

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
        $folderToDelete = base_path('public/export/orders/');
        $fs = new Filesystem;
        $fs->cleanDirectory($folderToDelete);

        $folderToDelete = base_path('public/export/contests/');
        $fs = new Filesystem;
        $fs->cleanDirectory($folderToDelete);

        $folderToDelete = base_path('public/export/jobs/');
        $fs = new Filesystem;
        $fs->cleanDirectory($folderToDelete);

        $folderToDelete = base_path('public/export/products/');
        $fs = new Filesystem;
        $fs->cleanDirectory($folderToDelete);

        $folderToDelete = base_path('public/export/labels/');
        $fs = new Filesystem;
        $fs->cleanDirectory($folderToDelete); 

        $folderToDelete = base_path('public/export/categories/');
        $fs = new Filesystem;
        $fs->cleanDirectory($folderToDelete);  

        $folderToDelete = base_path('public/cvs/');
        $fs = new Filesystem;
        $fs->cleanDirectory($folderToDelete); 
        return true;
    }
}
