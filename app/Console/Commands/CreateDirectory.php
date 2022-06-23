<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;
class CreateDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:directory';

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

        File::ensureDirectoryExists('public/uploads');
        File::ensureDirectoryExists('public/uploads/qr');
        File::ensureDirectoryExists('public/uploads/thumbs');
        File::ensureDirectoryExists('public/export');
        File::ensureDirectoryExists('public/export/categories');
        File::ensureDirectoryExists('public/export/contests');
        File::ensureDirectoryExists('public/export/jobs');
        File::ensureDirectoryExists('public/export/labels');
        File::ensureDirectoryExists('public/export/languages');
        File::ensureDirectoryExists('public/export/orders');
        File::ensureDirectoryExists('public/export/products');
        File::ensureDirectoryExists('public/cvs');
        File::ensureDirectoryExists('storage/db-backups');
        return true;
    }
}
